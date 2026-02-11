<?php

namespace App\Http\Controllers;

use App\Models\GuildMember;
use App\Models\GvgWeeklyStat;
use Illuminate\Http\Request;

class GvgWeeklyStatController extends Controller
{
    public function index()
    {
        $stats = GvgWeeklyStat::with('guildMember.jobClass.parent')
            ->orderByDesc('week_start_date')
            ->orderBy('guild_member_id')
            ->get();

        return view('gvg-weekly-stats.index', compact('stats'));
    }

    public function summary(Request $request)
    {
        $weeks = GvgWeeklyStat::query()
            ->select('week_start_date')
            ->distinct()
            ->orderByDesc('week_start_date')
            ->pluck('week_start_date')
            ->map(fn ($date) => $date->format('Y-m-d'));

        $selectedWeek = $request->input('week') ?? $weeks->first();

        $stats = collect();
        if ($selectedWeek) {
            $stats = GvgWeeklyStat::with('guildMember.jobClass.parent')
                ->whereDate('week_start_date', $selectedWeek)
                ->get();
        }

        $rows = $stats->map(function (GvgWeeklyStat $stat) {
            return [
                'member' => $stat->guildMember,
                'role' => $stat->guildMember?->role ?? 'dps',
                'kills' => $stat->kills,
                'deaths' => $stat->deaths,
                'revives' => $stat->revives,
                'war_score' => $stat->war_score,
                'score' => $stat->calculatedScoreAuto(),
                'combat_power' => $stat->calculatedCombatPower(),
            ];
        })->sortByDesc('score')->values();

        return view('gvg-weekly-stats.summary', [
            'weeks' => $weeks,
            'selectedWeek' => $selectedWeek,
            'rows' => $rows,
        ]);
    }

    public function create(Request $request)
    {
        $members = GuildMember::where('status', GuildMember::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
        $selectedMemberId = $request->query('member_id');

        return view('gvg-weekly-stats.create', compact('members', 'selectedMemberId'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        GvgWeeklyStat::create($data);

        return redirect()
            ->route('gvg-weekly-stats.index')
            ->with('status', 'เพิ่มผลงาน GVG เรียบร้อย');
    }

    public function show(GvgWeeklyStat $gvgWeeklyStat)
    {
        $gvgWeeklyStat->load('guildMember');

        return view('gvg-weekly-stats.show', compact('gvgWeeklyStat'));
    }

    public function edit(GvgWeeklyStat $gvgWeeklyStat)
    {
        $members = GuildMember::where('status', GuildMember::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('gvg-weekly-stats.edit', compact('gvgWeeklyStat', 'members'));
    }

    public function update(Request $request, GvgWeeklyStat $gvgWeeklyStat)
    {
        $data = $this->validatePayload($request, $gvgWeeklyStat->id);
        $gvgWeeklyStat->update($data);

        return redirect()
            ->route('gvg-weekly-stats.index')
            ->with('status', 'อัปเดตผลงาน GVG เรียบร้อย');
    }

    public function destroy(GvgWeeklyStat $gvgWeeklyStat)
    {
        $gvgWeeklyStat->delete();

        return redirect()
            ->route('gvg-weekly-stats.index')
            ->with('status', 'ลบผลงาน GVG เรียบร้อย');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'guild_member_id' => ['required', 'integer', 'exists:guild_members,id'],
            'week_start_date' => ['required', 'date'],
            'kills' => ['required', 'integer', 'min:0'],
            'deaths' => ['required', 'integer', 'min:0'],
            'revives' => ['required', 'integer', 'min:0'],
            'war_score' => ['required', 'integer', 'min:0'],
        ]);
    }
}
