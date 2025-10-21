@extends('layouts.admin')

@section('title', 'Ürünler')

@section('content')
<x-ui.page-header title="Ürünler" description="Stok kartlarınızı yönetin">
    <x-slot name="actions">
        @can('create', \App\Modules\Inventory\Domain\Models\Product::class)
            <x-ui.button variant="primary" href="{{ route('admin.inventory.products.create') }}">
                Yeni Ürün
            </x-ui.button>
        @endcan
    </x-slot>
</x-ui.page-header>

@if(session('status'))
    <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
@endif

@php
    $filters = $filters ?? [];
    $currentSort = $sort ?? 'created_at';
    $currentDir = $direction ?? 'desc';
    $queryBase = array_filter([
        'q' => $filters['q'] ?? null,
        'status' => $filters['status'] ?? null,
        'category_id' => $filters['category_id'] ?? null,
    ]);
    $sortUrl = function (string $column) use ($queryBase, $currentSort, $currentDir) {
        $direction = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';

        return route('admin.inventory.products.index', array_merge($queryBase, [
            'sort' => $column,
            'dir' => $direction,
        ]));
    };
@endphp

<x-ui.card class="mb-4" data-inventory-filters>
    <form method="GET" action="{{ route('admin.inventory.products.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-4 col-md-6">
            <x-ui.input
                name="q"
                label="Ara"
                :value="$filters['q'] ?? ''"
                placeholder="SKU veya ürün adı"
            />
        </div>
        <div class="col-lg-3 col-md-6">
            <x-ui.select name="category_id" label="Kategori">
                <option value="">Tümü</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="col-lg-2 col-md-6">
            <x-ui.select name="status" label="Durum">
                <option value="">Tümü</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Pasif</option>
            </x-ui.select>
        </div>
        <div class="col-lg-3 col-md-6 d-flex gap-2">
            <x-ui.button type="submit" class="flex-grow-1">Filtrele</x-ui.button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.inventory.products.index') }}">Sıfırla</a>
        </div>
    </form>
</x-ui.card>

@if($products->count())
    <x-ui.card>
        <x-ui.table dense>
            <thead>
                <tr>
                    <th scope="col">Görsel</th>
                    <th scope="col"><a href="{{ $sortUrl('sku') }}" class="table-sort {{ $currentSort === 'sku' ? 'active' : '' }}">SKU @if($currentSort === 'sku')<span aria-hidden="true">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                    <th scope="col"><a href="{{ $sortUrl('name') }}" class="table-sort {{ $currentSort === 'name' ? 'active' : '' }}">Ad @if($currentSort === 'name')<span aria-hidden="true">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                    <th scope="col">Kategori</th>
                    <th scope="col"><a href="{{ $sortUrl('created_at') }}" class="table-sort {{ $currentSort === 'created_at' ? 'active' : '' }}">Oluşturma @if($currentSort === 'created_at')<span aria-hidden="true">{{ $currentDir === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                    <th scope="col">Fiyat</th>
                    <th scope="col">Birim</th>
                    <th scope="col">Durum</th>
                    <th scope="col" class="text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td class="align-middle">
                            @if($product->media)
                                <x-ui.file-icon :ext="$product->media->ext" size="28" class="me-2" />
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <span class="fw-semibold">{{ $product->sku }}</span>
                        </td>
                        <td class="align-middle">
                            <div class="fw-semibold text-ellipsis" title="{{ $product->name }}">{{ $product->name }}</div>
                            @if($product->media)
                                <div class="text-muted small text-ellipsis" title="{{ $product->media->original_name }}">{{ $product->media->original_name }}</div>
                            @endif
                        </td>
                        <td class="align-middle text-muted">{{ $product->category?->name ?? '—' }}</td>
                        <td class="align-middle">{{ $product->created_at?->format('d.m.Y') }}</td>
                        <td class="align-middle">{{ number_format((float) $product->price, 2, ',', '.') }}</td>
                        <td class="align-middle">{{ $product->unit }}</td>
                        <td class="align-middle">
                            <x-ui.badge :type="$product->status === 'active' ? 'success' : 'secondary'" soft>
                                {{ $product->status === 'active' ? 'Aktif' : 'Pasif' }}
                            </x-ui.badge>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.inventory.products.show', $product) }}">Görüntüle</a>
                                @can('update', $product)
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.inventory.products.edit', $product) }}">Düzenle</a>
                                @endcan
                                @can('delete', $product)
                                    <form method="POST" action="{{ route('admin.inventory.products.destroy', $product) }}" onsubmit="return confirm('Ürünü silmek istediğinize emin misiniz?');">
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
        {{ $products->links() }}
    </div>
@else
    <x-ui.empty title="Ürün bulunamadı" description="Yeni ürün ekleyerek envanterinizi oluşturun.">
        @can('create', \App\Modules\Inventory\Domain\Models\Product::class)
            <x-slot name="actions">
                <x-ui.button variant="primary" href="{{ route('admin.inventory.products.create') }}">İlk Ürünü Oluştur</x-ui.button>
            </x-slot>
        @endcan
    </x-ui.empty>
@endif
@endsection
