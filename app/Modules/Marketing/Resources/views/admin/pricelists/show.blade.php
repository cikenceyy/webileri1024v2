@extends('layouts.admin')

@section('title', $pricelist->name)
@section('module', 'Marketing')

@push('page-styles')
    @vite('app/Modules/Marketing/Resources/scss/pricelists.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Marketing/Resources/js/pricelists.js')
@endpush

@section('content')
    @php($averagePrice = round($pricelist->items->avg('price') ?? 0, 2))
    <section class="inv-prices inv-prices--detail" data-pricelist="{{ $pricelist->id }}">
        <header class="inv-prices__hero">
            <div>
                <p class="inv-prices__eyebrow">{{ __('Fiyat Listesi') }}</p>
                <h1 class="inv-prices__title">{{ $pricelist->name }}</h1>
                <p class="inv-prices__subtitle">
                    {{ __(':currency para biriminde :count kalem içeriyor.', ['currency' => $pricelist->currency, 'count' => $pricelist->items->count()]) }}
                </p>
            </div>
            <div class="inv-prices__hero-actions">
                <span class="inv-badge @if($pricelist->active) inv-badge--success @else inv-badge--muted @endif">
                    {{ $pricelist->active ? __('Aktif') : __('Pasif') }}
                </span>
                <a href="{{ route('admin.marketing.pricelists.bulk.form', $pricelist) }}" class="btn btn-outline-primary">
                    <i class="bi bi-magic me-1"></i> {{ __('Toplu Güncelle') }}
                </a>
            </div>
        </header>

        <div class="inv-prices__layout">
            <aside class="inv-prices__sim-card" data-simulation>
                <h2 class="inv-prices__section-title">{{ __('Mini Simülasyon') }}</h2>
                <p class="inv-prices__section-hint">{{ __('Satış temsilcilerinizin standart koşullarda ödeyeceği tutarı hızlıca hesaplayın.') }}</p>
                <form class="inv-prices__sim-form" data-sim-form>
                    @csrf
                    <input type="hidden" name="currency" value="{{ $pricelist->currency }}">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="sim-quantity">{{ __('Adet') }}</label>
                            <input type="number" min="1" step="1" class="form-control" id="sim-quantity" name="quantity" value="5" data-sim-input>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="sim-price">{{ __('Birim fiyat') }}</label>
                            <input type="number" min="0" step="0.01" class="form-control" id="sim-price" name="price" value="{{ $averagePrice }}" data-sim-input>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="sim-discount">{{ __('İskonto (%)') }}</label>
                            <input type="number" min="0" max="100" step="0.5" class="form-control" id="sim-discount" name="discount" value="0" data-sim-input>
                        </div>
                        <div class="col-sm-6 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" data-action="simulate">
                                <i class="bi bi-calculator me-1"></i> {{ __('Hesapla') }}
                            </button>
                        </div>
                    </div>
                </form>
                <dl class="inv-prices__sim-output">
                    <dt>{{ __('Tahmini Tutar') }}</dt>
                    <dd data-sim-output>{{ __('Sonuç bekleniyor…') }}</dd>
                    <dt class="mt-3">{{ __('Özet') }}</dt>
                    <dd data-sim-summary class="text-muted small">{{ __('Adet ve fiyat değerlerini değiştirin.') }}</dd>
                </dl>
            </aside>

            <section class="inv-prices__items-card" data-items>
                <div class="inv-prices__items-header">
                    <h2 class="inv-prices__section-title">{{ __('Kalemler') }}</h2>
                    <span class="text-muted">{{ __('Toplam :count ürün', ['count' => $pricelist->items->count()]) }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">{{ __('Ürün') }}</th>
                                <th scope="col" class="text-end">{{ __('Fiyat') }}</th>
                                <th scope="col" class="text-end">{{ __('Güncellenme') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pricelist->items as $item)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $item->product?->name ?? __('Ürün') }}</span>
                                        <p class="text-muted small mb-0">SKU #{{ $item->product_id }}</p>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary-subtle text-primary fs-6">{{ number_format($item->price, 2) }} {{ $pricelist->currency }}</span>
                                    </td>
                                    <td class="text-end text-muted small">
                                        {{ optional($item->updated_at)->diffForHumans() ?? __('Bilinmiyor') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <h3 class="h5">{{ __('Liste henüz boş') }}</h3>
                                        <p class="text-muted mb-0">{{ __('Ürün eklemek için toplu güncelleme aracını kullanabilirsiniz.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
@endsection
