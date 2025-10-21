@extends('layouts.admin')

@section('title', 'Fiyat Listeleri')

@section('content')
<x-ui-page-header title="Fiyat Listeleri" description="Satış ve satın alma fiyat listeleri">
    <x-slot name="actions">
        @can('create', \App\Modules\Inventory\Domain\Models\PriceList::class)
            <x-ui-button variant="primary" href="{{ route('admin.inventory.pricelists.create') }}">Yeni Liste</x-ui-button>
        @endcan
    </x-slot>
</x-ui-page-header>

@if(session('status'))
    <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
@endif

<x-ui-card class="mb-4" data-inventory-filters>
    <form method="GET" action="{{ route('admin.inventory.pricelists.index') }}" class="row g-3">
        <div class="col-md-8">
            <x-ui-input name="q" label="Ara" :value="$filters['q'] ?? ''" placeholder="Liste adı" />
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
            <x-ui-button type="submit" class="flex-grow-1">Filtrele</x-ui-button>
            <a href="{{ route('admin.inventory.pricelists.index') }}" class="btn btn-outline-secondary">Sıfırla</a>
        </div>
    </form>
</x-ui-card>

@if($priceLists->count())
    <x-ui-card>
        <x-ui-table dense>
            <thead>
                <tr>
                    <th>Ad</th>
                    <th>Tür</th>
                    <th>Para Birimi</th>
                    <th>Durum</th>
                    <th class="text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($priceLists as $list)
                    <tr>
                        <td class="fw-semibold">{{ $list->name }}</td>
                        <td>{{ $list->type === 'sale' ? 'Satış' : 'Satın Alma' }}</td>
                        <td>{{ strtoupper($list->currency) }}</td>
                        <td>
                            <x-ui-badge :type="$list->active ? 'success' : 'secondary'" soft>{{ $list->active ? 'Aktif' : 'Pasif' }}</x-ui-badge>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.inventory.pricelists.show', $list) }}" class="btn btn-sm btn-outline-secondary">Detay</a>
                                @can('update', $list)
                                    <a href="{{ route('admin.inventory.pricelists.edit', $list) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                                @endcan
                                @can('delete', $list)
                                    <form method="POST" action="{{ route('admin.inventory.pricelists.destroy', $list) }}" onsubmit="return confirm('Listeyi silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui-button type="submit" variant="danger" size="sm">Sil</x-ui-button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui-table>
    </x-ui-card>

    <div class="mt-4">
        {{ $priceLists->links() }}
    </div>
@else
    <x-ui-empty title="Liste yok" description="Yeni bir fiyat listesi oluşturun." />
@endif
@endsection
