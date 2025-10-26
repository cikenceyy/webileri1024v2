@extends('layouts.admin')

@section('title', 'İş Emirleri')
@section('module', 'Production')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">İş Emirleri</h1>
        @can('create', \App\Modules\Production\Domain\Models\WorkOrder::class)
            <a href="{{ route('admin.production.workorders.create') }}" class="btn btn-primary">Yeni İş Emri</a>
        @endcan
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-3">
            <label for="status" class="form-label">Durum</label>
            <select name="status" id="status" class="form-select">
                <option value="">Hepsi</option>
                @foreach(['draft' => 'Taslak', 'released' => 'Serbest', 'in_progress' => 'Üretimde', 'completed' => 'Tamamlandı', 'closed' => 'Kapalı', 'cancelled' => 'İptal'] as $key => $label)
                    <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 align-self-end">
            <button class="btn btn-outline-secondary" type="submit">Filtrele</button>
        </div>
    </form>

    <div class="card">
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" :search-placeholder="__('İş emri ara…')" />
            </x-slot>
        </x-table>
    </div>
@endsection
