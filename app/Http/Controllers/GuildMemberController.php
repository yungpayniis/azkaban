<?php

namespace App\Http\Controllers;

use App\Models\GuildMember;
use App\Models\GuildMemberNameHistory;
use App\Models\JobClass;
use App\Models\PartySlot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuildMemberController extends Controller
{
    public function index()
    {
        $latestSaturday = now()->isSaturday()
            ? now()->copy()->startOfDay()
            : now()->previous(\Carbon\Carbon::SATURDAY)->startOfDay();

        $members = GuildMember::with('jobClass.parent')
            ->withCount('redCards')
            ->withCount([
                'gvgWeeklyStats as latest_saturday_gvg_count' => function ($query) use ($latestSaturday) {
                    $query->whereDate('week_start_date', $latestSaturday->toDateString());
                },
            ])
            ->orderBy('status')
            ->orderBy('name')
            ->where('status', 'active')
            ->get();

        return view('guild-members.index', [
            'members' => $members,
            'latestSaturday' => $latestSaturday,
        ]);
    }

    public function create()
    {
        $jobClasses = JobClass::where('tier', 4)->orderBy('name')->get();

        return view('guild-members.create', compact('jobClasses'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $stats = $this->validateStats($request);
        $guildMember = GuildMember::create($data);
        $guildMember->nameHistories()->create([
            'name' => $guildMember->name,
        ]);
        $guildMember->stat()->create($stats);
        $guildMember->statHistories()->create($stats);

        return redirect()
            ->route('guild-members.index')
            ->with('status', 'เพิ่มสมาชิกเรียบร้อย');
    }

    public function show(GuildMember $guildMember)
    {
        $guildMember->load('jobClass', 'gvgWeeklyStats', 'nameHistories', 'stat', 'statHistories', 'redCards');

        return view('guild-members.show', compact('guildMember'));
    }

    public function issueRedCard(Request $request, GuildMember $guildMember)
    {
        if ($guildMember->status === GuildMember::STATUS_LEFT) {
            return redirect()
                ->route('guild-members.show', $guildMember)
                ->with('status', 'สมาชิกออกจากกิลแล้ว ไม่สามารถแจกใบแดงเพิ่มได้');
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $guildMember->redCards()->create([
            'reason' => trim($data['reason']),
            'issued_at' => now(),
        ]);

        $redCardCount = $guildMember->redCards()->count();

        if ($redCardCount >= 3) {
            $guildMember->update(['status' => GuildMember::STATUS_LEFT]);
            PartySlot::where('member_id', $guildMember->id)->update(['member_id' => null]);

            return redirect()
                ->route('guild-members.show', $guildMember)
                ->with('status', 'แจกใบแดงสำเร็จ (ครบ 3 ใบ) ระบบนำสมาชิกออกจากกิลแล้ว');
        }

        return redirect()
            ->route('guild-members.show', $guildMember)
            ->with('status', "แจกใบแดงสำเร็จ ({$redCardCount}/3 ใบ)");
    }

    public function edit(GuildMember $guildMember)
    {
        $jobClasses = JobClass::where('tier', 4)->orderBy('name')->get();

        return view('guild-members.edit', compact('guildMember', 'jobClasses'));
    }

    public function update(Request $request, GuildMember $guildMember)
    {
        $data = $this->validatePayload($request, $guildMember);
        $stats = $this->validateStats($request);
        $originalName = $guildMember->name;
        $guildMember->update($data);
        $stat = $guildMember->stat()->updateOrCreate(
            ['guild_member_id' => $guildMember->id],
            $stats
        );
        if ($stat->wasRecentlyCreated || $stat->wasChanged()) {
            $guildMember->statHistories()->create($stats);
        }
        if ($originalName !== $guildMember->name) {
            $guildMember->nameHistories()->create([
                'name' => $guildMember->name,
            ]);
        }

        return redirect()
            ->route('guild-members.index')
            ->with('status', 'อัปเดตสมาชิกเรียบร้อย');
    }

    public function destroy(GuildMember $guildMember)
    {
        $guildMember->delete();

        return redirect()
            ->route('guild-members.index')
            ->with('status', 'ลบสมาชิกเรียบร้อย');
    }

    private function validatePayload(Request $request, ?GuildMember $guildMember = null): array
    {
        $nameRules = ['required', 'string', 'max:255', Rule::unique('guild_members', 'name')];

        if ($guildMember) {
            $nameRules = ['required', 'string', 'max:255', Rule::unique('guild_members', 'name')->ignore($guildMember->id)];
        }

        return $request->validate([
            'name' => $nameRules,
            'job_class_id' => ['nullable', 'integer', 'exists:job_classes,id'],
            'tier' => ['required', 'in:low,middle,top'],
            'role' => ['required', 'in:dps,support,tank'],
            'nationality' => ['required', 'in:thai,foreign'],
            'status' => ['required', 'in:' . GuildMember::STATUS_ACTIVE . ',' . GuildMember::STATUS_LEFT],
            'joined_at' => ['required', 'date'],
        ]);
    }

    private function validateStats(Request $request): array
    {
        $data = $request->validate([
            'stats' => ['required', 'array'],
            'stats.str' => ['required', 'integer', 'min:0'],
            'stats.vit' => ['required', 'integer', 'min:0'],
            'stats.luk' => ['required', 'integer', 'min:0'],
            'stats.agi' => ['required', 'integer', 'min:0'],
            'stats.dex' => ['required', 'integer', 'min:0'],
            'stats.int' => ['required', 'integer', 'min:0'],
            'stats.hp' => ['required', 'integer', 'min:0'],
            'stats.sp' => ['required', 'integer', 'min:0'],
            'stats.patk' => ['required', 'integer', 'min:0'],
            'stats.matk' => ['required', 'integer', 'min:0'],
            'stats.pdef' => ['required', 'integer', 'min:0'],
            'stats.mdef' => ['required', 'integer', 'min:0'],
        ]);

        return $data['stats'];
    }

    public function leftMembers()
    {
        $members = GuildMember::with('jobClass.parent')
            ->withCount('redCards')
            ->where('status', GuildMember::STATUS_LEFT)
            ->orderBy('name')
            ->get();

        return view('guild-members.left', compact('members'));
    }
}
