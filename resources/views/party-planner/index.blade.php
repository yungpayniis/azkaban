@extends('layouts.app')

@section('title', 'จัดปาร์ตี้กิล')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="planner-header">
        <div class="planner-title">
            <p class="muted">ลากชื่อสมาชิกลงช่องของแต่ละ pt (5x3) แล้วระบบจะช่วยสลับตำแหน่งให้</p>
        </div>
        <div class="planner-actions">
            <form class="add-party-form" method="POST" action="{{ route('party-planner.parties.store') }}">
                @csrf
                <input type="text" name="name" placeholder="ชื่อ pt (ไม่บังคับ)">
                <button type="submit">เพิ่ม pt</button>
            </form>
            <button type="button" class="btn" id="auto-assign-btn">จัดปาร์ตี้ออโต้</button>
        </div>
    </div>

    <div class="planner-split">
    @php use App\Services\MemberColorService; @endphp
    <div class="planner-left">
            <div class="party-grid">
                @foreach ($parties as $party)
                    <div class="party-card">
                    <div class="party-card-header">
                        <div class="party-name-display" data-party-name>
                            <span class="party-name-text">{{ $party->name }}</span>
                            <button type="button" class="btn btn-sm btn-icon" data-edit-party-name aria-label="แก้ไขชื่อปาร์ตี้">
                                ✎
                            </button>
                        </div>
                        <form class="party-name-form" method="POST" action="{{ route('party-planner.parties.update', $party) }}" data-party-form hidden>
                            @csrf
                            @method('PATCH')
                            <input type="text" name="name" value="{{ $party->name }}" aria-label="ชื่อปาร์ตี้">
                            <button type="submit" class="btn btn-sm">บันทึก</button>
                        </form>
                        <form class="delete-pt-form" method="POST" action="{{ route('party-planner.parties.destroy', $party) }}" onsubmit="return confirm('ลบปาร์ตี้ {{ $party->name }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">ลบ pt</button>
                        </form>
                    </div>
                        <div class="slots">
                            @foreach ($party->slots as $slot)
                                <div class="slot" data-slot-id="{{ $slot->id }}">
                    @if ($slot->member)
                        @php
                            $color = MemberColorService::colorFor($slot->member);
                            $textColor = MemberColorService::textColorFor($slot->member);
                            $eligible = $slot->member->joined_at ? $slot->member->joined_at->diffInDays(now()) >= 7 : false;
                        @endphp
                        <div class="member-card {{ $eligible ? '' : 'ineligible' }}" data-member-id="{{ $slot->member->id }}"
                            style="{{ $color ? "background: {$color}; color: {$textColor};" : '' }}"
                            data-eligible="{{ $eligible ? '1' : '0' }}">
                            {{ $slot->member->name }}
                        </div>
                                    @else
                                        <div class="member-card empty">ว่าง</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="planner-right">
            <h3>สมาชิกยังไม่มี pt</h3>
            <div class="field filters">
                <label for="tier-filter">Tier</label>
                <select id="tier-filter">
                    <option value="">ทั้งหมด</option>
                    @foreach (['low' => 'Low', 'middle' => 'Middle', 'top' => 'Top'] as $value => $label)
                        <option value="tier-{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field filters">
                <label for="job-class-filter">อาชีพ</label>
                <select id="job-class-filter">
                    <option value="">ทั้งหมด</option>
                    @foreach ($jobClasses as $jobClass)
                        <option value="{{ $jobClass->id }}">{{ 'Tier ' . $jobClass->tier }} - {{ $jobClass->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="text" id="unassigned-search" placeholder="ค้นหาชื่อสมาชิก..." autocomplete="off">
            <div id="unassigned-pool">
                @forelse ($unassignedMembers as $member)
                    @php
                        $color = MemberColorService::colorFor($member);
                        $textColor = MemberColorService::textColorFor($member);
                        $eligible = $member->joined_at ? $member->joined_at->diffInDays(now()) >= 7 : false;
                    @endphp
                    <div class="member-card {{ $eligible ? '' : 'ineligible' }}" data-member-id="{{ $member->id }}"
                        data-tier="tier-{{ $member->tier ?? '' }}"
                        data-job-class="{{ $member->jobClass->id ?? '' }}"
                        style="{{ $color ? "background: {$color}; color: {$textColor};" : '' }}"
                        data-eligible="{{ $eligible ? '1' : '0' }}">
                        {{ $member->name }}
                    </div>
                @empty
                    <div class="muted">ไม่มีสมาชิกว่าง</div>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const unassignedPool = document.getElementById('unassigned-pool');
        const searchInput = document.getElementById('unassigned-search');
        const plannerLeft = document.querySelector('.planner-left');
        const plannerRight = document.querySelector('.planner-right');
        let dragSourceSlotId = null;
        let isDragging = false;
        let currentPointerX = 0;
        let currentPointerY = 0;
        let scrollFrame = null;
        const scrollThreshold = 120;
        const scrollSpeed = 24;

        function getMemberId(card) {
            return card && card.dataset.memberId ? parseInt(card.dataset.memberId, 10) : null;
        }

        function syncSlot(slot) {
            const card = slot.querySelector('.member-card');
            if (!card || card.classList.contains('empty')) {
                return { slot_id: parseInt(slot.dataset.slotId, 10), member_id: null };
            }
            return { slot_id: parseInt(slot.dataset.slotId, 10), member_id: getMemberId(card) };
        }

        function saveUpdates(updates) {
            fetch("{{ route('party-planner.slots.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ updates }),
            });
        }

        function ensureEmptyPlaceholder(slot) {
            const cards = slot.querySelectorAll('.member-card');
            const hasReal = Array.from(cards).some((card) => !card.classList.contains('empty'));
            const emptyCard = slot.querySelector('.member-card.empty');

            if (hasReal && emptyCard) {
                emptyCard.remove();
            }

            if (!hasReal && !emptyCard) {
                const placeholder = document.createElement('div');
                placeholder.className = 'member-card empty';
                placeholder.textContent = 'ว่าง';
                slot.appendChild(placeholder);
            }
        }

        function getPanelUnderPointer() {
            const leftRect = plannerLeft.getBoundingClientRect();
            const rightRect = plannerRight.getBoundingClientRect();
            if (currentPointerX >= leftRect.left && currentPointerX <= leftRect.right &&
                currentPointerY >= leftRect.top && currentPointerY <= leftRect.bottom) {
                return plannerLeft;
            }
            if (currentPointerX >= rightRect.left && currentPointerX <= rightRect.right &&
                currentPointerY >= rightRect.top && currentPointerY <= rightRect.bottom) {
                return plannerRight;
            }
            return null;
        }

        function startAutoScroll() {
            if (scrollFrame) {
                return;
            }
            const step = () => {
                if (!isDragging) {
                    scrollFrame = null;
                    return;
                }
                const panel = getPanelUnderPointer();
                if (panel) {
                    const rect = panel.getBoundingClientRect();
                    const distanceToTop = currentPointerY - rect.top;
                    const distanceToBottom = rect.bottom - currentPointerY;
                    if (distanceToTop < scrollThreshold) {
                        panel.scrollBy(0, -scrollSpeed);
                    } else if (distanceToBottom < scrollThreshold) {
                        panel.scrollBy(0, scrollSpeed);
                    }
                }
                scrollFrame = requestAnimationFrame(step);
            };
            scrollFrame = requestAnimationFrame(step);
        }

        document.addEventListener('pointermove', (evt) => {
            currentPointerX = evt.clientX;
            currentPointerY = evt.clientY;
        });

        document.addEventListener('wheel', (evt) => {
            if (!isDragging) return;
            const panel = getPanelUnderPointer();
            if (panel) {
                evt.preventDefault();
                panel.scrollBy(0, evt.deltaY);
            }
        }, { passive: false });

        document.querySelectorAll('.slot').forEach((slot) => {
                new Sortable(slot, {
                    group: 'party-slots',
                    animation: 150,
                    sort: false,
                    draggable: '.member-card:not(.empty):not(.ineligible)',
                filter: '.empty',
                onStart: (evt) => {
                    isDragging = true;
                    startAutoScroll();
                    const originSlot = evt.from.closest('.slot');
                    dragSourceSlotId = originSlot ? originSlot.dataset.slotId : null;
                },
                onAdd: (evt) => {
                    if (evt.to.id === 'unassigned-pool') {
                        const sourceSlot = dragSourceSlotId ? document.querySelector(`.slot[data-slot-id="${dragSourceSlotId}"]`) : null;
                        if (sourceSlot) {
                            ensureEmptyPlaceholder(sourceSlot);
                            saveUpdates([syncSlot(sourceSlot)]);
                        }
                        return;
                    }

                    const target = evt.to;
                    const sourceSlot = dragSourceSlotId ? document.querySelector(`.slot[data-slot-id="${dragSourceSlotId}"]`) : null;

                    if (target.querySelectorAll('.member-card').length > 1 && sourceSlot) {
                        const cards = target.querySelectorAll('.member-card');
                        const displaced = cards[0];
                        target.removeChild(displaced);
                        sourceSlot.appendChild(displaced);
                    }

                    ensureEmptyPlaceholder(target);
                    if (sourceSlot) {
                        ensureEmptyPlaceholder(sourceSlot);
                    }

                    const updates = [
                        syncSlot(target),
                        sourceSlot ? syncSlot(sourceSlot) : null,
                    ].filter(Boolean);

                    saveUpdates(updates);
                },
                onEnd: (evt) => {
                    if (evt.to.classList.contains('slot')) {
                        ensureEmptyPlaceholder(evt.to);
                        if (evt.from === evt.to) {
                            const updates = [syncSlot(evt.to)];
                            saveUpdates(updates);
                        }
                    }
                    isDragging = false;
                },
            });
        });

        new Sortable(unassignedPool, {
            group: 'party-slots',
            animation: 150,
            sort: false,
            draggable: '.member-card:not(.ineligible)',
            onStart: () => {
                isDragging = true;
                startAutoScroll();
            },
            onAdd: () => {
                const sourceSlot = dragSourceSlotId ? document.querySelector(`.slot[data-slot-id="${dragSourceSlotId}"]`) : null;
                if (sourceSlot) {
                    ensureEmptyPlaceholder(sourceSlot);
                    saveUpdates([syncSlot(sourceSlot)]);
                }
            },
            onEnd: () => {
                isDragging = false;
            },
        });

        const tierFilter = document.getElementById('tier-filter');
        const jobClassFilter = document.getElementById('job-class-filter');

        function filterUnassignedMembers() {
            const query = searchInput.value.toLowerCase();
            const tierValue = tierFilter.value;
            const jobClassValue = jobClassFilter.value;
            unassignedPool.querySelectorAll('.member-card').forEach((card) => {
                const matchesSearch = card.textContent.toLowerCase().includes(query);
                const matchesTier = !tierValue || card.dataset.tier === tierValue;
                const matchesClass = !jobClassValue || card.dataset.jobClass === jobClassValue;
                card.style.display = matchesSearch && matchesTier && matchesClass ? '' : 'none';
            });
        }

        searchInput.addEventListener('input', filterUnassignedMembers);
        tierFilter.addEventListener('change', filterUnassignedMembers);
        jobClassFilter.addEventListener('change', filterUnassignedMembers);

        const autoAssignBtn = document.getElementById('auto-assign-btn');
        if (autoAssignBtn) {
            autoAssignBtn.addEventListener('click', () => {
                const ok = confirm('จัดปาร์ตี้ออโต้จะล้างการจัดปาร์ตี้เดิมทั้งหมด แล้วจัดใหม่ตาม tier (Top → Middle → Low) ต้องการดำเนินการต่อไหม?');
                if (!ok) return;
                fetch("{{ route('party-planner.auto-assign') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                }).then(() => window.location.reload());
            });
        }

        document.querySelectorAll('[data-party-form]').forEach((form) => {
            form.hidden = true;
        });
        document.querySelectorAll('[data-party-name]').forEach((display) => {
            display.hidden = false;
        });

        document.querySelectorAll('[data-edit-party-name]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const header = btn.closest('.party-card-header');
                const display = header.querySelector('[data-party-name]');
                const form = header.querySelector('[data-party-form]');
                const input = form.querySelector('input[name="name"]');
                display.hidden = true;
                form.hidden = false;
                input.focus();
                input.select();
            });
        });

        document.querySelectorAll('[data-party-form]').forEach((form) => {
            form.addEventListener('submit', (evt) => {
                evt.preventDefault();
                const header = form.closest('.party-card-header');
                const display = header.querySelector('[data-party-name]');
                const textEl = header.querySelector('.party-name-text');
                const input = form.querySelector('input[name="name"]');
                const nameValue = input.value.trim();
                if (!nameValue) return;

                fetch(form.action, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ name: nameValue }),
                })
                .then((res) => {
                    if (!res.ok) throw new Error('save_failed');
                    return res.json();
                })
                .then(() => {
                    textEl.textContent = nameValue;
                    form.hidden = true;
                    display.hidden = false;
                })
                .catch(() => {
                    alert('บันทึกชื่อปาร์ตี้ไม่สำเร็จ');
                });
            });
        });
    </script>

    <style>
        .planner-header {
            margin-bottom: 24px;
        }
        .planner-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        .planner-title {
            flex: 1;
        }
        .add-party-form {
            display: flex;
            gap: 8px;
        }
        .add-party-form input {
            width: 220px;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }
        .planner-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .planner-split {
            display: flex;
            gap: 20px;
            height: calc(100vh - 160px);
        }
        .planner-left {
            flex: 1;
            overflow-y: auto;
            padding-right: 12px;
        }
        .planner-right {
            width: 300px;
            flex-shrink: 0;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .party-grid {
            display: grid;
            gap: 20px;
        }
        .party-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .party-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .party-name-display {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .party-name-text {
            font-size: 1.1rem;
            font-weight: 700;
        }
        .party-name-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .party-name-form[hidden],
        .party-name-display[hidden] {
            display: none;
        }
        .party-name-form input {
            width: 200px;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }
        .btn-icon {
            padding: 4px 8px;
            line-height: 1;
        }
        .btn-sm {
            font-size: 0.8rem;
            padding: 6px 10px;
            border-radius: 8px;
        }
        .party-card h2 {
            margin: 0 0 12px;
        }
        .party-card .pt-meta {
            margin: 0 0 12px;
            color: #64748b;
            font-size: 0.85rem;
        }
        .slots {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
        }
        .slot {
            min-height: 54px;
            border: 2px dashed #cbd5f5;
            border-radius: 12px;
            padding: 4px;
            background: #f8fafc;
        }
        .member-card {
            background: #0f766e;
            color: #fff;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            cursor: grab;
            font-weight: 600;
        }
        .member-card.empty {
            background: #e2e8f0;
            color: #64748b;
            cursor: default;
        }
        .member-card.ineligible {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        .planner-right h3 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 18px;
        }
        #unassigned-search {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 0.9rem;
            box-sizing: border-box;
        }
        #unassigned-pool {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        @media (max-width: 960px) {
            .planner-split {
                flex-direction: column;
                height: auto;
            }
            .planner-left {
                padding-right: 0;
            }
            .planner-right {
                width: auto;
            }
            .slots {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .slots {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
@endsection
