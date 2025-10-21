@extends('layouts.admin')

@section('title', 'Ambarlar')

@section('content')
<x-ui.page-header title="Ambarlar" description="Depolarınızı yönetin">
    <x-slot name="actions">
        @can('create', \App\Modules\Inventory\Domain\Models\Warehouse::class)
            <x-ui.button variant="primary" href="{{ route('admin.inventory.warehouses.create') }}">Yeni Ambar</x-ui.button>
        @endcan
    </x-slot>
</x-ui.page-header>

@if(session('status'))
    <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
@endif

<x-ui.card class="mb-4" data-inventory-filters>
    <form method="GET" action="{{ route('admin.inventory.warehouses.index') }}" class="row g-3">
        <div class="col-md-8">
            <x-ui.input name="q" label="Ara" :value="$filters['q'] ?? ''" placeholder="Kod veya ad" />
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
            <x-ui.button type="submit" class="flex-grow-1">Filtrele</x-ui.button>
            <a href="{{ route('admin.inventory.warehouses.index') }}" class="btn btn-outline-secondary">Sıfırla</a>
        </div>
    </form>
</x-ui.card>

@if($warehouses->count())
    <x-ui.card>
        <x-ui.table dense>
            <thead>
                <tr>
                    <th>Kod</th>
                    <th>Ad</th>
                    <th>Durum</th>
                    <th>Varsayılan</th>
                    <th class="text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($warehouses as $warehouse)
                    <tr>
                        <td class="fw-semibold">{{ $warehouse->code }}</td>
                        <td>{{ $warehouse->name }}</td>
                        <td>
                            <x-ui.badge :type="$warehouse->status === 'active' ? 'success' : 'secondary'" soft>{{ $warehouse->status === 'active' ? 'Aktif' : 'Pasif' }}</x-ui.badge>
                        </td>
                        <td>
                            @if($warehouse->is_default)
                                <x-ui.badge type="primary" soft>Varsayılan</x-ui.badge>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                @can('update', $warehouse)
                                    <a href="{{ route('admin.inventory.warehouses.edit', $warehouse) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                                @endcan
                                @can('delete', $warehouse)
                                    <form method="POST" action="{{ route('admin.inventory.warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Ambarı silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" size="sm">Sil</x-ui.button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    </x-ui.card>

    <div class="mt-4">
        {{ $warehouses->links() }}
    </div>
@else
    <x-ui.empty title="Ambar bulunamadı" description="Yeni ambar ekleyin." />
@endif
@endsection
