@extends('layouts.app')

@section('title', 'อาชีพ')

@section('content')
    <div class="actions" style="margin-bottom: 16px;">
        <a class="btn btn-primary" href="{{ route('job-classes.create') }}">เพิ่มอาชีพ</a>
    </div>

    <div class="card">
        <table class="datatable">
            <thead>
                <tr>
                    <th>Tier</th>
                    <th>ชื่ออาชีพ</th>
                    <th>สี</th>
                    <th>อาชีพขั้นก่อนหน้า</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobClasses as $jobClass)
                    <tr>
                        <td>Tier {{ $jobClass->tier }}</td>
                        <td>{{ $jobClass->name }}</td>
                        <td>
                            @if ($jobClass->color)
                                <span class="color-pill" style="background: {{ $jobClass->color }};"></span> {{ $jobClass->color }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $jobClass->parent?->name ?? '-' }}</td>
                        <td class="actions">
                            <a class="btn" href="{{ route('job-classes.show', $jobClass) }}">ดู</a>
                            <a class="btn" href="{{ route('job-classes.edit', $jobClass) }}">แก้ไข</a>
                            <form method="POST" action="{{ route('job-classes.destroy', $jobClass) }}" onsubmit="return confirm('ยืนยันการลบ?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">ลบ</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">ยังไม่มีอาชีพ</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <style>
        .color-pill {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
            border: 1px solid #cbd5f5;
        }
    </style>
@endsection
