@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'replenish')

@section('content')
    <div class="container-fluid py-4 consoles-page" data-console-root data-default-action="replenish_transfer" data-selection-mode="lines">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Replenish Konsolu</h1>
                <p class="text-muted mb-0">Düşük stoklu ürünleri depolar arasında hızlıca dengeleyin.</p>
            </div>
            <form method="get" action="{{ route('admin.consoles.replenish.index') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label" for="filter-threshold">Eşik</label>
                    <input class="form-control form-control-sm" id="filter-threshold" type="number" step="0.01" name="threshold" value="{{ $state['filters']['threshold'] ?? 0 }}">
                </div>
                <div class="col-auto">
                    <label class="form-label" for="filter-product">Ürün ID</label>
                    <input class="form-control form-control-sm" id="filter-product" type="number" min="1" name="product_id" value="{{ $state['filters']['product_id'] ?? '' }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm" type="submit">Filtrele</button>
                </div>
            </form>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h2 class="h6 mb-0">Düşük Stoklu Ürünler</h2>
                <span class="badge text-bg-primary rounded-pill">{{ number_format(count($state['low_stock'] ?? [])) }}</span>
            </div>
            <div class="card-body p-0">
                @if(!empty($state['low_stock']))
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                            <tr>
                                <th style="width:40px;"><input type="checkbox" class="form-check-input" data-console-select-all="low"></th>
                                <th>Ürün</th>
                                <th>Depo</th>
                                <th>Mevcut</th>
                                <th>Transfer Miktarı</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($state['low_stock'] as $index => $item)
                                <tr data-console-row data-console-step="low" data-product-id="{{ $item['product_id'] }}" data-amount="{{ abs($item['balance'] ?? 0) }}">
                                    <td><input class="form-check-input" type="checkbox" value="{{ $item['product_id'] }}:{{ $item['warehouse_id'] ?? 0 }}" data-console-checkbox></td>
                                    <td class="fw-semibold">{{ $item['product'] ?? __('Ürün #:id', ['id' => $item['product_id']]) }} <span class="text-muted small">{{ $item['sku'] ?? '' }}</span></td>
                                    <td>{{ $item['warehouse_id'] ? __('Depo #:id', ['id' => $item['warehouse_id']]) : '—' }}</td>
                                    <td class="small text-muted">{{ number_format($item['balance'] ?? 0, 2) }}</td>
                                    <td style="width:160px;">
                                        <input type="number" class="form-control form-control-sm" step="0.01" min="0" data-console-qty value="{{ max(0, ($item['threshold'] ?? 0) - ($item['balance'] ?? 0)) }}">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <p class="mb-1 fw-semibold">Düşük stoklu kayıt bulunamadı</p>
                        <p class="mb-0 small">Eşik değerini değiştirerek aramayı genişletebilirsiniz.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-4 mt-4">
            <div class="col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0">
                        <h2 class="h6 mb-0">Transfer Parametreleri</h2>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.consoles.replenish.transfer') }}" data-console-action-form>
                            @csrf
                            <input type="hidden" name="action" value="replenish_transfer" data-console-action-input>
                            <div data-console-selected-inputs></div>

                            <div class="mb-3">
                                <label class="form-label">Kaynak Depo</label>
                                <input type="number" class="form-control" name="from_warehouse_id" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hedef Depo</label>
                                <input type="number" class="form-control" name="to_warehouse_id" min="1" required>
                            </div>

                            <button type="button" class="btn btn-outline-success w-100" data-console-action="replenish_transfer">Transfer Oluştur &amp; Post Et</button>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0">Seçili satırlar kadar transfer satırı oluşturulur.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="alert alert-info border-0 shadow-sm">
                    <strong>{{ __('İpucu:') }}</strong> Seçtiğiniz satırlar için transfer miktarlarını sağdaki inputlardan düzenleyebilirsiniz. Eksi stoklar için threshold değerini yükseltin.
                </div>
            </div>
        </div>

        <div class="console-footer bg-white border-top shadow-sm d-flex justify-content-between align-items-center px-3 py-2 mt-4" data-console-footer>
            <div>
                <strong data-console-selected-count>0</strong> satır seçildi.
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Toplam transfer miktarı:</span>
                <strong data-console-total-amount>0</strong>
            </div>
        </div>
    </div>
@endsection

@include('consoles::admin.partials.script')
