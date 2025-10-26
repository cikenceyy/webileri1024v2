@extends('layouts.admin')

@section('title', 'Müşteriler')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Müşteri Listesi</h1>
        <a href="{{ route('admin.marketing.customers.create') }}" class="btn btn-primary">Yeni Müşteri</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="İsim, e-posta veya telefon ile ara">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Durum (Tümü)</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Pasif</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Filtrele</button>
        </div>
    </form>

    <div class="card">
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" :search-placeholder="__('Müşteri ara…')" />
            </x-slot>
        </x-table>
    </div>
@endsection
