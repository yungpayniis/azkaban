@extends('layouts.app')

@section('title', 'สรุปคะแนน GVG รายสัปดาห์')

@php use App\Services\MemberColorService; @endphp
@section('content')
<style>
    .gvg-wide .container {
        max-width: 1400px;
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
                    @php $rowColor = MemberColorService::colorFor($row['member']); @endphp
                    <tr style="{{ $rowColor ? "background-color: {$rowColor} !important; color: #fff !important;" : '' }}">
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
@endsection
