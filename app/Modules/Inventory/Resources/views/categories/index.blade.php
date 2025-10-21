@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
<x-ui.page-header title="Kategoriler" description="Ürün kategorilerinizi yönetin">
    <x-slot name="actions">
        @can('create', \App\Modules\Inventory\Domain\Models\ProductCategory::class)
            <x-ui.button variant="primary" href="{{ route('admin.inventory.categories.create') }}">Yeni Kategori</x-ui.button>
        @endcan
    </x-slot>
</x-ui.page-header>

@if(session('status'))
    <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
@endif

<x-ui.card class="mb-4" data-inventory-filters>
    <form method="GET" action="{{ route('admin.inventory.categories.index') }}" class="row g-3">
        <div class="col-md-8">
            <x-ui.input name="q" label="Ara" :value="$filters['q'] ?? ''" placeholder="Kod veya kategori adı" />
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
            <x-ui.button type="submit" class="flex-grow-1">Filtrele</x-ui.button>
            <a href="{{ route('admin.inventory.categories.index') }}" class="btn btn-outline-secondary">Sıfırla</a>
        </div>
    </form>
</x-ui.card>

@if($categories->count())
    <x-ui.card>
        <x-ui.table dense>
            <thead>
                <tr>
                    <th>Kod</th>
                    <th>Ad</th>
                    <th>Üst Kategori</th>
                    <th>Durum</th>
                    <th class="text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        <td class="fw-semibold">{{ $category->code }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->parent?->name ?? '—' }}</td>
                        <td>
                            <x-ui.badge :type="$category->status === 'active' ? 'success' : 'secondary'" soft>
                                {{ $category->status === 'active' ? 'Aktif' : 'Pasif' }}
                            </x-ui.badge>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                @can('update', $category)
                                    <a href="{{ route('admin.inventory.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                                @endcan
                                @can('delete', $category)
                                    <form method="POST" action="{{ route('admin.inventory.categories.destroy', $category) }}" onsubmit="return confirm('Kategoriyi silmek istediğinize emin misiniz?');">
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
        {{ $categories->links() }}
    </div>
@else
    <x-ui.empty title="Kategori bulunamadı" description="Yeni kategori oluşturarak başlayın.">
        @can('create', \App\Modules\Inventory\Domain\Models\ProductCategory::class)
            <x-slot name="actions">
                <x-ui.button variant="primary" href="{{ route('admin.inventory.categories.create') }}">İlk Kategoriyi Oluştur</x-ui.button>
            </x-slot>
        @endcan
    </x-ui.empty>
@endif
@endsection
