@extends('layouts.app')

@section('title', 'KVM Party Planner')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php use App\Services\MemberColorService; @endphp
    <div class="planner-header">
        <p class="muted">Drag members into a pt slot (5 ppl each). Use the button to create a new pt.</p>
        <form class="add-party-form" method="POST" action="{{ route('kvm-planner.parties.store') }}">
            @csrf
            <input type="text" name="name" placeholder="pt name (optional)">
            <button type="submit">Add pt</button>
        </form>
    </div>

    <div class="planner-split">
        <div class="planner-left">
            <div class="party-grid">
                @foreach ($parties as $index => $party)
                    <div class="party-card">
                        <div class="party-card-header">
                            <div>
                                <h2>{{ $party->name }}</h2>
                                <p class="pt-meta">pt{{ $index + 1 }}</p>
                            </div>
                            <form method="POST" action="{{ route('kvm-planner.parties.slots.store', $party) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm">+ เพิ่มสมาชิกในตี้</button>
                            </form>
                        </div>
                        <div class="slots">
                            @foreach ($party->slots as $slot)
                                <div class="slot" data-slot-id="{{ $slot->id }}">
                @if ($slot->member)
                    @php
                        $color = MemberColorService::colorFor($slot->member);
                        $textColor = MemberColorService::textColorFor($slot->member);
                    @endphp
                    <div class="member-card" data-member-id="{{ $slot->member->id }}"
                        style="{{ $color ? "background: {$color}; color: {$textColor};" : '' }}"
                        data-eligible="1">
                        {{ $slot->member->name }}
                    </div>
                                    @else
                                        <div class="member-card empty">Empty</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="planner-right">
            <h3>Members without pt</h3>
            <div class="field filters">
                <label for="tier-filter">Tier</label>
                <select id="tier-filter">
                    <option value="">All</option>
                    @foreach (['low' => 'Low', 'middle' => 'Middle', 'top' => 'Top'] as $value => $label)
                        <option value="tier-{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field filters">
                <label for="job-class-filter">Job</label>
                <select id="job-class-filter">
                    <option value="">All</option>
                    @foreach ($jobClasses as $jobClass)
                        <option value="{{ $jobClass->id }}">{{ 'Tier ' . $jobClass->tier }} - {{ $jobClass->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="text" id="unassigned-search" placeholder="Search members..." autocomplete="off">
            <div id="unassigned-pool">
                @forelse ($unassignedMembers as $member)
                @php
                    $color = MemberColorService::colorFor($member);
                    $textColor = MemberColorService::textColorFor($member);
                @endphp
                <div class="member-card" data-member-id="{{ $member->id }}"
                    data-tier="tier-{{ $member->tier ?? '' }}"
                    data-job-class="{{ $member->jobClass->id ?? '' }}"
                    style="{{ $color ? "background: {$color}; color: {$textColor};" : '' }}"
                    data-eligible="1">
                    {{ $member->name }}
                </div>
                @empty
                    <div class="muted">No free members</div>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const unassignedPool = document.getElementById('unassigned-pool');
        const searchInput = document.getElementById('unassigned-search');
        const tierFilter = document.getElementById('tier-filter');
        const jobClassFilter = document.getElementById('job-class-filter');
        let dragSourceSlotId = null;

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
            fetch("{{ route('kvm-planner.slots.update') }}", {
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
                placeholder.textContent = 'Empty';
                slot.appendChild(placeholder);
            }
        }

        const scrollThreshold = 120;
        const scrollSpeed = 24;
        let isDragging = false;
        let currentPointerX = 0;
        let currentPointerY = 0;
        let scrollFrame = null;

        const plannerLeft = document.querySelector('.planner-left');
        const plannerRight = document.querySelector('.planner-right');

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
                    group: 'kvm-party-slots',
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
            group: 'kvm-party-slots',
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
    </script>

    <style>
        .planner-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }
        .add-party-form {
            display: flex;
            gap: 8px;
        }
        .planner-split {
            display: flex;
            height: calc(100vh - 140px);
            gap: 20px;
        }
        .planner-left {
            flex: 1;
            overflow-y: auto;
            padding-right: 12px;
        }
        .planner-right {
            width: 280px;
            flex-shrink: 0;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .planner-right h3 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 18px;
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
        .party-card h2 {
            margin: 0 0 12px;
        }
        .party-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
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
        @media (max-width: 900px) {
            .planner-split {
                flex-direction: column;
                height: auto;
            }
            .planner-left {
                overflow-y: visible;
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
            .planner-header {
                flex-direction: column;
            }
        }
    </style>
@endsection
