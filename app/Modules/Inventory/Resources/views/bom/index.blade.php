@extends('layouts.admin')

@section('title', 'Ürün Reçeteleri')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/bom.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/bom.js')
@endpush

@section('content')
    <section class="inv-bom inv-bom--list">
        <header class="inv-bom__header">
            <h1 class="inv-bom__title">Ürün Reçeteleri</h1>
        </header>
        <div class="inv-bom__grid">
            @foreach ($products as $product)
                <article class="inv-card">
                    <header class="inv-card__header">
                        <h2 class="inv-card__title">{{ $product->name }}</h2>
                        <span class="inv-badge">SKU: {{ $product->sku }}</span>
                    </header>
                    <p class="inv-card__body">Reçete revizyonu: v1</p>
                    <footer class="inv-card__footer">
                        <a href="{{ route('admin.inventory.bom.show', $product) }}" class="btn btn-sm btn-outline-primary">Detaya git</a>
                    </footer>
                </article>
            @endforeach
        </div>
        <footer class="inv-bom__pagination">
            {{ $products->links() }}
        </footer>
    </section>
@endsection
