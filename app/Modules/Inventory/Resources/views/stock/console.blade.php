@extends('layouts.admin')

@section('title', 'Stok İşlem Konsolu')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/stock_console.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/stock_console.js')
@endpush

@section('content')
    <div class="inv-console"
         data-mode="{{ $mode }}"
         data-endpoint="{{ route('admin.inventory.stock.console.store') }}"
         data-allow-negative="{{ $allowNegative ? 'true' : 'false' }}">
        <header class="inv-console__tabs" role="tablist">
            @foreach (['in' => 'Giriş', 'out' => 'Çıkış', 'transfer' => 'Transfer', 'adjust' => 'Düzeltme'] as $tabMode => $label)
                <a href="{{ route('admin.inventory.stock.console', ['mode' => $tabMode]) }}"
                   class="inv-console__tab {{ $mode === $tabMode ? 'is-active' : '' }}"
                   role="tab"
                   data-console-tab="{{ $tabMode }}">
                    {{ $label }}
                </a>
            @endforeach
        </header>

        <section class="inv-console__filters" aria-label="İşlem parametreleri">
            <form class="row g-3" data-console-form>
                <div class="col-md-3">
                    <label class="form-label">Kaynak Depo</label>
                    <select class="form-select" name="source_warehouse_id">
                        <option value="">Depo seçin</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hedef Depo</label>
                    <select class="form-select" name="target_warehouse_id">
                        <option value="">Depo seçin</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Belge No</label>
                    <input type="text" class="form-control" name="reference" placeholder="Opsiyonel">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tarih / Saat</label>
                    <input type="datetime-local" class="form-control" name="moved_at" value="{{ $defaultMovedAt }}">
                </div>
                <div class="col-12">
                    <label class="form-label">Ürün Ara / Barkod</label>
                    <input type="search"
                           class="form-control"
                           data-action="product-search"
                           placeholder="SKU, barkod ya da isim"
                           autocomplete="off"
                           inputmode="search"
                           data-endpoint="{{ route('admin.inventory.stock.console.lookup') }}">
                </div>
                <div class="col-12">
                    <div class="alert d-none" data-console-feedback role="alert" aria-live="assertive"></div>
                </div>
            </form>
        </section>

        <section class="inv-console__body">
            <div class="inv-console__cart" data-cart-region>
                <p class="text-muted">Sepete ürün eklemek için arayın veya barkodu okutun.</p>
            </div>

            <aside class="inv-console__keypad" aria-label="Sayısal tuş takımı">
                <div class="inv-keypad">
                    @foreach ([7,8,9,4,5,6,1,2,3,0,'.'] as $key)
                        <button type="button" class="inv-keypad__key" data-key="{{ $key }}">{{ $key }}</button>
                    @endforeach
                    <button type="button" class="inv-keypad__key" data-key="plus">+1</button>
                    <button type="button" class="inv-keypad__key" data-key="minus">-1</button>
                    <button type="button" class="inv-keypad__key" data-key="del">Sil</button>
                </div>
                <div class="inv-console__summary" data-summary-region>
                    <dl class="inv-console__summary-list">
                        <div>
                            <dt>Kalem</dt>
                            <dd data-summary-lines>0</dd>
                        </div>
                        <div>
                            <dt>Toplam Miktar</dt>
                            <dd data-summary-qty>0</dd>
                        </div>
                        <div>
                            <dt>Tahmini Değer</dt>
                            <dd data-summary-value>0</dd>
                        </div>
                    </dl>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-action="console-submit">Kaydet</button>
                        <button type="button" class="btn btn-outline-secondary" data-action="console-reset">Temizle</button>
                        <button type="button" class="btn btn-outline-secondary" data-action="console-print">Yazdır</button>
                        <button type="button" class="btn btn-outline-secondary" data-action="console-share">Paylaş</button>
                    </div>
                </div>
            </aside>
        </section>

        <section class="inv-console__suggestions" aria-label="Son eklenen ürünler">
            <h2 class="inv-console__suggestions-title">Hızlı Seçim</h2>
            <div class="inv-console__suggestion-grid">
                @foreach ($recentProducts as $product)
                    <button type="button"
                            class="inv-console__suggestion"
                            data-action="cart-select"
                            data-item-id="{{ $product->id }}"
                            data-item='@json([
                                "id" => $product->id,
                                "name" => $product->name,
                                "sku" => $product->sku,
                                "price" => (float) ($product->price ?? 0),
                                "unit" => $product->baseUnit?->code,
                                "onHand" => round($product->stockItems->sum("qty"), 2),
                            ])'>
                        <span class="inv-console__suggestion-name">{{ $product->name }}</span>
                        <span class="inv-console__suggestion-meta">{{ $product->sku }}</span>
                    </button>
                @endforeach
            </div>
        </section>
    </div>
@endsection
