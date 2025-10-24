@php($active = filter_var(old('is_active', $employmentType->is_active ?? true), FILTER_VALIDATE_BOOLEAN))
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Kod</label>
        <input type="text" name="code" value="{{ old('code', $employmentType->code ?? '') }}" class="form-control" maxlength="32" required>
        @error('code')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Ad</label>
        <input type="text" name="name" value="{{ old('name', $employmentType->name ?? '') }}" class="form-control" required>
        @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label">Durum</label>
        <select name="is_active" class="form-select">
            <option value="1" @selected($active)>Aktif</option>
            <option value="0" @selected(! $active)>Pasif</option>
        </select>
        @error('is_active')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
</div>
