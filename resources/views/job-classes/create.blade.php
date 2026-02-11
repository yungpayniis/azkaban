@extends('layouts.app')

@section('title', 'เพิ่มอาชีพ')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('job-classes.store') }}">
            @include('job-classes._form', ['jobClass' => null])

            <div class="actions" style="margin-top: 16px;">
                <button class="btn btn-primary" type="submit">บันทึก</button>
                <a class="btn" href="{{ route('job-classes.index') }}">กลับ</a>
            </div>
        </form>
    </div>
@endsection
