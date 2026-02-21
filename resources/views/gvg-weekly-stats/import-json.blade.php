@extends('layouts.app')

@section('title', 'นำเข้าผลงาน GVG จาก JSON')

@section('content')
    <div class="actions" style="margin-bottom: 16px;">
        <a class="btn" href="{{ route('gvg-weekly-stats.index') }}">กลับหน้ารายการ GVG</a>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">นำเข้าผลงาน GVG แบบ JSON</h2>
        <p class="muted">รองรับ array ของรายการ โดยใช้คีย์ `member`, `battle_stats` (ฆ่า/ตาย/ชุบ) และ `score`</p>

        <form method="POST" action="{{ route('gvg-weekly-stats.import-json.store') }}">
            @csrf

            <div class="field">
                <label for="week_start_date">สัปดาห์เริ่ม (วันเสาร์)</label>
                <input
                    id="week_start_date"
                    name="week_start_date"
                    type="date"
                    value="{{ old('week_start_date', $defaultWeekStartDate) }}"
                    required
                >
                @error('week_start_date')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="json_payload">JSON</label>
                <textarea
                    id="json_payload"
                    name="json_payload"
                    rows="16"
                    style="width: 100%; font-family: monospace;"
                    placeholder='[{"member":"ชื่อสมาชิก","battle_stats":"10/20/3","score":1500}]'
                    required
                >{{ old('json_payload') }}</textarea>
                @error('json_payload')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="actions" style="margin-top: 16px;">
                <button class="btn btn-primary" type="submit">นำเข้าข้อมูล</button>
            </div>
        </form>

        @if (session('import_issues'))
            <div style="margin-top: 16px;">
                <h3>รายการที่ข้าม/มีปัญหา</h3>
                <ul>
                    @foreach (session('import_issues') as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('import_match_notes'))
            <div style="margin-top: 16px;">
                <h3>รายการที่ระบบจับคู่ชื่ออัตโนมัติ</h3>
                <ul>
                    @foreach (session('import_match_notes') as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
