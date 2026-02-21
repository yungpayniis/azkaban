@extends('layouts.app')

@section('title', 'สมาชิกที่ออกจากกิล')

@section('content')
    <div class="actions" style="margin-bottom: 16px;">
        <a class="btn" href="{{ route('guild-members.index') }}">กลับหน้าสมาชิกกิล</a>
    </div>

    @php use App\Services\MemberColorService; @endphp
    <div class="card">
        <div class="table-wrap">
            <table class="datatable guild-members-table">
                <thead>
                    <tr>
                        <th>ชื่อ</th>
                        <th>อาชีพ</th>
                        <th>ระดับ</th>
                        <th>บทบาท</th>
                        <th>ใบแดง</th>
                        <th>สัญชาติ</th>
                        <th>วันที่เข้ากิล</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        @php
                            $rowColor = MemberColorService::colorFor($member);
                            $textColor = MemberColorService::textColorFor($member);
                        @endphp
                        <tr
                            onclick="window.location='{{ route('guild-members.show', $member) }}'"
                            style="cursor: pointer; {{ $rowColor ? "background-color: {$rowColor} !important; color: {$textColor} !important;" : '' }}"
                        >
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->jobClass?->name ?? '-' }}</td>
                            <td>{{ ucfirst($member->tier) }}</td>
                            <td>{{ strtoupper($member->role ?? 'dps') }}</td>
                            <td>{{ $member->red_cards_count ?? 0 }}/3</td>
                            <td>{{ $member->nationality === 'foreign' ? 'ต่างชาติ' : 'คนไทย' }}</td>
                            <td>{{ optional($member->joined_at)->format('Y-m-d') ?? '-' }}</td>
                            <td class="actions" onclick="event.stopPropagation();">
                                <a class="btn" href="{{ route('guild-members.show', $member) }}">ดู</a>
                                <a class="btn" href="{{ route('guild-members.edit', $member) }}">แก้ไข</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="muted">ยังไม่มีสมาชิกที่สถานะออกจากกิล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .table-wrap {
            overflow-x: auto;
        }
        .guild-members-table {
            width: 100%;
            min-width: 1000px;
        }
    </style>
@endsection
