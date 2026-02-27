@csrf

<div class="field">
    <label for="name">ชื่อสมาชิก</label>
    <input id="name" name="name" type="text" value="{{ old('name', $guildMember->name ?? '') }}" required>
    @error('name')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="job_class_id">อาชีพ</label>
    <div class="select2-ish" data-select-id="job_class_id">
        <div class="select2-ish-control" tabindex="0" role="button" aria-haspopup="listbox" aria-expanded="false">
            <span class="select2-ish-value" data-placeholder="- ไม่ระบุ -">- ไม่ระบุ -</span>
            <span class="select2-ish-arrow">▾</span>
        </div>
        <div class="select2-ish-panel" role="listbox" aria-hidden="true">
            <input class="select2-ish-search" type="text" placeholder="ค้นหาอาชีพ..." autocomplete="off">
            <div class="select2-ish-list">
                <div class="select2-ish-option" data-value="">- ไม่ระบุ -</div>
                @foreach ($jobClasses as $jobClass)
                    <div class="select2-ish-option" data-value="{{ $jobClass->id }}">
                        Tier {{ $jobClass->tier }} - {{ $jobClass->name }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <select id="job_class_id" name="job_class_id" class="select2-ish-native">
        <option value="">- ไม่ระบุ -</option>
        @foreach ($jobClasses as $jobClass)
            <option value="{{ $jobClass->id }}" @selected(old('job_class_id', $guildMember->job_class_id ?? '') == $jobClass->id)>
                Tier {{ $jobClass->tier }} - {{ $jobClass->name }}
            </option>
        @endforeach
    </select>
    @error('job_class_id')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="tier">ระดับ</label>
    <select id="tier" name="tier" required>
        @foreach (['low' => 'Low', 'middle' => 'Middle', 'top' => 'Top'] as $value => $label)
            <option value="{{ $value }}" @selected(old('tier', $guildMember->tier ?? 'low') == $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('tier')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="role">บทบาท</label>
    <select id="role" name="role" required>
        @foreach (['dps' => 'DPS', 'support' => 'Support', 'tank' => 'Tank'] as $value => $label)
            <option value="{{ $value }}" @selected(old('role', $guildMember->role ?? 'dps') == $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('role')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="nationality">สัญชาติ</label>
    <select id="nationality" name="nationality" required>
        @foreach (['thai' => 'คนไทย', 'foreign' => 'ต่างชาติ'] as $value => $label)
            <option value="{{ $value }}" @selected(old('nationality', $guildMember->nationality ?? 'thai') == $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('nationality')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="status">สถานะ</label>
    <select id="status" name="status" required>
        @foreach (['active' => 'Active', 'left' => 'Left'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $guildMember->status ?? 'active') == $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('status')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="joined_at">วันที่เข้ากิล</label>
    <input id="joined_at" name="joined_at" type="datetime-local"
        value="{{ old('joined_at', isset($guildMember) && $guildMember->joined_at ? $guildMember->joined_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" required>
    <small class="muted">สมาชิกต้องรอ 7 วันนับจากวันเข้ากิลก่อนจะลง GVG ได้</small>
    @error('joined_at')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

@php $stat = $guildMember->stat ?? null; @endphp
<div class="field">
    <label>ค่าสเตตัสส่วนตัว</label>
    <div class="stat-grid">
        <label for="stat_str">STR</label>
        <input id="stat_str" name="stats[str]" type="number" min="0"
            value="{{ old('stats.str', $stat->str ?? 0) }}" required>

        <label for="stat_vit">VIT</label>
        <input id="stat_vit" name="stats[vit]" type="number" min="0"
            value="{{ old('stats.vit', $stat->vit ?? 0) }}" required>

        <label for="stat_dex">DEX</label>
        <input id="stat_dex" name="stats[dex]" type="number" min="0"
            value="{{ old('stats.dex', $stat->dex ?? 0) }}" required>

        <label for="stat_agi">AGI</label>
        <input id="stat_agi" name="stats[agi]" type="number" min="0"
            value="{{ old('stats.agi', $stat->agi ?? 0) }}" required>

        <label for="stat_int">INT</label>
        <input id="stat_int" name="stats[int]" type="number" min="0"
            value="{{ old('stats.int', $stat->int ?? 0) }}" required>

        <label for="stat_luk">LUK</label>
        <input id="stat_luk" name="stats[luk]" type="number" min="0"
            value="{{ old('stats.luk', $stat->luk ?? 0) }}" required>

        <label for="stat_max_hp">MAX HP</label>
        <input id="stat_max_hp" name="stats[max_hp]" type="number" min="0"
            value="{{ old('stats.max_hp', old('stats.hp', $stat->hp ?? 0)) }}" required>

        <label for="stat_max_sp">MAX SP</label>
        <input id="stat_max_sp" name="stats[max_sp]" type="number" min="0"
            value="{{ old('stats.max_sp', old('stats.sp', $stat->sp ?? 0)) }}" required>

        <label for="stat_patk">P.ATK</label>
        <input id="stat_patk" name="stats[patk]" type="number" min="0"
            value="{{ old('stats.patk', $stat->patk ?? 0) }}" required>

        <label for="stat_matk">M.ATK</label>
        <input id="stat_matk" name="stats[matk]" type="number" min="0"
            value="{{ old('stats.matk', $stat->matk ?? 0) }}" required>

        <label for="stat_pdef">P.DEF</label>
        <input id="stat_pdef" name="stats[pdef]" type="number" min="0"
            value="{{ old('stats.pdef', $stat->pdef ?? 0) }}" required>

        <label for="stat_mdef">M.DEF</label>
        <input id="stat_mdef" name="stats[mdef]" type="number" min="0"
            value="{{ old('stats.mdef', $stat->mdef ?? 0) }}" required>
    </div>
    @error('stats')
        <div class="error">{{ $message }}</div>
    @enderror
    @error('stats.str') <div class="error">{{ $message }}</div> @enderror
    @error('stats.vit') <div class="error">{{ $message }}</div> @enderror
    @error('stats.luk') <div class="error">{{ $message }}</div> @enderror
    @error('stats.agi') <div class="error">{{ $message }}</div> @enderror
    @error('stats.dex') <div class="error">{{ $message }}</div> @enderror
    @error('stats.int') <div class="error">{{ $message }}</div> @enderror
    @error('stats.max_hp') <div class="error">{{ $message }}</div> @enderror
    @error('stats.max_sp') <div class="error">{{ $message }}</div> @enderror
    @error('stats.hp') <div class="error">{{ $message }}</div> @enderror
    @error('stats.sp') <div class="error">{{ $message }}</div> @enderror
    @error('stats.patk') <div class="error">{{ $message }}</div> @enderror
    @error('stats.matk') <div class="error">{{ $message }}</div> @enderror
    @error('stats.pdef') <div class="error">{{ $message }}</div> @enderror
    @error('stats.mdef') <div class="error">{{ $message }}</div> @enderror
</div>

<script>
    (function () {
        const wrapper = document.querySelector('.select2-ish[data-select-id="job_class_id"]');
        const select = document.getElementById('job_class_id');
        if (!wrapper || !select) return;

        const control = wrapper.querySelector('.select2-ish-control');
        const panel = wrapper.querySelector('.select2-ish-panel');
        const searchInput = wrapper.querySelector('.select2-ish-search');
        const valueEl = wrapper.querySelector('.select2-ish-value');
        const optionEls = Array.from(wrapper.querySelectorAll('.select2-ish-option'));

        function setValue(value, label) {
            select.value = value;
            valueEl.textContent = label || valueEl.dataset.placeholder;
        }

        function syncFromSelect() {
            const selected = select.options[select.selectedIndex];
            if (selected) {
                setValue(selected.value, selected.textContent);
            }
        }

        function openPanel() {
            panel.style.display = 'block';
            panel.setAttribute('aria-hidden', 'false');
            control.setAttribute('aria-expanded', 'true');
            searchInput.value = '';
            filterOptions();
            searchInput.focus();
        }

        function closePanel() {
            panel.style.display = 'none';
            panel.setAttribute('aria-hidden', 'true');
            control.setAttribute('aria-expanded', 'false');
        }

        function filterOptions() {
            const query = searchInput.value.trim().toLowerCase();
            optionEls.forEach((opt) => {
                const text = opt.textContent.toLowerCase();
                opt.style.display = query === '' || text.includes(query) ? '' : 'none';
            });
        }

        control.addEventListener('click', () => {
            if (panel.style.display === 'block') {
                closePanel();
                return;
            }
            openPanel();
        });

        control.addEventListener('keydown', (evt) => {
            if (evt.key === 'Enter' || evt.key === ' ') {
                evt.preventDefault();
                openPanel();
            }
        });

        optionEls.forEach((opt) => {
            opt.addEventListener('click', () => {
                setValue(opt.dataset.value || '', opt.textContent.trim());
                closePanel();
            });
        });

        searchInput.addEventListener('input', filterOptions);

        document.addEventListener('click', (evt) => {
            if (!wrapper.contains(evt.target)) {
                closePanel();
            }
        });

        document.addEventListener('keydown', (evt) => {
            if (evt.key === 'Escape') {
                closePanel();
            }
        });

        syncFromSelect();
    })();
</script>

<style>
    .select2-ish-native {
        display: none;
    }
    .select2-ish {
        position: relative;
        width: 100%;
    }
    .select2-ish-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 10px;
        background: #fff;
        cursor: pointer;
    }
    .select2-ish-value {
        color: #111827;
    }
    .select2-ish-arrow {
        color: #6b7280;
        margin-left: 8px;
        font-size: 0.9rem;
    }
    .select2-ish-panel {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        z-index: 20;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
        padding: 8px;
        display: none;
    }
    .select2-ish-search {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        margin-bottom: 8px;
        box-sizing: border-box;
    }
    .select2-ish-list {
        max-height: 220px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .select2-ish-option {
        padding: 8px 10px;
        border-radius: 6px;
        cursor: pointer;
    }
    .select2-ish-option:hover {
        background: #f1f5f9;
    }
    .stat-grid {
        display: grid;
        grid-template-columns: 90px minmax(0, 1fr);
        gap: 8px 12px;
        align-items: center;
        margin-top: 8px;
    }
    .stat-grid input {
        padding: 8px 10px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
    }
</style>
