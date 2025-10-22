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
                        $threshold = (float) ($item->reorder_point ?? $item->product?->reorder_point ?? 0);
                        $ratio = $threshold > 0 ? min(1, max(0, $item->qty / $threshold)) : 1;
                        $level = (int) ceil($ratio * 5);
                        $payload = [[
                            'id' => $item->product?->id,
                            'name' => $item->product?->name,
                            'sku' => $item->product?->sku,
                            'qty' => round($item->qty, 2),
                            'reserved' => round($item->reserved_qty ?? 0, 2),
                        ]];
                    @endphp
                    <button type="button"
                            class="inv-heat__cell inv-warehouse__cell"
                            data-action="select-cell"
                            data-rack="R{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}"
                            data-level="{{ $level }}"
                            data-items='@json($payload)'>
                        <span class="inv-warehouse__cell-label">{{ $item->product?->sku ?? 'SKU' }}</span>
                        <span class="inv-warehouse__cell-qty">{{ number_format($item->qty, 2) }}</span>
                    </button>
                @endforeach
            </div>

            <aside class="inv-warehouse__panel" data-panel-region>
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
