@extends('layouts.admin')

@section('title', 'Depolar')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/warehouses.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/warehouses.js')
@endpush

@section('content')
    <section class="inv-warehouse inv-warehouse--list">
        <header class="inv-warehouse__header">
            <h1 class="inv-warehouse__title">Depo Listesi</h1>
        </header>
        <div class="inv-warehouse__grid">
            @foreach ($warehouses as $warehouse)
                @php
                    $stat = $stats->get($warehouse->id);
                @endphp
                <article class="inv-card inv-card--action">
                    <header class="inv-card__header">
                        <h2 class="inv-card__title">{{ $warehouse->name }}</h2>
                        <p class="inv-card__subtitle">Kod: {{ $warehouse->code ?? '—' }}</p>
                    </header>
                    <dl class="inv-card__stats">
                        <div>
                            <dt>Toplam Miktar</dt>
                            <dd>{{ number_format($stat->total_qty ?? 0, 2) }}</dd>
                        </div>
                        <div>
                            <dt>Kalem</dt>
                            <dd>{{ number_format($stat->line_count ?? 0) }}</dd>
                        </div>
                    </dl>
                    <footer class="inv-card__footer">
                        <a href="{{ route('admin.inventory.warehouses.show', $warehouse) }}" class="btn btn-sm btn-outline-primary">Detaya git</a>
                    </footer>
                </article>
            @endforeach
        </div>
    </section>
@endsection
