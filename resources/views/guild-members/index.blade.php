@extends('layouts.app')

@section('title', 'สมาชิกกิล')

@section('content')
    <div class="actions" style="margin-bottom: 16px;">
        <a class="btn btn-primary" href="{{ route('guild-members.create') }}">เพิ่มสมาชิก</a>
        <a class="btn" href="{{ route('party-planner.index') }}">ไปหน้าจัดปาร์ตี้</a>
        <a class="btn" href="{{ route('guild-members.left') }}">ดูสมาชิกที่ออกจากกิล</a>
    </div>
    <p class="muted" style="margin-bottom: 12px;">เช็กปุ่มเพิ่มผลงานจากเสาร์ล่าสุด: {{ $latestSaturday->format('Y-m-d') }}</p>

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
                    <th>ใบแดง</th>
                    <th>สถานะ</th>
                    <th>สัญชาติ</th>
                    <th>ผลงาน GVG เสาร์ล่าสุด</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                @php
                    $rowColor = MemberColorService::colorFor($member);
                    $textColor = MemberColorService::textColorFor($member);
                    $missingLatestSaturdayGvg = (($member->latest_saturday_gvg_count ?? 0) === 0);
                @endphp
                <tr onclick="window.location='{{ route('guild-members.show', $member) }}'"
                    style="cursor: pointer; {{ $rowColor ? "background-color: {$rowColor} !important; color: {$textColor} !important;" : '' }}">
                        <td>{{ $member->name }}</td>
                        <td>{{ $member->jobClass?->name ?? '-' }}</td>
                        <td>{{ ucfirst($member->tier) }}</td>
                        <td>{{ strtoupper($member->role ?? 'dps') }}</td>
                        <td>{{ $member->red_cards_count ?? 0 }}/3</td>
                        <td>{{ $member->status === 'left' ? 'ออกแล้ว' : 'Active' }}</td>
                        <td>{{ $member->nationality === 'foreign' ? 'ต่างชาติ' : 'คนไทย' }}</td>
                        <td data-order="{{ $missingLatestSaturdayGvg ? 0 : 1 }}" onclick="event.stopPropagation();">
                            @if ($missingLatestSaturdayGvg)
                                <button
                                    type="button"
                                    class="btn open-gvg-modal-btn"
                                    data-member-id="{{ $member->id }}"
                                    data-member-name="{{ $member->name }}"
                                    data-week-start-date="{{ $latestSaturday->format('Y-m-d') }}"
                                >
                                    เพิ่มผลงาน GVG
                                </button>
                            @else
                                <span class="muted">เพิ่มแล้ว</span>
                            @endif
                        </td>
                        <td class="actions" onclick="event.stopPropagation();">
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
                        <td colspan="9" class="muted">ยังไม่มีสมาชิก</td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>
    </div>

    <div id="gvg-modal-backdrop" class="gvg-modal-backdrop" hidden>
        <div class="gvg-modal" role="dialog" aria-modal="true" aria-labelledby="gvg-modal-title">
            <div class="gvg-modal-header">
                <h3 id="gvg-modal-title">เพิ่มผลงาน GVG</h3>
                <button type="button" class="btn" id="close-gvg-modal">ปิด</button>
            </div>
            <p class="muted" id="gvg-modal-member-name" style="margin-top: 0;"></p>
            <form method="POST" action="{{ route('gvg-weekly-stats.store') }}">
                @csrf
                <input type="hidden" name="guild_member_id" id="gvg-modal-member-id" required>
                <input type="hidden" name="week_start_date" id="gvg-modal-week-start-date" value="{{ $latestSaturday->format('Y-m-d') }}" required>
                <input type="hidden" name="stay_on_page" value="1">

                <div class="field">
                    <label for="gvg-kills">จำนวนฆ่า</label>
                    <input id="gvg-kills" name="kills" type="number" min="0" value="0" required>
                </div>
                <div class="field">
                    <label for="gvg-deaths">จำนวนตาย</label>
                    <input id="gvg-deaths" name="deaths" type="number" min="0" value="0" required>
                </div>
                <div class="field">
                    <label for="gvg-revives">จำนวนชุบ</label>
                    <input id="gvg-revives" name="revives" type="number" min="0" value="0" required>
                </div>
                <div class="field">
                    <label for="gvg-war-score">แต้มวอร์</label>
                    <input id="gvg-war-score" name="war_score" type="number" min="0" value="0" required>
                </div>

                <div class="actions" style="margin-top: 16px;">
                    <button class="btn btn-primary" type="submit">บันทึกผลงาน</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const gvgModalBackdrop = document.getElementById('gvg-modal-backdrop');
        const closeGvgModalBtn = document.getElementById('close-gvg-modal');
        const gvgModalMemberId = document.getElementById('gvg-modal-member-id');
        const gvgModalWeekStartDate = document.getElementById('gvg-modal-week-start-date');
        const gvgModalMemberName = document.getElementById('gvg-modal-member-name');

        function openGvgModal(memberId, memberName, weekStartDate) {
            gvgModalMemberId.value = memberId;
            gvgModalWeekStartDate.value = weekStartDate;
            gvgModalMemberName.textContent = `สมาชิก: ${memberName} | สัปดาห์: ${weekStartDate}`;
            gvgModalBackdrop.hidden = false;
        }

        function closeGvgModal() {
            gvgModalBackdrop.hidden = true;
        }

        document.querySelectorAll('.open-gvg-modal-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                openGvgModal(btn.dataset.memberId, btn.dataset.memberName, btn.dataset.weekStartDate);
            });
        });

        closeGvgModalBtn.addEventListener('click', closeGvgModal);
        document.addEventListener('keydown', (evt) => {
            if (evt.key === 'Escape' && !gvgModalBackdrop.hidden) {
                closeGvgModal();
            }
        });
    </script>

    <style>
        .table-wrap {
            overflow-x: auto;
        }
        .guild-members-table {
            width: 100%;
            min-width: 1100px;
        }
        .gvg-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            z-index: 1000;
        }
        .gvg-modal-backdrop[hidden] {
            display: none;
        }
        .gvg-modal {
            width: min(520px, 100%);
            background: #fff;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.25);
        }
        .gvg-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }
    </style>
@endsection
