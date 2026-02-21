<?php

namespace App\Http\Controllers;

use App\Models\GuildMember;
use App\Models\GvgWeeklyStat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        $activeMembersQuery = GuildMember::where('status', GuildMember::STATUS_ACTIVE);
        $activeMembersCount = (clone $activeMembersQuery)->count();
        $cooldownBlockedCount = (clone $activeMembersQuery)
            ->where(function ($query) {
                $query->whereNull('joined_at')
                    ->orWhere('joined_at', '>', now()->subDays(7));
            })
            ->count();
        $cooldownBlockedRate = $activeMembersCount > 0
            ? ($cooldownBlockedCount / $activeMembersCount) * 100
            : 0;

        $missingMembers = collect();
        if ($selectedWeek) {
            $submittedMemberIds = $stats->pluck('guild_member_id')->filter()->values();
            $cooldownCutoff = now()->subDays(7);

            $missingMembers = GuildMember::with('jobClass.parent')
                ->where('status', GuildMember::STATUS_ACTIVE)
                ->when($submittedMemberIds->isNotEmpty(), function ($query) use ($submittedMemberIds) {
                    $query->whereNotIn('id', $submittedMemberIds);
                })
                ->orderByRaw("CASE tier WHEN 'top' THEN 1 WHEN 'middle' THEN 2 WHEN 'low' THEN 3 ELSE 4 END")
                ->orderBy('name')
                ->get()
                ->map(function (GuildMember $member) use ($cooldownCutoff) {
                    $blockedByCooldown = !$member->joined_at || $member->joined_at->gt($cooldownCutoff);

                    return [
                        'name' => $member->name,
                        'tier' => $member->tier,
                        'role' => $member->role,
                        'job' => $member->jobClass?->name,
                        'status' => $blockedByCooldown ? 'ติดคูลดาวน์ 7 วัน' : 'พร้อมลงแต่ยังไม่ส่งผลงาน',
                    ];
                })
                ->values();
        }

        return view('gvg-weekly-stats.summary', [
            'weeks' => $weeks,
            'selectedWeek' => $selectedWeek,
            'rows' => $rows,
            'activeMembersCount' => $activeMembersCount,
            'cooldownBlockedCount' => $cooldownBlockedCount,
            'cooldownBlockedRate' => $cooldownBlockedRate,
            'missingMembers' => $missingMembers,
        ]);
    }

    public function create(Request $request)
    {
        $latestSaturday = $this->latestSaturday();

        $members = GuildMember::where('status', GuildMember::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();
        $selectedMemberId = $request->query('member_id');

        return view('gvg-weekly-stats.create', [
            'members' => $members,
            'selectedMemberId' => $selectedMemberId,
            'defaultWeekStartDate' => $latestSaturday->format('Y-m-d'),
            'lockWeekStartDate' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $latestSaturday = $this->latestSaturday();
        $data['week_start_date'] = $latestSaturday->toDateString();
        GvgWeeklyStat::create($data);

        if ($request->boolean('stay_on_page')) {
            return redirect()
                ->back()
                ->with('status', 'เพิ่มผลงาน GVG เรียบร้อย');
        }

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

    public function destroyWeek(Request $request)
    {
        $data = $request->validate([
            'week_start_date' => ['required', 'date'],
        ]);

        $weekStartDate = \Carbon\Carbon::parse($data['week_start_date'])->toDateString();
        $query = GvgWeeklyStat::whereDate('week_start_date', $weekStartDate);
        $count = (clone $query)->count();
        $query->delete();

        return redirect()
            ->route('gvg-weekly-stats.summary', ['week' => $weekStartDate])
            ->with('status', "ลบผลงาน GVG ของวันที่ {$weekStartDate} แล้ว {$count} รายการ");
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

    public function importJsonForm()
    {
        $latestSaturday = $this->latestSaturday();

        return view('gvg-weekly-stats.import-json', [
            'defaultWeekStartDate' => $latestSaturday->format('Y-m-d'),
        ]);
    }

    public function importJsonStore(Request $request)
    {
        $data = $request->validate([
            'week_start_date' => ['required', 'date'],
            'json_payload' => ['required', 'string'],
        ]);

        try {
            $rows = json_decode($data['json_payload'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['json_payload' => 'JSON ไม่ถูกต้อง: ' . $e->getMessage()]);
        }

        if (!is_array($rows)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['json_payload' => 'รูปแบบ JSON ต้องเป็น array ของรายการ']);
        }

        $weekStartDate = \Carbon\Carbon::parse($data['week_start_date'])->toDateString();
        $activeMembers = GuildMember::with('nameHistories')
            ->where('status', GuildMember::STATUS_ACTIVE)
            ->get();
        $membersByNormalizedName = $activeMembers->groupBy(function (GuildMember $member) {
            return $this->normalizeMemberName($member->name);
        });
        $membersByLooseName = $activeMembers->groupBy(function (GuildMember $member) {
            return $this->normalizeMemberNameLoose($member->name);
        });
        $membersByLooseNameNoDigitPrefix = $activeMembers->groupBy(function (GuildMember $member) {
            return $this->normalizeMemberNameNoDigitPrefix($member->name);
        });
        $historyNameRows = $activeMembers->flatMap(function (GuildMember $member) {
            return $member->nameHistories->map(function ($history) use ($member) {
                return [
                    'member' => $member,
                    'name' => (string) $history->name,
                ];
            });
        });
        $membersByHistoryNormalizedName = $historyNameRows
            ->groupBy(function ($row) {
                return $this->normalizeMemberName($row['name']);
            })
            ->map(function ($group) {
                return $group->pluck('member')->unique('id')->values();
            });
        $membersByHistoryLooseName = $historyNameRows
            ->groupBy(function ($row) {
                return $this->normalizeMemberNameLoose($row['name']);
            })
            ->map(function ($group) {
                return $group->pluck('member')->unique('id')->values();
            });
        $membersByHistoryLooseNameNoDigitPrefix = $historyNameRows
            ->groupBy(function ($row) {
                return $this->normalizeMemberNameNoDigitPrefix($row['name']);
            })
            ->map(function ($group) {
                return $group->pluck('member')->unique('id')->values();
            });

        $created = 0;
        $updated = 0;
        $unchanged = 0;
        $skipped = 0;
        $issues = [];
        $matchNotes = [];
        $seenMemberIds = [];

        foreach ($rows as $index => $row) {
            $lineNo = $index + 1;
            if (!is_array($row)) {
                $skipped++;
                $issues[] = "รายการที่ {$lineNo}: รูปแบบไม่ถูกต้อง";
                continue;
            }

            $normalizedName = $this->normalizeMemberName((string) ($row['member'] ?? ''));
            if ($normalizedName === '') {
                $skipped++;
                $issues[] = "รายการที่ {$lineNo}: ไม่พบชื่อสมาชิก";
                continue;
            }

            $resolved = $this->resolveMemberFromJsonName(
                (string) ($row['member'] ?? ''),
                $activeMembers,
                $membersByNormalizedName,
                $membersByLooseName,
                $membersByLooseNameNoDigitPrefix,
                $membersByHistoryNormalizedName,
                $membersByHistoryLooseName,
                $membersByHistoryLooseNameNoDigitPrefix
            );

            if (!$resolved['member']) {
                $skipped++;
                $issues[] = "รายการที่ {$lineNo}: {$resolved['error']}";
                continue;
            }
            /** @var GuildMember $member */
            $member = $resolved['member'];

            if (!empty($resolved['note'])) {
                $matchNotes[] = "รายการที่ {$lineNo}: {$resolved['note']}";
            }

            if (in_array($member->id, $seenMemberIds, true)) {
                $skipped++;
                $issues[] = "รายการที่ {$lineNo}: ชื่อซ้ำใน JSON (สมาชิก {$member->name})";
                continue;
            }
            $seenMemberIds[] = $member->id;

            [$kills, $deaths, $revives] = $this->parseBattleStats($row['battle_stats'] ?? null);
            if ($kills === null || $deaths === null || $revives === null) {
                $skipped++;
                $issues[] = "รายการที่ {$lineNo}: battle_stats ไม่ถูกต้อง (ต้องเป็นรูปแบบ 10/20/3)";
                continue;
            }

            $warScoreRaw = $row['score'] ?? $row['war_score'] ?? null;
            if (!is_numeric($warScoreRaw)) {
                $skipped++;
                $issues[] = "รายการที่ {$lineNo}: ไม่พบ score/war_score ที่เป็นตัวเลข";
                continue;
            }

            $payload = [
                'kills' => (int) $kills,
                'deaths' => (int) $deaths,
                'revives' => (int) $revives,
                'war_score' => (int) $warScoreRaw,
            ];

            $stat = GvgWeeklyStat::firstOrNew([
                'guild_member_id' => $member->id,
                'week_start_date' => $weekStartDate,
            ]);
            $isExisting = $stat->exists;

            if ($isExisting
                && (int) $stat->kills === $payload['kills']
                && (int) $stat->deaths === $payload['deaths']
                && (int) $stat->revives === $payload['revives']
                && (int) $stat->war_score === $payload['war_score']
            ) {
                $unchanged++;
                continue;
            }

            $stat->fill($payload);
            $stat->save();

            if (!$isExisting) {
                $created++;
            } else {
                $updated++;
            }
        }

        return redirect()
            ->back()
            ->with('status', "นำเข้าเสร็จแล้ว: เพิ่มใหม่ {$created}, อัปเดต {$updated}, ไม่เปลี่ยนแปลง {$unchanged}, ข้าม {$skipped}")
            ->with('import_issues', $issues)
            ->with('import_match_notes', $matchNotes);
    }

    private function parseBattleStats(mixed $value): array
    {
        if (!is_string($value)) {
            return [null, null, null];
        }

        if (!preg_match('/^\s*(\d+)\s*\/\s*(\d+)\s*\/\s*(\d+)\s*$/', $value, $matches)) {
            return [null, null, null];
        }

        return [(int) $matches[1], (int) $matches[2], (int) $matches[3]];
    }

    private function normalizeMemberName(string $name): string
    {
        $normalized = trim($name);
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? '';
        $normalized = preg_replace('/\s*-\s*$/u', '', $normalized) ?? '';

        return Str::lower(trim($normalized));
    }

    private function normalizeMemberNameLoose(string $name): string
    {
        $normalized = $this->normalizeMemberName($name);
        $normalized = preg_replace('/[\s\-_\.]+/u', '', $normalized) ?? '';

        return Str::lower(trim($normalized));
    }

    private function normalizeMemberNameNoDigitPrefix(string $name): string
    {
        $normalized = $this->normalizeMemberNameLoose($name);
        $normalized = preg_replace('/^\d+/u', '', $normalized) ?? '';

        return Str::lower(trim($normalized));
    }

    private function resolveMemberFromJsonName(
        string $rawName,
        $activeMembers,
        $membersByNormalizedName,
        $membersByLooseName,
        $membersByLooseNameNoDigitPrefix,
        $membersByHistoryNormalizedName,
        $membersByHistoryLooseName,
        $membersByHistoryLooseNameNoDigitPrefix
    ): array {
        $normalizedName = $this->normalizeMemberName($rawName);
        $looseName = $this->normalizeMemberNameLoose($rawName);
        $looseNameNoDigitPrefix = $this->normalizeMemberNameNoDigitPrefix($rawName);

        if ($normalizedName !== '' && $membersByNormalizedName->has($normalizedName)) {
            $matches = $membersByNormalizedName->get($normalizedName);
            if ($matches->count() === 1) {
                return ['member' => $matches->first(), 'error' => null, 'note' => null];
            }
            return ['member' => null, 'error' => "ชื่อซ้ำในระบบ ({$rawName}) โปรดใช้ชื่อให้ชัดเจน", 'note' => null];
        }

        if ($looseName !== '' && $membersByLooseName->has($looseName)) {
            $matches = $membersByLooseName->get($looseName);
            if ($matches->count() === 1) {
                $member = $matches->first();
                return ['member' => $member, 'error' => null, 'note' => "จับคู่ชื่ออัตโนมัติ: {$rawName} -> {$member->name}"];
            }
            return ['member' => null, 'error' => "ชื่อคล้ายหลายคน ({$rawName}) โปรดระบุให้ชัดเจน", 'note' => null];
        }

        if ($looseNameNoDigitPrefix !== '' && $membersByLooseNameNoDigitPrefix->has($looseNameNoDigitPrefix)) {
            $matches = $membersByLooseNameNoDigitPrefix->get($looseNameNoDigitPrefix);
            if ($matches->count() === 1) {
                $member = $matches->first();
                return ['member' => $member, 'error' => null, 'note' => "จับคู่ชื่ออัตโนมัติ (ตัดเลขนำหน้า): {$rawName} -> {$member->name}"];
            }
            return ['member' => null, 'error' => "ชื่อคล้ายหลายคนหลังตัดเลขนำหน้า ({$rawName}) โปรดระบุให้ชัดเจน", 'note' => null];
        }

        if ($normalizedName !== '' && $membersByHistoryNormalizedName->has($normalizedName)) {
            $matches = $membersByHistoryNormalizedName->get($normalizedName);
            if ($matches->count() === 1) {
                $member = $matches->first();
                return ['member' => $member, 'error' => null, 'note' => "จับคู่จากชื่อเก่า: {$rawName} -> {$member->name}"];
            }
            return ['member' => null, 'error' => "ชื่อเก่าซ้ำหลายคน ({$rawName}) โปรดระบุให้ชัดเจน", 'note' => null];
        }

        if ($looseName !== '' && $membersByHistoryLooseName->has($looseName)) {
            $matches = $membersByHistoryLooseName->get($looseName);
            if ($matches->count() === 1) {
                $member = $matches->first();
                return ['member' => $member, 'error' => null, 'note' => "จับคู่จากชื่อเก่าแบบยืดหยุ่น: {$rawName} -> {$member->name}"];
            }
            return ['member' => null, 'error' => "ชื่อเก่าคล้ายหลายคน ({$rawName}) โปรดระบุให้ชัดเจน", 'note' => null];
        }

        if ($looseNameNoDigitPrefix !== '' && $membersByHistoryLooseNameNoDigitPrefix->has($looseNameNoDigitPrefix)) {
            $matches = $membersByHistoryLooseNameNoDigitPrefix->get($looseNameNoDigitPrefix);
            if ($matches->count() === 1) {
                $member = $matches->first();
                return ['member' => $member, 'error' => null, 'note' => "จับคู่จากชื่อเก่า (ตัดเลขนำหน้า): {$rawName} -> {$member->name}"];
            }
            return ['member' => null, 'error' => "ชื่อเก่าคล้ายหลายคนหลังตัดเลขนำหน้า ({$rawName}) โปรดระบุให้ชัดเจน", 'note' => null];
        }

        if ($looseName !== '') {
            $containsMatches = $activeMembers->filter(function (GuildMember $member) use ($looseName) {
                $memberLoose = $this->normalizeMemberNameLoose($member->name);
                return $memberLoose !== '' && (str_contains($memberLoose, $looseName) || str_contains($looseName, $memberLoose));
            })->values();

            if ($containsMatches->count() === 1) {
                $member = $containsMatches->first();
                return ['member' => $member, 'error' => null, 'note' => "จับคู่ชื่ออัตโนมัติ: {$rawName} -> {$member->name}"];
            }
            if ($containsMatches->count() > 1) {
                return ['member' => null, 'error' => "ชื่อคล้ายหลายคน ({$rawName}) โปรดระบุให้ชัดเจน", 'note' => null];
            }
        }

        $bestMember = null;
        $bestScore = -1.0;
        $secondBestScore = -1.0;
        foreach ($activeMembers as $member) {
            $memberLoose = $this->normalizeMemberNameLoose($member->name);
            if ($memberLoose === '' || $looseName === '') {
                continue;
            }
            similar_text($looseName, $memberLoose, $percent);
            if ($percent > $bestScore) {
                $secondBestScore = $bestScore;
                $bestScore = $percent;
                $bestMember = $member;
            } elseif ($percent > $secondBestScore) {
                $secondBestScore = $percent;
            }
        }

        if ($bestMember && $bestScore >= 72 && ($bestScore - $secondBestScore) >= 6) {
            return ['member' => $bestMember, 'error' => null, 'note' => "จับคู่ชื่อใกล้เคียง: {$rawName} -> {$bestMember->name}"];
        }

        return ['member' => null, 'error' => "ไม่พบสมาชิกที่ตรงกับชื่อ ({$rawName})", 'note' => null];
    }

    private function latestSaturday(): \Carbon\Carbon
    {
        return now()->isSaturday()
            ? now()->copy()->startOfDay()
            : now()->previous(\Carbon\Carbon::SATURDAY)->startOfDay();
    }
}
