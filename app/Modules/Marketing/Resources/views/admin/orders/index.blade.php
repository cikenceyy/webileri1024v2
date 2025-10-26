@extends('layouts.admin')

@section('title', 'Satış Siparişleri')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Satış Siparişleri</h1>
        <a href="{{ route('admin.marketing.orders.create') }}" class="btn btn-primary">Yeni Sipariş</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Sipariş no veya müşteri">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Durum (Tümü)</option>
                @foreach (['draft' => 'Taslak', 'confirmed' => 'Onaylı', 'fulfilled' => 'Tamamlandı', 'cancelled' => 'İptal'] as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Filtrele</button>
        </div>
    </form>

    <div class="card">
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" :search-placeholder="__('Sipariş ara…')" />
            </x-slot>
        </x-table>
    </div>
@endsection
