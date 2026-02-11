@extends('layouts.app')

@section('title', 'แก้ไขสมาชิก')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('guild-members.update', $guildMember) }}">
            @method('PUT')
            @include('guild-members._form', ['guildMember' => $guildMember])

            <div class="actions" style="margin-top: 16px;">
                <button class="btn btn-primary" type="submit">บันทึก</button>
                <a class="btn" href="{{ route('guild-members.index') }}">กลับ</a>
            </div>
        </form>
    </div>
@endsection
