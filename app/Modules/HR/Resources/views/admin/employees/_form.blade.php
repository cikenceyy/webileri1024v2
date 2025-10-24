@php($active = filter_var(old('is_active', $employee->is_active ?? true), FILTER_VALIDATE_BOOLEAN))
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Kod</label>
        <input type="text" name="code" value="{{ old('code', $employee->code ?? '') }}" class="form-control" maxlength="32" required>
        @error('code')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-5">
        <label class="form-label">Ad Soyad</label>
        <input type="text" name="name" value="{{ old('name', $employee->name ?? '') }}" class="form-control" required>
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
    <div class="col-md-2">
        <label class="form-label">Kullanıcı</label>
        <select name="user_id" class="form-select">
            <option value="">Eşleştirme yok</option>
            @foreach ($users as $id => $label)
                <option value="{{ $id }}" @selected((string) old('user_id', $employee->user_id ?? '') === (string) $id)>{{ $label }}</option>
            @endforeach
        </select>
        @error('user_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">E-posta</label>
        <input type="email" name="email" value="{{ old('email', $employee->email ?? '') }}" class="form-control">
        @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Telefon</label>
        <input type="text" name="phone" value="{{ old('phone', $employee->phone ?? '') }}" class="form-control">
        @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Departman</label>
        <select name="department_id" class="form-select">
            <option value="">Seçiniz</option>
            @foreach ($departments as $id => $label)
                <option value="{{ $id }}" @selected((string) old('department_id', $employee->department_id ?? '') === (string) $id)>{{ $label }}</option>
            @endforeach
        </select>
        @error('department_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Ünvan</label>
        <select name="title_id" class="form-select">
            <option value="">Seçiniz</option>
            @foreach ($titles as $id => $label)
                <option value="{{ $id }}" @selected((string) old('title_id', $employee->title_id ?? '') === (string) $id)>{{ $label }}</option>
            @endforeach
        </select>
        @error('title_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Çalışma Tipi</label>
        <select name="employment_type_id" class="form-select">
            <option value="">Seçiniz</option>
            @foreach ($employmentTypes as $id => $label)
                <option value="{{ $id }}" @selected((string) old('employment_type_id', $employee->employment_type_id ?? '') === (string) $id)>{{ $label }}</option>
            @endforeach
        </select>
        @error('employment_type_id')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">İşe Giriş Tarihi</label>
        <input type="date" name="hire_date" value="{{ old('hire_date', optional($employee?->hire_date)->toDateString()) }}" class="form-control">
        @error('hire_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">İşten Ayrılış Tarihi</label>
        <input type="date" name="termination_date" value="{{ old('termination_date', optional($employee?->termination_date)->toDateString()) }}" class="form-control">
        @error('termination_date')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">Notlar</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $employee->notes ?? '') }}</textarea>
        @error('notes')<span class="text-danger small">{{ $message }}</span>@enderror
    </div>
</div>
