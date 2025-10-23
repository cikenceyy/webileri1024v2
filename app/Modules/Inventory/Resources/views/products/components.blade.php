@extends('layouts.admin')

@section('title', $product->name . ' • Kullanılan Malzemeler')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/components.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/components.js')
@endpush

@section('content')
    <section class="inv-components" data-product="{{ $product->id }}">
        <header class="inv-components__header">
            <h1 class="inv-components__title">{{ $product->name }}</h1>
            <form method="get" class="inv-components__lot">
                <label for="components-lot" class="form-label">Lot</label>
                <select id="components-lot" name="lot" class="form-select" data-action="change-lot">
                    @foreach ([1, 10, 100] as $lotOption)
                        <option value="{{ $lotOption }}" {{ $lot === $lotOption ? 'selected' : '' }}>{{ $lotOption }}</option>
                    @endforeach
                </select>
            </form>
        </header>

        <div class="inv-components__grid">
            @forelse ($components as $component)
                <article class="inv-components__card {{ $component['onHand'] < $component['required'] ? 'inv-components__card--insufficient' : '' }}" data-component="{{ $component['id'] }}">
                    <header class="inv-components__meta">
                        <h2 class="inv-components__name">{{ $component['name'] }}</h2>
                        <span class="inv-components__sku">{{ $component['sku'] }}</span>
                    </header>
                    <dl class="inv-components__stats">
                        <div>
                            <dt>Gereken</dt>
                            <dd>{{ number_format($component['required'], 2) }}</dd>
                        </div>
                        <div>
                            <dt>Mevcut</dt>
                            <dd>{{ number_format($component['onHand'], 2) }}</dd>
                        </div>
                        @if (!empty($component['warehouse']))
                            <div>
                                <dt>Depo</dt>
                                <dd>{{ $component['warehouse'] }}</dd>
                            </div>
                        @endif
                        @if (!empty($component['timestamp']))
                            <div>
                                <dt>Son Hareket</dt>
                                <dd>{{ $component['timestamp'] }}</dd>
                            </div>
                        @endif
                    </dl>
                    <footer class="inv-components__actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-action="component-procure">Tedarik</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="component-transfer">Depo Transferi</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-action="component-alternative">Alternatif Öner</button>
                    </footer>
                </article>
            @empty
                <p class="text-muted">Bu lot için bileşen bulunamadı.</p>
            @endforelse
        </div>
    </section>
@endsection
