@extends('layouts.admin')

@section('title', $warehouse->name)
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/warehouses.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/warehouses.js')
@endpush

@section('content')
    <section class="inv-warehouse" data-warehouse="{{ $warehouse->id }}">
        <header class="inv-warehouse__header">
            <h1 class="inv-warehouse__title">{{ $warehouse->name }}</h1>
            <p class="inv-warehouse__subtitle">Kod: {{ $warehouse->code ?? '—' }}</p>
        </header>

        <div class="inv-warehouse__layout">
            <div class="inv-warehouse__grid" data-heatmap>
                @foreach ($stockItems as $index => $item)
                    @php
                        $level = min(5, max(0, (int) ceil($item->qty)));
                    @endphp
                    <button type="button"
                            class="inv-warehouse__cell"
                            data-action="select-cell"
                            data-level="{{ $level }}"
                            data-product-id="{{ $item->product?->id }}">
                        <span class="inv-warehouse__cell-label">{{ $item->product?->sku ?? 'SKU' }}</span>
                        <span class="inv-warehouse__cell-qty">{{ number_format($item->qty, 2) }}</span>
                    </button>
                @endforeach
            </div>

            <aside class="inv-warehouse__panel" data-panel>
                <h2 class="inv-warehouse__panel-title">Seçili Raf</h2>
                <div class="inv-warehouse__panel-body" data-panel-body>
                    <p class="text-muted">Bir hücre seçerek ürün detaylarını görüntüleyin.</p>
                </div>
                <div class="inv-warehouse__actions">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-action="transfer">Transfer</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="adjust">Düzeltme</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-action="label">Etiket Yazdır</button>
                </div>
            </aside>
        </div>

        <footer class="inv-warehouse__pagination">
            {{ $stockItems->links() }}
        </footer>
    </section>
@endsection
