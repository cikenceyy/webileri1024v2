@extends('layouts.admin')

@section('title', $product->name.' - Varyantlar')

@section('content')
<x-ui.page-header :title="$product->name" description="Varyantlar">
    <x-slot name="actions">
        <a href="{{ route('admin.inventory.products.show', $product) }}" class="btn btn-outline-secondary">Ürün Detayı</a>
        @can('create', \App\Modules\Inventory\Domain\Models\ProductVariant::class)
            <x-ui.button variant="primary" href="{{ route('admin.inventory.products.variants.create', $product) }}">Yeni Varyant</x-ui.button>
        @endcan
    </x-slot>
</x-ui.page-header>

@if(session('status'))
    <x-ui.alert type="success" dismissible>{{ session('status') }}</x-ui.alert>
@endif

@if($variants->count())
    <x-ui.card>
        <x-ui.table dense>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Barkod</th>
                    <th>Opsiyonlar</th>
                    <th>Durum</th>
                    <th class="text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($variants as $variant)
                    <tr>
                        <td class="fw-semibold">{{ $variant->sku }}</td>
                        <td>{{ $variant->barcode ?? '—' }}</td>
                        <td>
                            @if(empty($variant->options))
                                <span class="text-muted">—</span>
                            @else
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($variant->options as $key => $value)
                                        <x-ui.badge type="secondary" soft>{{ $key }}: {{ $value }}</x-ui.badge>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <x-ui.badge :type="$variant->status === 'active' ? 'success' : 'secondary'" soft>{{ $variant->status === 'active' ? 'Aktif' : 'Pasif' }}</x-ui.badge>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2">
                                @can('update', $variant)
                                    <a href="{{ route('admin.inventory.products.variants.edit', [$product, $variant]) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                                @endcan
                                @can('delete', $variant)
                                    <form method="POST" action="{{ route('admin.inventory.products.variants.destroy', [$product, $variant]) }}" onsubmit="return confirm('Varyantı silmek istediğinize emin misiniz?');">
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
        {{ $variants->links() }}
    </div>
@else
    <x-ui.empty title="Varyant yok" description="Bu ürün için varyant ekleyin.">
        @can('create', \App\Modules\Inventory\Domain\Models\ProductVariant::class)
            <x-slot name="actions">
                <x-ui.button variant="primary" href="{{ route('admin.inventory.products.variants.create', $product) }}">Varyant Oluştur</x-ui.button>
            </x-slot>
        @endcan
    </x-ui.empty>
@endif
@endsection
