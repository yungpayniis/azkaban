@extends('layouts.app')

@section('title', 'แก้ไขผลงาน GVG')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('gvg-weekly-stats.update', $gvgWeeklyStat) }}">
            @method('PUT')
            @include('gvg-weekly-stats._form', ['gvgWeeklyStat' => $gvgWeeklyStat])

            <div class="actions" style="margin-top: 16px;">
                <button class="btn btn-primary" type="submit">บันทึก</button>
                <a class="btn" href="{{ route('gvg-weekly-stats.index') }}">กลับ</a>
            </div>
        </form>
    </div>
@endsection
