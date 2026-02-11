@extends('layouts.app')

@section('title', 'แก้ไขอาชีพ')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('job-classes.update', $jobClass) }}">
            @method('PUT')
            @include('job-classes._form', ['jobClass' => $jobClass])

            <div class="actions" style="margin-top: 16px;">
                <button class="btn btn-primary" type="submit">บันทึก</button>
                <a class="btn" href="{{ route('job-classes.index') }}">กลับ</a>
            </div>
        </form>
    </div>
@endsection
