@extends('layouts.app')

@section('title', 'สมาชิกกิล')

@section('content')
    <div class="actions" style="margin-bottom: 16px;">
        <a class="btn btn-primary" href="{{ route('guild-members.create') }}">เพิ่มสมาชิก</a>
        <a class="btn" href="{{ route('party-planner.index') }}">ไปหน้าจัดปาร์ตี้</a>
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
                    <th>สถานะ</th>
                    <th>สัญชาติ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                @php
                    $rowColor = MemberColorService::colorFor($member);
                    $textColor = MemberColorService::textColorFor($member);
                @endphp
                <tr onclick="window.location='{{ route('gvg-weekly-stats.create', ['member_id' => $member->id]) }}'"
                    style="cursor: pointer; {{ $rowColor ? "background-color: {$rowColor} !important; color: {$textColor} !important;" : '' }}">
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->jobClass?->name ?? '-' }}</td>
                        <td>{{ ucfirst($member->tier) }}</td>
                        <td>{{ strtoupper($member->role ?? 'dps') }}</td>
                        <td>{{ $member->status === 'left' ? 'ออกแล้ว' : 'Active' }}</td>
                        <td>{{ $member->nationality === 'foreign' ? 'ต่างชาติ' : 'คนไทย' }}</td>
                        <td class="actions" onclick="event.stopPropagation();">
                            <a class="btn" href="{{ route('gvg-weekly-stats.create', ['member_id' => $member->id]) }}">เพิ่มผลงาน GVG</a>
                            <a class="btn" href="{{ route('guild-members.show', $member) }}">ดู</a>
                            <a class="btn" href="{{ route('guild-members.edit', $member) }}">แก้ไข</a>
                            <form method="POST" action="{{ route('guild-members.destroy', $member) }}" onsubmit="return confirm('ยืนยันการลบ?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">ลบ</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">ยังไม่มีสมาชิก</td>
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
            min-width: 1100px;
        }
    </style>
@endsection
