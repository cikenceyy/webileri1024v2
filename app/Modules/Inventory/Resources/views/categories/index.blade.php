@extends('layouts.admin')

@section('title', 'Ürün Kategorileri')
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header d-flex justify-content-between align-items-center">
            <h1 class="inv-card__title">Ürün Kategorileri</h1>
        </header>
        <div class="row g-4">
            <div class="col-md-5">
                <h2 class="h6">Yeni Kategori</h2>
                <form method="post" action="{{ route('admin.inventory.categories.store') }}" class="card card-body">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Üst Kategori</label>
                        <select name="parent_id" class="form-select">
                            <option value="">— Yok —</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('parent_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kod</label>
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control" required>
                        @error('code')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ad</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        @error('name')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug') }}" class="form-control">
                        @error('slug')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="active" @selected(old('status', 'active') === 'active')>Aktif</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Pasif</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </form>
            </div>
            <div class="col-md-7">
                <h2 class="h6">Kategori Ağacı</h2>
                <ul class="list-group">
                    @forelse ($categories as $category)
                        <li class="list-group-item">
                            <form method="post" action="{{ route('admin.inventory.categories.update', $category) }}" class="row g-2 align-items-center">
                                @csrf
                                @method('put')
                                <div class="col-md-3">
                                    <input type="text" name="code" value="{{ old('code', $category->code) }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="active" @selected($category->status === 'active')>Aktif</option>
                                        <option value="inactive" @selected($category->status === 'inactive')>Pasif</option>
                                    </select>
                                </div>
                                <div class="col-md-1 text-center">
                                    <input type="checkbox" name="is_active" value="1" @checked($category->is_active)>
                                </div>
                                <input type="hidden" name="parent_id" value="{{ $category->parent_id }}">
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">Güncelle</button>
                                </div>
                            </form>
                            <form method="post" action="{{ route('admin.inventory.categories.destroy', $category) }}" onsubmit="return confirm('Kategori silinsin mi?')">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-outline-danger btn-sm mt-2">Sil</button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Henüz kategori yok.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </section>
@endsection
