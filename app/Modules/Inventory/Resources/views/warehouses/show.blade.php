@extends('layouts.admin')

@section('title', $warehouse->name)

@section('content')
<x-ui-page-header :title="$warehouse->name" description="Ambar detayları">
    <x-slot name="actions">
        <a href="{{ route('admin.inventory.warehouses.index') }}" class="btn btn-outline-secondary">Listeye Dön</a>
        @can('update', $warehouse)
            <x-ui-button variant="primary" href="{{ route('admin.inventory.warehouses.edit', $warehouse) }}">Düzenle</x-ui-button>
        @endcan
    </x-slot>
</x-ui-page-header>

<x-ui-card>
    <dl class="row mb-0">
        <dt class="col-sm-4 text-muted">Kod</dt>
        <dd class="col-sm-8 fw-semibold">{{ $warehouse->code }}</dd>
        <dt class="col-sm-4 text-muted">Durum</dt>
        <dd class="col-sm-8">
            <x-ui-badge :type="$warehouse->status === 'active' ? 'success' : 'secondary'" soft>{{ $warehouse->status === 'active' ? 'Aktif' : 'Pasif' }}</x-ui-badge>
        </dd>
        <dt class="col-sm-4 text-muted">Varsayılan</dt>
        <dd class="col-sm-8">{{ $warehouse->is_default ? 'Evet' : 'Hayır' }}</dd>
        <dt class="col-sm-4 text-muted">Oluşturulma</dt>
        <dd class="col-sm-8">{{ $warehouse->created_at?->format('d.m.Y H:i') }}</dd>
    </dl>
    <p class="text-muted mb-0 mt-3">Stok detayı bu sürümde placeholder olarak sunulmaktadır.</p>
</x-ui-card>
@endsection
