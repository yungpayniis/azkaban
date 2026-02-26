<?php

namespace App\Http\Controllers;

use App\Models\GuildMember;
use App\Models\KvmParty;
use App\Models\JobClass;
use App\Models\Party;
use App\Models\PartySlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartyPlannerController extends Controller
{
    public function index()
    {
        $parties = Party::with(['slots.member.jobClass.parent'])
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->each(function (Party $party) {
                $party->setRelation(
                    'slots',
                    $party->slots->sortBy('position')->values()
                );
            });

        $assignedMemberIds = PartySlot::whereNotNull('member_id')->pluck('member_id');
        $unassignedMembers = GuildMember::where('status', GuildMember::STATUS_ACTIVE)
            ->whereNotIn('id', $assignedMemberIds)
            ->with('jobClass.parent')
            ->orderByRaw("CASE tier WHEN 'top' THEN 1 WHEN 'middle' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get();
        $jobClasses = JobClass::orderBy('tier')->orderBy('name')->get();

        return view('party-planner.index', compact('parties', 'unassignedMembers', 'jobClasses'));
    }

    public function view()
    {
        $parties = Party::with(['slots.member'])
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->each(function (Party $party) {
                $party->setRelation(
                    'slots',
                    $party->slots->sortBy('position')->values()
                );
            });

        return view('party-planner.view', compact('parties'));
    }

    public function updateSlots(Request $request)
    {
        $data = $request->validate([
            'updates' => ['required', 'array', 'min:1'],
            'updates.*.slot_id' => ['required', 'integer', 'exists:party_slots,id'],
            'updates.*.member_id' => ['nullable', 'integer', 'exists:guild_members,id'],
        ]);

        $memberIds = collect($data['updates'])
            ->pluck('member_id')
            ->filter(fn ($memberId) => $memberId !== null)
            ->values();
        if ($memberIds->duplicates()->isNotEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'member_id ซ้ำใน updates',
            ], 422);
        }

        DB::transaction(function () use ($data) {
            $slotIds = collect($data['updates'])
                ->pluck('slot_id')
                ->unique()
                ->values();

            PartySlot::whereIn('id', $slotIds)->update(['member_id' => null]);

            foreach ($data['updates'] as $update) {
                PartySlot::where('id', $update['slot_id'])
                    ->update(['member_id' => $update['member_id']]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function autoAssign()
    {
        $parties = Party::with('slots')
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->each(function (Party $party) {
                $party->setRelation(
                    'slots',
                    $party->slots->sortBy('position')->values()
                );
            });

        $partySlotQueues = $parties->map(function (Party $party) {
            return $party->slots->pluck('id')->values();
        })->values();

        $eligibleDate = now()->subDays(7);
        $members = GuildMember::where('status', GuildMember::STATUS_ACTIVE)
            ->whereNotNull('joined_at')
            ->where('joined_at', '<=', $eligibleDate)
            ->orderBy('name')
            ->get()
            ->values();

        $foreignMembers = $members->where('nationality', 'foreign')->values();
        $localMembers = $members->where('nationality', 'thai')->values();

        $kvmPartyIndexByMemberId = [];
        $kvmParties = KvmParty::with('slots')->orderBy('id')->get();
        foreach ($kvmParties as $index => $kvmParty) {
            foreach ($kvmParty->slots as $slot) {
                if ($slot->member_id) {
                    $kvmPartyIndexByMemberId[$slot->member_id] = $index;
                }
            }
        }

        $slotsPerParty = $partySlotQueues->first()?->count() ?? 0;
        $partyCount = $partySlotQueues->count();
        $foreignPartyCount = $slotsPerParty > 0 ? (int) ceil($foreignMembers->count() / $slotsPerParty) : 0;
        $foreignPartyCount = min($foreignPartyCount, $partyCount);
        $foreignPartyIndices = $foreignPartyCount > 0
            ? range($partyCount - $foreignPartyCount, $partyCount - 1)
            : [];

        $assigned = [];

        $assignToParty = function (int $partyIndex, int $memberId) use (&$partySlotQueues, &$assigned): bool {
            $queue = $partySlotQueues[$partyIndex] ?? collect();
            if ($queue->isEmpty()) {
                return false;
            }
            $slotId = $queue->shift();
            $partySlotQueues[$partyIndex] = $queue;
            $assigned[$slotId] = $memberId;
            return true;
        };

        // 1) Foreign members -> dedicated foreign parties (ท้ายสุดก่อน)
        if ($foreignPartyCount > 0) {
            $foreignIndex = 0;
            foreach ($foreignPartyIndices as $partyIndex) {
                while ($foreignIndex < $foreignMembers->count()) {
                    if (! $assignToParty($partyIndex, $foreignMembers[$foreignIndex]->id)) {
                        break;
                    }
                    $foreignIndex++;
                }
            }
        }

        // 2) Local members with KVM party -> same party index (ถ้าไม่ใช่ foreign party)
        $localsWithKvm = $localMembers->filter(function ($member) use ($kvmPartyIndexByMemberId) {
            return array_key_exists($member->id, $kvmPartyIndexByMemberId);
        })->values();
        foreach ($localsWithKvm as $member) {
            $targetIndex = $kvmPartyIndexByMemberId[$member->id];
            if ($targetIndex >= $partyCount) {
                continue;
            }
            if (in_array($targetIndex, $foreignPartyIndices, true)) {
                continue;
            }
            $assignToParty($targetIndex, $member->id);
        }

        // 3) Fill remaining locals by tier priority
        $remainingLocals = $localMembers->filter(function ($member) use ($kvmPartyIndexByMemberId) {
            return ! array_key_exists($member->id, $kvmPartyIndexByMemberId);
        })->values();

        $tierOrder = ['top' => 1, 'middle' => 2, 'low' => 3];
        $remainingLocals = $remainingLocals->sort(function ($a, $b) use ($tierOrder) {
            $tierA = $tierOrder[$a->tier] ?? 99;
            $tierB = $tierOrder[$b->tier] ?? 99;
            if ($tierA === $tierB) {
                return strcmp($a->name, $b->name);
            }
            return $tierA <=> $tierB;
        })->values();

        foreach ($partySlotQueues as $partyIndex => $queue) {
            if (in_array($partyIndex, $foreignPartyIndices, true)) {
                continue;
            }
            while ($queue->isNotEmpty() && $remainingLocals->isNotEmpty()) {
                $member = $remainingLocals->shift();
                $assignToParty($partyIndex, $member->id);
                $queue = $partySlotQueues[$partyIndex];
            }
        }

        // Build remaining list after any assignments
        $assignedMemberIds = collect($assigned)->values()->all();
        $remainingMembers = $members->filter(function ($member) use ($assignedMemberIds) {
            return ! in_array($member->id, $assignedMemberIds, true);
        })->values();

        DB::transaction(function () use ($parties, $remainingMembers, $assigned, $foreignPartyIndices) {
            // Clear all existing slots
            $allSlotIds = $parties->flatMap(function (Party $party) {
                return $party->slots->pluck('id');
            })->values();
            if ($allSlotIds->isNotEmpty()) {
                PartySlot::whereIn('id', $allSlotIds)->update(['member_id' => null]);
            }

            // Apply assigned from current slots
            foreach ($assigned as $slotId => $memberId) {
                PartySlot::where('id', $slotId)->update(['member_id' => $memberId]);
            }

            $unassignedQueue = $remainingMembers->values();
            $foreignQueue = $unassignedQueue->where('nationality', 'foreign')->values();
            $localQueue = $unassignedQueue->where('nationality', 'thai')->values();

            $slotPartyIndex = [];
            foreach ($parties as $partyIndex => $party) {
                foreach ($party->slots as $slot) {
                    $slotPartyIndex[$slot->id] = $partyIndex;
                }
            }

            // Fill remaining foreigns into foreign parties first (if any)
            if (!empty($foreignPartyIndices)) {
                foreach ($foreignPartyIndices as $partyIndex) {
                    $party = $parties->get($partyIndex);
                    if (!$party) {
                        continue;
                    }
                    foreach ($party->slots as $slot) {
                        if (array_key_exists($slot->id, $assigned)) {
                            continue;
                        }
                        if ($foreignQueue->isEmpty()) {
                            break 2;
                        }
                        $member = $foreignQueue->shift();
                        PartySlot::where('id', $slot->id)->update(['member_id' => $member->id]);
                    }
                }
            }

            // Fill remaining locals into empty slots of non-foreign parties
            $emptyLocalSlotIds = $parties->flatMap(function (Party $party) {
                return $party->slots->pluck('id');
            })->values()->filter(function ($slotId) use ($assigned, $foreignPartyIndices, $slotPartyIndex) {
                if (array_key_exists($slotId, $assigned)) {
                    return false;
                }
                $partyIndex = $slotPartyIndex[$slotId] ?? null;
                return $partyIndex !== null && !in_array($partyIndex, $foreignPartyIndices, true);
            })->values();

            foreach ($emptyLocalSlotIds as $slotId) {
                if ($localQueue->isEmpty()) {
                    break;
                }
                $member = $localQueue->shift();
                PartySlot::where('id', $slotId)->update(['member_id' => $member->id]);
            }

            // Create new parties for remaining foreign members
            while ($foreignQueue->isNotEmpty()) {
                $partyNumber = Party::count() + 1;
                $party = Party::create([
                    'name' => 'Party ' . $partyNumber,
                    'position' => ((int) Party::max('position')) + 1,
                ]);
                for ($position = 1; $position <= 15; $position++) {
                    $member = $foreignQueue->shift();
                    PartySlot::create([
                        'party_id' => $party->id,
                        'position' => $position,
                        'member_id' => $member?->id,
                    ]);
                }
            }

            // Create new parties for remaining local members
            while ($localQueue->isNotEmpty()) {
                $partyNumber = Party::count() + 1;
                $party = Party::create([
                    'name' => 'Party ' . $partyNumber,
                    'position' => ((int) Party::max('position')) + 1,
                ]);
                for ($position = 1; $position <= 15; $position++) {
                    $member = $localQueue->shift();
                    PartySlot::create([
                        'party_id' => $party->id,
                        'position' => $position,
                        'member_id' => $member?->id,
                    ]);
                }
            }
        });

        return response()->json(['ok' => true]);
    }

    public function store(Request $request)
    {
        $count = Party::count() + 1;
        $name = trim($request->input('name', ''));
        if ($name === '') {
            $name = 'Party ' . $count;
        }

        DB::transaction(function () use ($name) {
            $party = Party::create([
                'name' => $name,
                'position' => ((int) Party::max('position')) + 1,
            ]);
            for ($position = 1; $position <= 15; $position++) {
                PartySlot::create([
                    'party_id' => $party->id,
                    'position' => $position,
                    'member_id' => null,
                ]);
            }
        });

        return redirect()
            ->route('party-planner.index')
            ->with('status', 'สร้างปาร์ตี้ใหม่เรียบร้อย');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'party_ids' => ['required', 'array', 'min:1'],
            'party_ids.*' => ['required', 'integer', 'distinct', 'exists:parties,id'],
        ]);

        $totalParties = Party::count();
        if (count($data['party_ids']) !== $totalParties) {
            return response()->json([
                'ok' => false,
                'message' => 'party_ids ต้องส่งให้ครบทุกปาร์ตี้',
            ], 422);
        }

        DB::transaction(function () use ($data) {
            foreach ($data['party_ids'] as $index => $partyId) {
                Party::where('id', $partyId)->update(['position' => $index + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function update(Request $request, Party $party)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $party->update([
            'name' => trim($data['name']),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()
            ->route('party-planner.index')
            ->with('status', 'อัปเดตชื่อปาร์ตี้เรียบร้อย');
    }

    public function destroy(Party $party)
    {
        $party->delete();

        return redirect()
            ->route('party-planner.index')
            ->with('status', 'ลบปาร์ตี้เรียบร้อย');
    }

    public function destroyAll()
    {
        Party::query()->delete();

        return redirect()
            ->route('party-planner.index')
            ->with('status', 'ลบปาร์ตี้ทั้งหมดเรียบร้อย');
    }
}
