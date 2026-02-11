@csrf

<div class="field">
    <label for="name">ชื่ออาชีพ</label>
    <input id="name" name="name" type="text" value="{{ old('name', $jobClass->name ?? '') }}" required>
    @error('name')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="tier">ระดับ (Tier)</label>
    <select id="tier" name="tier" required>
        @for ($i = 1; $i <= 4; $i++)
            <option value="{{ $i }}" @selected(old('tier', $jobClass->tier ?? 1) == $i)>Tier {{ $i }}</option>
        @endfor
    </select>
    @error('tier')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="parent_id">อาชีพขั้นก่อนหน้า</label>
    <select id="parent_id" name="parent_id">
        <option value="">- ไม่ระบุ -</option>
        @foreach ($parents as $parent)
            <option value="{{ $parent->id }}" @selected(old('parent_id', $jobClass->parent_id ?? '') == $parent->id)>
                Tier {{ $parent->tier }} - {{ $parent->name }}
            </option>
        @endforeach
    </select>
    @error('parent_id')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label for="color">สีประจำสาย</label>
    <input id="color" name="color" type="text" placeholder="#FBC02D"
        value="{{ old('color', $jobClass->color ?? '') }}">
    <small class="muted">ใส่ค่าสี Hex (ex. #C62828) เพื่อให้แถวสมาชิกได้สีตามสายอาชีพ</small>
    @error('color')
        <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="field">
    <label>
        <input type="hidden" name="force_dark_text" value="0">
        <input type="checkbox" name="force_dark_text" value="1" @checked(old('force_dark_text', $jobClass->force_dark_text ?? false))>
        ใช้ข้อความสีดำเมื่อแถวใช้สีอ่อน
    </label>
    <small class="muted">ติ๊กถ้าอยากให้ row ของสายนี้แสดงตัวหนังสือเป็นสีดำแม้พื้นหลังสีอ่อน</small>
</div>
