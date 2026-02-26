@extends('layouts.app')

@section('title', 'รายชื่อปาร์ตี้')

@section('content')
    @php use App\Services\MemberColorService; @endphp

    <div class="view-header">
        <h1>รายชื่อปาร์ตี้</h1>
        <a class="btn" href="{{ route('party-planner.index') }}">กลับหน้าจัดปาร์ตี้</a>
    </div>

    @if ($parties->isNotEmpty())
        <div class="party-grid">
            @foreach ($parties as $party)
                <section class="party-card">
                    <h2>{{ $party->name }}</h2>
                    <div class="slots">
                        @foreach ($party->slots->sortBy('position')->values() as $slot)
                            @if ($slot->member)
                                @php
                                    $color = MemberColorService::colorFor($slot->member);
                                    $textColor = MemberColorService::textColorFor($slot->member);
                                @endphp
                                <div class="member-card" style="{{ $color ? "background: {$color}; color: {$textColor};" : '' }}">
                                    {{ $slot->member->name }}
                                </div>
                            @else
                                <div class="member-card empty">ว่าง</div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <p class="muted">ยังไม่มีปาร์ตี้</p>
    @endif

    <style>
        .view-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        .view-header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .party-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .party-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
        }
        .party-card h2 {
            margin: 0 0 10px;
            font-size: 1.1rem;
            text-align: center;
        }
        .slots {
            display: grid;
            grid-template-rows: repeat(5, minmax(0, 1fr));
            grid-auto-flow: column;
            grid-auto-columns: minmax(0, 1fr);
            gap: 8px;
        }
        .member-card {
            background: #0f766e;
            color: #fff;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
            font-weight: 600;
            line-height: 1.3;
        }
        .member-card.empty {
            background: #e2e8f0;
            color: #64748b;
        }
        @media (max-width: 960px) {
            .party-grid {
                grid-template-columns: 1fr;
            }
            .slots {
                grid-template-rows: repeat(5, minmax(0, 1fr));
                grid-auto-flow: column;
                grid-auto-columns: minmax(0, 1fr);
            }
        }
        @media (max-width: 640px) {
            .slots {
                grid-template-rows: repeat(5, minmax(0, 1fr));
                grid-auto-flow: column;
                grid-auto-columns: minmax(0, 1fr);
            }
        }
    </style>
@endsection
