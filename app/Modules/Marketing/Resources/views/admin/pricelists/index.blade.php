@extends('layouts.admin')

@section('title', 'Fiyat Listeleri')
@section('module', 'Marketing')

@push('page-styles')
    @vite('app/Modules/Marketing/Resources/scss/pricelists.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Marketing/Resources/js/pricelists.js')
@endpush

@section('content')
    <section class="inv-prices inv-prices--list">
        <header class="inv-prices__header">
            <h1 class="inv-prices__title">Fiyat Listeleri</h1>
        </header>
        <div class="inv-prices__grid">
            @foreach ($priceLists as $list)
                <article class="inv-card">
                    <header class="inv-card__header">
                        <h2 class="inv-card__title">{{ $list->name }}</h2>
                        <span class="inv-badge">{{ $list->currency }}</span>
                    </header>
                    <p class="inv-card__body">{{ $list->items_count }} kalem • {{ $list->active ? 'Aktif' : 'Pasif' }}</p>
                    <footer class="inv-card__footer">
                        <a href="{{ route('admin.marketing.pricelists.show', $list) }}" class="btn btn-sm btn-outline-primary">Detaya git</a>
                    </footer>
                </article>
            @endforeach
        </div>
        <footer class="inv-prices__pagination">
            {{ $priceLists->links() }}
        </footer>
    </section>
@endsection
