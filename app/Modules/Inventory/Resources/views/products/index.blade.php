@extends('layouts.admin')

@section('title', 'Ürünler')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/products_index.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/products_index.js')
@endpush

@section('content')
    @php
        use Illuminate\Support\Str;
    @endphp
    <div class="inv-products-list" data-view="{{ $filters['view'] }}">
        <header class="inv-products-list__filters">
            <form method="get" class="inv-products-list__filter-form">
                <div class="inv-chip-group" role="group" aria-label="Depo filtresi">
                    <span class="inv-chip inv-chip--label">Depo</span>
                    @foreach ($warehouses as $warehouse)
                        <button type="submit"
                                name="warehouse"
                                value="{{ $warehouse->id }}"
                                class="inv-chip {{ $filters['warehouse'] === $warehouse->id ? 'is-active' : '' }}"
                                data-chip-action="toggle-filter">
                            {{ $warehouse->name }}
                        </button>
                    @endforeach
                </div>

                <div class="inv-chip-group" role="group" aria-label="Kategori filtresi">
                    <span class="inv-chip inv-chip--label">Kategori</span>
                    @foreach ($categories as $category)
                        <button type="submit"
                                name="category"
                                value="{{ $category->id }}"
                                class="inv-chip {{ $filters['category'] === $category->id ? 'is-active' : '' }}"
                                data-chip-action="toggle-filter">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <div class="inv-products-list__search">
                    <input type="search" class="form-control" name="q" placeholder="Ürün ara" value="{{ $filters['q'] }}">
                    <button class="btn btn-outline-secondary" type="submit">Filtrele</button>
                </div>
            </form>

            <div class="inv-products-list__view-toggle">
                <button type="button" class="btn btn-sm {{ $filters['view'] === 'grid' ? 'btn-primary' : 'btn-outline-secondary' }}" data-action="toggle-view" data-view="grid">Kart</button>
                <button type="button" class="btn btn-sm {{ $filters['view'] === 'table' ? 'btn-primary' : 'btn-outline-secondary' }}" data-action="toggle-view" data-view="table">Tablo</button>
            </div>
        </header>

        <div class="inv-products-list__grid" data-view-panel="grid">
            @foreach ($products as $product)
                <article class="inv-card inv-card--product" data-product-card>
                    <header class="inv-card__header">
                        <h3 class="inv-card__title">{{ $product->name }}</h3>
                        <p class="inv-card__subtitle">{{ $product->sku }}</p>
                    </header>
                    <p class="inv-card__body">{{ Str::limit($product->description, 80) }}</p>
                    <footer class="inv-card__footer">
                        <a href="{{ route('admin.inventory.products.show', $product) }}" class="btn btn-sm btn-outline-primary">Detay</a>
                        <a href="{{ route('admin.inventory.stock.console', ['mode' => 'transfer', 'product' => $product->id]) }}" class="btn btn-sm btn-outline-secondary">Transfer</a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="print-label">Etiket</button>
                    </footer>
                </article>
            @endforeach
        </div>

        <div class="inv-products-list__table" data-view-panel="table">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>SKU</th>
                        <th>Stok</th>
                        <th>Fiyat</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ number_format($product->stockItems->sum('qty') ?? 0, 2) }}</td>
                            <td>{{ number_format($product->price ?? 0, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.inventory.products.show', $product) }}" class="btn btn-sm btn-outline-primary">Detay</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="inv-products-list__pagination">
            {{ $products->links() }}
        </div>
    </div>
@endsection
