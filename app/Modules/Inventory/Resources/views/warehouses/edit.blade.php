@extends('layouts.admin')

@section('title', 'Depo Düzenle')
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header">
            <h1 class="inv-card__title">{{ $warehouse->name }} düzenle</h1>
        </header>
        <form method="post" action="{{ route('admin.inventory.warehouses.update', $warehouse) }}" class="inv-form">
            @csrf
            @method('put')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Kod</label>
                    <input type="text" name="code" value="{{ old('code', $warehouse->code) }}" class="form-control" required>
                    @error('code')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label">Ad</label>
                    <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" class="form-control" required>
                    @error('name')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="active" @selected(old('status', $warehouse->status) === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status', $warehouse->status) === 'inactive')>Pasif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Varsayılan Depo</label>
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $warehouse->is_default))>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Aktif</label>
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $warehouse->is_active))>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Güncelle</button>
                <a href="{{ route('admin.inventory.warehouses.show', $warehouse) }}" class="btn btn-secondary">İptal</a>
            </div>
        </form>
    </section>
@endsection
