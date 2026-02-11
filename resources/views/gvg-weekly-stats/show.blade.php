@extends('layouts.app')

@section('title', 'รายละเอียดผลงาน GVG')

@section('content')
    <div class="card">
        <h2 style="margin-top: 0;">{{ $gvgWeeklyStat->guildMember?->name ?? '-' }}</h2>
        <p>สัปดาห์เริ่ม: <strong>{{ $gvgWeeklyStat->week_start_date->format('Y-m-d') }}</strong></p>
        <p>จำนวนฆ่า: <strong>{{ $gvgWeeklyStat->kills }}</strong></p>
        <p>จำนวนตาย: <strong>{{ $gvgWeeklyStat->deaths }}</strong></p>
        <p>จำนวนชุบ: <strong>{{ $gvgWeeklyStat->revives }}</strong></p>
        <p>แต้มวอร์: <strong>{{ $gvgWeeklyStat->war_score }}</strong></p>
        <p>คะแนนอัตโนมัติ: <strong>{{ number_format($gvgWeeklyStat->calculatedScoreAuto(), 1) }}</strong></p>
        <p>CP: <strong>{{ number_format($gvgWeeklyStat->calculatedCombatPower(), 1) }}</strong></p>

        <div class="actions" style="margin-top: 16px;">
            <a class="btn" href="{{ route('gvg-weekly-stats.edit', $gvgWeeklyStat) }}">แก้ไข</a>
            <a class="btn" href="{{ route('gvg-weekly-stats.index') }}">กลับ</a>
        </div>
    </div>
@endsection
