@extends('layouts.app')

@section('title', 'รายละเอียดอาชีพ')

@section('content')
    <div class="card">
        <h2 style="margin-top: 0;">{{ $jobClass->name }}</h2>
        <p class="muted">Tier {{ $jobClass->tier }}</p>
        <p>อาชีพขั้นก่อนหน้า: <strong>{{ $jobClass->parent?->name ?? '-' }}</strong></p>

        @if ($jobClass->children->count() > 0)
            <p>อาชีพขั้นถัดไป:</p>
            <ul>
                @foreach ($jobClass->children as $child)
                    <li>{{ $child->name }} (Tier {{ $child->tier }})</li>
                @endforeach
            </ul>
        @endif

        <div class="actions" style="margin-top: 16px;">
            <a class="btn" href="{{ route('job-classes.edit', $jobClass) }}">แก้ไข</a>
            <a class="btn" href="{{ route('job-classes.index') }}">กลับ</a>
        </div>
    </div>
@endsection
