@extends('layouts.app')

@section('title', 'เพิ่มสมาชิก')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('guild-members.store') }}">
            @include('guild-members._form', ['guildMember' => null])

            <div class="actions" style="margin-top: 16px;">
                <button class="btn btn-primary" type="submit">บันทึก</button>
                <a class="btn" href="{{ route('guild-members.index') }}">กลับ</a>
            </div>
        </form>
    </div>
@endsection
