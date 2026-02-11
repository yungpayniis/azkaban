@csrf

<div class="field">
    <label for="guild_member_id">สมาชิก</label>
    <select id="guild_member_id" name="guild_member_id" required>
        <option value="">- เลือกสมาชิก -</option>
        @foreach ($members as $member)
            <option value="{{ $member->id }}" @selected(old('guild_member_id', $selectedMemberId ?? ($gvgWeeklyStat->guild_member_id ?? '')) == $member->id)>
                {{ $member->name }}
            </option>
        @endforeach
    </select>
    @error('guild_member_id')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="week_start_date">สัปดาห์เริ่ม</label>
    <input id="week_start_date" name="week_start_date" type="date" value="{{ old('week_start_date', isset($gvgWeeklyStat) ? $gvgWeeklyStat->week_start_date->format('Y-m-d') : '') }}" required>
    @error('week_start_date')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="kills">จำนวนฆ่า</label>
    <input id="kills" name="kills" type="number" min="0" value="{{ old('kills', $gvgWeeklyStat->kills ?? 0) }}" required>
    @error('kills')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="deaths">จำนวนตาย</label>
    <input id="deaths" name="deaths" type="number" min="0" value="{{ old('deaths', $gvgWeeklyStat->deaths ?? 0) }}" required>
    @error('deaths')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="revives">จำนวนชุบ</label>
    <input id="revives" name="revives" type="number" min="0" value="{{ old('revives', $gvgWeeklyStat->revives ?? 0) }}" required>
    @error('revives')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="war_score">แต้มวอร์</label>
    <input id="war_score" name="war_score" type="number" min="0" value="{{ old('war_score', $gvgWeeklyStat->war_score ?? 0) }}" required>
    @error('war_score')
        <div class="error">{{ $message }}</div>
    @enderror
</div>
