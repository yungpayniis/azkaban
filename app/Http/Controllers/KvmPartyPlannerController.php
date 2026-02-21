<?php

namespace App\Http\Controllers;

use App\Models\GuildMember;
use App\Models\JobClass;
use App\Models\KvmParty;
use App\Models\KvmPartySlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KvmPartyPlannerController extends Controller
{
    public function index()
    {
        $parties = KvmParty::with(['slots.member.jobClass.parent'])
            ->orderBy('id')
            ->get()
            ->each(function (KvmParty $party) {
                $party->setRelation(
                    'slots',
                    $party->slots->sortBy('position')->values()
                );
            });

        $assignedMemberIds = KvmPartySlot::whereNotNull('member_id')->pluck('member_id');
        $unassignedMembers = GuildMember::where('status', GuildMember::STATUS_ACTIVE)
            ->whereNotIn('id', $assignedMemberIds)
            ->with('jobClass.parent')
            ->orderBy('name')
            ->get();
        $jobClasses = JobClass::orderBy('tier')->orderBy('name')->get();

        return view('kvm-planner.index', compact('parties', 'unassignedMembers', 'jobClasses'));
    }

    public function updateSlots(Request $request)
    {
        $data = $request->validate([
            'updates' => ['required', 'array', 'min:1'],
            'updates.*.slot_id' => ['required', 'integer', 'exists:kvm_party_slots,id'],
            'updates.*.member_id' => ['nullable', 'integer', 'exists:guild_members,id'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['updates'] as $update) {
                KvmPartySlot::where('id', $update['slot_id'])
                    ->update(['member_id' => $update['member_id']]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function store(Request $request)
    {
        $count = KvmParty::count() + 1;
        $name = trim($request->input('name', ''));
        if ($name === '') {
            $name = 'pt' . $count;
        }

        DB::transaction(function () use ($name) {
            $party = KvmParty::create(['name' => $name]);
            for ($position = 1; $position <= 5; $position++) {
                KvmPartySlot::create([
                    'kvm_party_id' => $party->id,
                    'position' => $position,
                    'member_id' => null,
                ]);
            }
        });

        return redirect()
            ->route('kvm-planner.index')
            ->with('status', 'สร้างปาร์ตี้ KVM ใหม่แล้ว');
    }

    public function addMemberSlot(KvmParty $kvmParty)
    {
        $nextPosition = ((int) $kvmParty->slots()->max('position')) + 1;

        KvmPartySlot::create([
            'kvm_party_id' => $kvmParty->id,
            'position' => $nextPosition,
            'member_id' => null,
        ]);

        return redirect()
            ->route('kvm-planner.index')
            ->with('status', "เพิ่มช่องสมาชิกสำรองให้ {$kvmParty->name} แล้ว");
    }
}
