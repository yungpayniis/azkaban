@extends('layouts.app')

@section('title', 'ผลงาน GVG รายสัปดาห์')

@section('content')
<style>
    .gvg-wide .container {
        max-width: 1400px;
    }
</style>
<div class="gvg-wide">
    <div class="actions" style="margin-bottom: 16px;">
        <a class="btn btn-primary" href="{{ route('gvg-weekly-stats.create') }}">เพิ่มผลงาน GVG</a>
        <a class="btn" href="{{ route('gvg-weekly-stats.import-json.form') }}">นำเข้า JSON</a>
        <a class="btn" href="{{ route('gvg-weekly-stats.summary') }}">สรุปคะแนน GVG</a>
        <a class="btn" href="{{ route('guild-members.index') }}">สมาชิกกิล</a>
    </div>

    <div class="card">
        <table class="datatable">
            <thead>
                <tr>
                    <th>สมาชิก</th>
                    <th>สัปดาห์เริ่ม</th>
                    <th>ฆ่า</th>
                    <th>ตาย</th>
                    <th>ชุบ</th>
                    <th>แต้มวอร์</th>
                    <th>คะแนนอัตโนมัติ</th>
                    <th>CP</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($stats as $stat)
                    <tr>
                        <td>{{ $stat->guildMember?->name ?? '-' }}</td>
                        <td>{{ $stat->week_start_date->format('Y-m-d') }}</td>
                        <td>{{ $stat->kills }}</td>
                        <td>{{ $stat->deaths }}</td>
                        <td>{{ $stat->revives }}</td>
                        <td>{{ $stat->war_score }}</td>
                        <td>{{ number_format($stat->calculatedScoreAuto(), 1) }}</td>
                        <td>{{ number_format($stat->calculatedCombatPower(), 1) }}</td>
                        <td class="actions">
                            <a class="btn" href="{{ route('gvg-weekly-stats.show', $stat) }}">ดู</a>
                            <a class="btn" href="{{ route('gvg-weekly-stats.edit', $stat) }}">แก้ไข</a>
                            <form method="POST" action="{{ route('gvg-weekly-stats.destroy', $stat) }}" onsubmit="return confirm('ยืนยันการลบ?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">ลบ</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="muted">ยังไม่มีผลงาน GVG</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
</div>
@endsection
