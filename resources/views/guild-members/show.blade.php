@extends('layouts.app')

@section('title', 'รายละเอียดสมาชิก')

@section('content')
    <div class="card">
        <h2 style="margin-top: 0;">{{ $guildMember->name }}</h2>
        <p>อาชีพ: <strong>{{ $guildMember->jobClass?->name ?? '-' }}</strong></p>
        <p>ระดับ: <strong>{{ ucfirst($guildMember->tier) }}</strong></p>
        <p>บทบาท: <strong>{{ strtoupper($guildMember->role ?? 'dps') }}</strong></p>
        <p>สัญชาติ: <strong>{{ $guildMember->nationality === 'foreign' ? 'ต่างชาติ' : 'คนไทย' }}</strong></p>
        <p>วันที่เข้ากิล: <strong>{{ $guildMember->joined_at->format('Y-m-d H:i') }}</strong></p>
        <p>สถานะ: <strong>{{ $guildMember->status === 'left' ? 'ออกจากกิลแล้ว' : 'Active' }}</strong></p>

        @if ($guildMember->stat)
            <h3>ค่าสเตตัสล่าสุด</h3>
            <table>
                <tbody>
                    <tr>
                        <th>STR</th><td>{{ $guildMember->stat->str }}</td>
                        <th>VIT</th><td>{{ $guildMember->stat->vit }}</td>
                        <th>LUK</th><td>{{ $guildMember->stat->luk }}</td>
                    </tr>
                    <tr>
                        <th>AGI</th><td>{{ $guildMember->stat->agi }}</td>
                        <th>DEX</th><td>{{ $guildMember->stat->dex }}</td>
                        <th>INT</th><td>{{ $guildMember->stat->int }}</td>
                    </tr>
                    <tr>
                        <th>HP</th><td>{{ $guildMember->stat->hp }}</td>
                        <th>SP</th><td>{{ $guildMember->stat->sp }}</td>
                        <th>P.ATK</th><td>{{ $guildMember->stat->patk }}</td>
                    </tr>
                    <tr>
                        <th>M.ATK</th><td>{{ $guildMember->stat->matk }}</td>
                        <th>P.DEF</th><td>{{ $guildMember->stat->pdef }}</td>
                        <th>M.DEF</th><td>{{ $guildMember->stat->mdef }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        @if ($guildMember->gvgWeeklyStats->count() > 0)
            @php $latestGvg = $guildMember->gvgWeeklyStats->sortByDesc('week_start_date')->first(); @endphp
            <p>CP (ผลงาน GVG ล่าสุด): <strong>{{ number_format($latestGvg?->calculatedCombatPower() ?? 0, 1) }}</strong></p>
            <h3>ผลงาน GVG ล่าสุด</h3>
            <table class="datatable">
                <thead>
                    <tr>
                        <th>สัปดาห์เริ่ม</th>
                        <th>ฆ่า</th>
                        <th>ตาย</th>
                        <th>ชุบ</th>
                        <th>แต้มวอร์</th>
                        <th>CP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($guildMember->gvgWeeklyStats->sortByDesc('week_start_date')->take(5) as $stat)
                        <tr>
                            <td>{{ $stat->week_start_date->format('Y-m-d') }}</td>
                            <td>{{ $stat->kills }}</td>
                            <td>{{ $stat->deaths }}</td>
                            <td>{{ $stat->revives }}</td>
                            <td>{{ $stat->war_score }}</td>
                            <td>{{ number_format($stat->calculatedCombatPower(), 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if ($guildMember->nameHistories->count() > 0)
            <div style="margin-top: 16px;">
                <h3>ประวัติชื่อเดิม</h3>
                <ul>
                    @foreach ($guildMember->nameHistories->sortByDesc('recorded_at') as $history)
                        <li>{{ $history->name }} ({{ $history->recorded_at->format('Y-m-d H:i') }})</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($guildMember->statHistories->count() > 0)
            <div style="margin-top: 16px;">
                <h3>ประวัติสเตตัส</h3>
                <table class="datatable">
                    <thead>
                        <tr>
                            <th>เวลา</th>
                            <th>STR</th>
                            <th>VIT</th>
                            <th>LUK</th>
                            <th>AGI</th>
                            <th>DEX</th>
                            <th>INT</th>
                            <th>HP</th>
                            <th>SP</th>
                            <th>P.ATK</th>
                            <th>M.ATK</th>
                            <th>P.DEF</th>
                            <th>M.DEF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guildMember->statHistories->sortByDesc('recorded_at') as $history)
                            <tr>
                                <td>{{ $history->recorded_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $history->str }}</td>
                                <td>{{ $history->vit }}</td>
                                <td>{{ $history->luk }}</td>
                                <td>{{ $history->agi }}</td>
                                <td>{{ $history->dex }}</td>
                                <td>{{ $history->int }}</td>
                                <td>{{ $history->hp }}</td>
                                <td>{{ $history->sp }}</td>
                                <td>{{ $history->patk }}</td>
                                <td>{{ $history->matk }}</td>
                                <td>{{ $history->pdef }}</td>
                                <td>{{ $history->mdef }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 16px;">
                <button type="button" class="btn" id="toggle-stat-chart">ดูกราฟพัฒนาการ</button>
                <div id="stat-chart-section" style="display: none; margin-top: 12px;">
                    <canvas id="stat-chart-canvas" width="900" height="320" style="width: 100%; max-width: 900px;"></canvas>
                </div>
            </div>
        @endif

        <div class="actions" style="margin-top: 16px;">
            <a class="btn" href="{{ route('guild-members.edit', $guildMember) }}">แก้ไข</a>
            <a class="btn" href="{{ route('guild-members.index') }}">กลับ</a>
        </div>
    </div>

    @if ($guildMember->statHistories->count() > 0)
    @php
        $statHistoryJson = $guildMember->statHistories
            ->sortBy('recorded_at')
            ->values()
            ->map(function ($history) {
                return [
                    'recorded_at' => optional($history->recorded_at)->format('Y-m-d H:i'),
                    'str' => $history->str,
                    'vit' => $history->vit,
                    'luk' => $history->luk,
                    'agi' => $history->agi,
                    'dex' => $history->dex,
                    'int' => $history->int,
                    'hp' => $history->hp,
                    'sp' => $history->sp,
                    'patk' => $history->patk,
                    'matk' => $history->matk,
                    'pdef' => $history->pdef,
                    'mdef' => $history->mdef,
                ];
            })
            ->all();
    @endphp
    <script>
        (function () {
            const rawHistories = @json($statHistoryJson);

            if (!rawHistories.length) return;

            const toggleBtn = document.getElementById('toggle-stat-chart');
            const section = document.getElementById('stat-chart-section');
            const canvas = document.getElementById('stat-chart-canvas');
            const ctx = canvas.getContext('2d');

            function computeOverall(row) {
                const baseStatMax = Math.max(
                    Number(row.str || 0),
                    Number(row.vit || 0),
                    Number(row.luk || 0),
                    Number(row.agi || 0),
                    Number(row.dex || 0),
                    Number(row.int || 0)
                );
                const hpScore = Number(row.hp || 0) / 100;
                const atkScore = Math.max(Number(row.patk || 0), Number(row.matk || 0));
                const defScore = Number(row.pdef || 0) + Number(row.mdef || 0);
                return (baseStatMax * 0.3) + (hpScore * 0.25) + (atkScore * 0.25) + (defScore * 0.2);
            }

            function drawChart() {
                const labels = rawHistories.map((row) => row.recorded_at || '');
                const values = rawHistories.map((row) => computeOverall(row));

                const padding = { top: 20, right: 20, bottom: 40, left: 50 };
                const width = canvas.width;
                const height = canvas.height;
                const plotWidth = width - padding.left - padding.right;
                const plotHeight = height - padding.top - padding.bottom;

                const minVal = Math.min(...values);
                const maxVal = Math.max(...values);
                const range = Math.max(maxVal - minVal, 1);

                ctx.clearRect(0, 0, width, height);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, width, height);

                ctx.strokeStyle = '#e5e7eb';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(padding.left, padding.top);
                ctx.lineTo(padding.left, height - padding.bottom);
                ctx.lineTo(width - padding.right, height - padding.bottom);
                ctx.stroke();

                const tickCount = 4;
                ctx.fillStyle = '#6b7280';
                ctx.font = '12px sans-serif';
                for (let i = 0; i <= tickCount; i++) {
                    const y = padding.top + (plotHeight * i) / tickCount;
                    const value = Math.round(maxVal - (range * i) / tickCount);
                    ctx.strokeStyle = '#f1f5f9';
                    ctx.beginPath();
                    ctx.moveTo(padding.left, y);
                    ctx.lineTo(width - padding.right, y);
                    ctx.stroke();
                    ctx.fillText(String(value), 8, y + 4);
                }

                const count = values.length;
                const step = count > 1 ? plotWidth / (count - 1) : 0;
                ctx.strokeStyle = '#0f766e';
                ctx.lineWidth = 2;
                ctx.beginPath();
                values.forEach((value, index) => {
                    const x = padding.left + step * index;
                    const y = padding.top + ((maxVal - value) / range) * plotHeight;
                    if (index === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
                });
                ctx.stroke();

                ctx.fillStyle = '#0f766e';
                values.forEach((value, index) => {
                    const x = padding.left + step * index;
                    const y = padding.top + ((maxVal - value) / range) * plotHeight;
                    ctx.beginPath();
                    ctx.arc(x, y, 3, 0, Math.PI * 2);
                    ctx.fill();
                });

                ctx.fillStyle = '#6b7280';
                ctx.font = '11px sans-serif';
                labels.forEach((label, index) => {
                    if (count > 8 && index % Math.ceil(count / 8) !== 0 && index !== count - 1) return;
                    const x = padding.left + step * index;
                    const y = height - padding.bottom + 16;
                    ctx.save();
                    ctx.translate(x, y);
                    ctx.rotate(-0.35);
                    ctx.fillText(label, 0, 0);
                    ctx.restore();
                });
            }

            function openSection() {
                section.style.display = 'block';
                drawChart();
            }

            toggleBtn.addEventListener('click', () => {
                if (section.style.display === 'block') {
                    section.style.display = 'none';
                } else {
                    openSection();
                }
            });

        })();
    </script>
    @endif
@endsection
