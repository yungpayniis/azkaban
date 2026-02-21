@extends('layouts.app')

@section('title', 'สรุปคะแนน GVG รายสัปดาห์')

@section('content')
    @php
        $participants = $rows->count();
        $totalKills = $rows->sum('kills');
        $totalDeaths = $rows->sum('deaths');
        $totalRevives = $rows->sum('revives');
        $totalWarScore = $rows->sum('war_score');
        $avgScore = $participants > 0 ? $rows->avg('score') : 0;
        $avgCombatPower = $participants > 0 ? $rows->avg('combat_power') : 0;
        $teamKd = $totalKills / max($totalDeaths, 1);
        $nonWarCount = $rows->where('war_score', 0)->count();
        $warJoinCount = $participants - $nonWarCount;
        $warJoinRate = $participants > 0 ? ($warJoinCount / $participants) * 100 : 0;
        $topScoreRow = $rows->sortByDesc('score')->first();
        $topCombatPowerRow = $rows->sortByDesc('combat_power')->first();
        $topReviveRow = $rows->sortByDesc('revives')->first();
        $roleSummary = $rows->groupBy('role')->map(function ($group) {
            return [
                'count' => $group->count(),
                'avg_score' => $group->avg('score'),
                'avg_cp' => $group->avg('combat_power'),
            ];
        });
    @endphp

    <style>
        .gvg-wide .container {
            max-width: 1400px;
        }
        .gvg-dashboard .gvg-kpi-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 10px;
        }
        .gvg-dashboard .gvg-kpi-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            background: #f8fafc;
        }
        .gvg-dashboard .gvg-kpi-label {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
        }
        .gvg-dashboard .gvg-kpi-value {
            margin: 6px 0 0;
            font-size: 1.2rem;
            font-weight: 700;
        }
        .gvg-dashboard .gvg-highlight-grid {
            margin-top: 14px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .gvg-dashboard .gvg-highlight-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            background: #fff;
        }
        .gvg-dashboard .gvg-highlight-card h4 {
            margin: 0 0 6px;
        }
        .gvg-dashboard .gvg-highlight-card p {
            margin: 0 0 4px;
            color: #64748b;
        }
        @media (max-width: 1100px) {
            .gvg-dashboard .gvg-kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 760px) {
            .gvg-dashboard .gvg-kpi-grid,
            .gvg-dashboard .gvg-highlight-grid {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>

    <div class="gvg-wide">
        <div class="card" style="margin-bottom: 16px;">
            <form method="GET" action="{{ route('gvg-weekly-stats.summary') }}" class="actions">
                <div class="field" style="margin: 0;">
                    <label for="week">สัปดาห์เริ่ม</label>
                    <select id="week" name="week" onchange="this.form.submit()">
                        @foreach ($weeks as $week)
                            <option value="{{ $week }}" @selected($selectedWeek === $week)>
                                {{ $week }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <a class="btn" href="{{ route('gvg-weekly-stats.index') }}">กลับ</a>
            </form>
            @if (!empty($selectedWeek))
                <form
                    method="POST"
                    action="{{ route('gvg-weekly-stats.summary.destroy-week') }}"
                    style="margin-top: 10px;"
                    onsubmit="return confirm('ยืนยันลบข้อมูล GVG ทั้งหมดของวันที่ {{ $selectedWeek }} ?');"
                >
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="week_start_date" value="{{ $selectedWeek }}">
                    <button type="submit" class="btn btn-danger">ลบข้อมูลทั้งวัน {{ $selectedWeek }}</button>
                </form>
            @endif
        </div>

        <div class="card">
            @if ($rows->isNotEmpty())
                <table class="datatable">
                    <thead>
                        <tr>
                            <th>อันดับ</th>
                            <th>สมาชิก</th>
                            <th>บทบาท</th>
                            <th>ฆ่า</th>
                            <th>ตาย</th>
                            <th>ชุบ</th>
                            <th>แต้มวอร์</th>
                            <th>คะแนนรวม</th>
                            <th>CP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $index => $row)
                            <tr>
                                <td>#{{ $index + 1 }}</td>
                                <td>{{ $row['member']?->name ?? '-' }}</td>
                                <td>{{ strtoupper($row['role']) }}</td>
                                <td>{{ $row['kills'] }}</td>
                                <td>{{ $row['deaths'] }}</td>
                                <td>{{ $row['revives'] }}</td>
                                <td>{{ $row['war_score'] }}</td>
                                <td>{{ number_format($row['score'], 1) }}</td>
                                <td>{{ number_format($row['combat_power'], 1) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="muted">ยังไม่มีข้อมูลในสัปดาห์ที่เลือก</div>
            @endif
        </div>

        @if ($rows->isNotEmpty())
            <div class="card gvg-dashboard" style="margin-top: 16px;">
                <h3 style="margin-top: 0;">Dashboard สัปดาห์ {{ $selectedWeek }}</h3>

                <div class="gvg-kpi-grid">
                    <div class="gvg-kpi-card">
                        <p class="gvg-kpi-label">ผู้ลงข้อมูล</p>
                        <p class="gvg-kpi-value">{{ number_format($participants) }}</p>
                    </div>
                    <div class="gvg-kpi-card">
                        <p class="gvg-kpi-label">คะแนนเฉลี่ย</p>
                        <p class="gvg-kpi-value">{{ number_format($avgScore, 1) }}</p>
                    </div>
                    <div class="gvg-kpi-card">
                        <p class="gvg-kpi-label">CP เฉลี่ย</p>
                        <p class="gvg-kpi-value">{{ number_format($avgCombatPower, 1) }}</p>
                    </div>
                    <div class="gvg-kpi-card">
                        <p class="gvg-kpi-label">K/D ทีม</p>
                        <p class="gvg-kpi-value">{{ number_format($teamKd, 2) }}</p>
                    </div>
                    <div class="gvg-kpi-card">
                        <p class="gvg-kpi-label">แต้มวอร์รวม</p>
                        <p class="gvg-kpi-value">{{ number_format($totalWarScore) }}</p>
                    </div>
                    <div class="gvg-kpi-card">
                        <p class="gvg-kpi-label">อัตราเข้าวอร์</p>
                        <p class="gvg-kpi-value">{{ number_format($warJoinRate, 1) }}%</p>
                    </div>
                    <div class="gvg-kpi-card">
                        <p class="gvg-kpi-label">ติดคูลดาวน์ 7 วัน</p>
                        <p class="gvg-kpi-value">{{ number_format($cooldownBlockedCount) }} คน</p>
                    </div>
                </div>

                <p class="muted" style="margin: 12px 0 0;">
                    หมายเหตุ: `แต้มวอร์ = 0` ถือว่าไม่ลงวอร์ ({{ $nonWarCount }} คน) |
                    ติดคูลดาวน์ 7 วัน {{ number_format($cooldownBlockedRate, 1) }}%
                    ({{ number_format($cooldownBlockedCount) }}/{{ number_format($activeMembersCount) }} คน)
                </p>

                <div class="gvg-highlight-grid">
                    <div class="gvg-highlight-card">
                        <h4>คะแนนสูงสุด</h4>
                        <p>{{ $topScoreRow['member']?->name ?? '-' }}</p>
                        <strong>{{ number_format($topScoreRow['score'] ?? 0, 1) }}</strong>
                    </div>
                    <div class="gvg-highlight-card">
                        <h4>CP สูงสุด</h4>
                        <p>{{ $topCombatPowerRow['member']?->name ?? '-' }}</p>
                        <strong>{{ number_format($topCombatPowerRow['combat_power'] ?? 0, 1) }}</strong>
                    </div>
                    <div class="gvg-highlight-card">
                        <h4>ชุบสูงสุด</h4>
                        <p>{{ $topReviveRow['member']?->name ?? '-' }}</p>
                        <strong>{{ number_format($topReviveRow['revives'] ?? 0) }}</strong>
                    </div>
                </div>

                <h4 style="margin-top: 18px;">สรุปตามบทบาท</h4>
                <table class="datatable">
                    <thead>
                        <tr>
                            <th>บทบาท</th>
                            <th>จำนวน</th>
                            <th>คะแนนเฉลี่ย</th>
                            <th>CP เฉลี่ย</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roleSummary as $role => $summary)
                            <tr>
                                <td>{{ strtoupper($role) }}</td>
                                <td>{{ number_format($summary['count']) }}</td>
                                <td>{{ number_format($summary['avg_score'] ?? 0, 1) }}</td>
                                <td>{{ number_format($summary['avg_cp'] ?? 0, 1) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if (!empty($selectedWeek))
            <div class="card" style="margin-top: 16px;">
                <h3 style="margin-top: 0;">รายชื่อคนที่ยังไม่มีผลงาน GVG ({{ $selectedWeek }})</h3>
                @if (($missingMembers ?? collect())->isNotEmpty())
                    <table class="datatable">
                        <thead>
                            <tr>
                                <th>ชื่อ</th>
                                <th>อาชีพ</th>
                                <th>Tier</th>
                                <th>บทบาท</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($missingMembers as $member)
                                <tr>
                                    <td>{{ $member['name'] }}</td>
                                    <td>{{ $member['job'] ?? '-' }}</td>
                                    <td>{{ strtoupper($member['tier'] ?? '-') }}</td>
                                    <td>{{ strtoupper($member['role'] ?? '-') }}</td>
                                    <td>{{ $member['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="muted">ทุกคนมีผลงาน GVG แล้วในสัปดาห์นี้</div>
                @endif
            </div>
        @endif
    </div>
@endsection
