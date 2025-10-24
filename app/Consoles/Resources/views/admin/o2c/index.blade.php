@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'o2c')

@section('content')
    <div class="container-fluid py-4 consoles-page" data-console-root data-default-action="shipments_ship" data-print-action="shipments_ship">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Order to Cash Konsolu</h1>
                <p class="text-muted mb-0">Siparişten tahsilata kadar tüm adımları tek ekranda yönetin.</p>
            </div>
            <form method="get" action="{{ route('admin.consoles.o2c.index') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label" for="filter-status">Durum</label>
                    <select class="form-select form-select-sm" id="filter-status" name="status">
                        <option value="">Tümü</option>
                        <option value="confirmed" @selected(($state['filters']['status'] ?? null) === 'confirmed')>Onaylı</option>
                        <option value="fulfilled" @selected(($state['filters']['status'] ?? null) === 'fulfilled')>Tamamlandı</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label" for="filter-customer">Müşteri ID</label>
                    <input class="form-control form-control-sm" id="filter-customer" type="number" min="1" name="customer_id" value="{{ $state['filters']['customer_id'] ?? '' }}">
                </div>
                <div class="col-auto">
                    <label class="form-label" for="filter-from">Başlangıç</label>
                    <input class="form-control form-control-sm" id="filter-from" type="date" name="from" value="{{ $state['filters']['from'] ?? '' }}">
                </div>
                <div class="col-auto">
                    <label class="form-label" for="filter-to">Bitiş</label>
                    <input class="form-control form-control-sm" id="filter-to" type="date" name="to" value="{{ $state['filters']['to'] ?? '' }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm" type="submit">Filtrele</button>
                </div>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Onaylı Siparişler</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['orders'] ?? 0) }}</span>
                            <span class="text-muted small">adet</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Açık Sevkiyat</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['shipments'] ?? 0) }}</span>
                            <span class="text-muted small">adet</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Tahsilat Bekleyen</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['invoices_due'] ?? 0) }}</span>
                            <span class="text-muted small">fatura</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Bugünkü Tahsilatlar</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['receipts_today'] ?? 0) }}</span>
                            <span class="text-muted small">kayıt</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php($steps = collect($state['steps'] ?? [])->keyBy('key'))
        @php($currentKey = $steps->keys()->first())

        <div class="row g-4">
            <aside class="col-lg-2">
                <div class="list-group" data-console-stepper>
                    @foreach($steps as $key => $step)
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @if($loop->first) active @endif" data-step-target="{{ $key }}">
                            <span>{{ $step['label'] ?? str($key)->headline() }}</span>
                            <span class="badge text-bg-primary rounded-pill">{{ number_format(count($step['items'] ?? [])) }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>
            <section class="col-lg-7">
                @foreach($steps as $key => $step)
                    <div class="card shadow-sm border-0 console-list @if(!$loop->first) d-none @endif" data-step-list="{{ $key }}">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h6 mb-1">{{ $step['label'] ?? str($key)->headline() }}</h2>
                                <p class="text-muted small mb-0">Toplam {{ number_format(count($step['items'] ?? [])) }} kayıt</p>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(!empty($step['items']))
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;"><input class="form-check-input" type="checkbox" data-console-select-all="{{ $key }}"></th>
                                            <th>Belge</th>
                                            <th>Durum</th>
                                            <th>Detay</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($step['items'] as $item)
                                            <tr data-console-row data-console-step="{{ $key }}" data-id="{{ $item['id'] ?? '' }}" data-amount="{{ $item['total'] ?? $item['balance'] ?? $item['amount'] ?? 0 }}">
                                                <td><input class="form-check-input" type="checkbox" value="{{ $item['id'] ?? '' }}" data-console-checkbox></td>
                                                <td class="fw-semibold">{{ $item['doc_no'] ?? $item['order_no'] ?? ('#' . ($item['id'] ?? '')) }}</td>
                                                <td><span class="badge text-bg-light text-capitalize">{{ str($item['status'] ?? '')->replace('_', ' ')->headline() }}</span></td>
                                                <td class="small text-muted">
                                                    @if($key === 'orders')
                                                        {{ $item['customer'] ?? '—' }} · {{ __('Kalem: :count', ['count' => $item['line_count'] ?? 0]) }} · {{ __('Stok: :signal', ['signal' => ucfirst($item['signal'] ?? '—')]) }}
                                                    @elseif($key === 'shipments')
                                                        {{ __('Satır: :count', ['count' => $item['lines'] ?? 0]) }} · {{ __('Depo #:id', ['id' => $item['warehouse_id'] ?? '—']) }}
                                                    @elseif($key === 'invoices')
                                                        {{ __('Bakiye: :amount', ['amount' => number_format($item['balance'] ?? 0, 2)]) }} · {{ __('Vade: :date', ['date' => $item['due_date'] ?? '—']) }}
                                                    @else
                                                        {{ __('Tutar: :amount', ['amount' => number_format($item['amount'] ?? 0, 2)]) }} · {{ $item['received_at'] ?? '' }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <p class="mb-1 fw-semibold">Bu adımda bekleyen kayıt yok</p>
                                    <p class="mb-0 small">Filtreleri güncelleyerek farklı kayıtları görüntüleyebilirsiniz.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
            <aside class="col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0">
                        <h2 class="h6 mb-0">Aksiyon Paneli</h2>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.consoles.o2c.action') }}" data-console-action-form>
                            @csrf
                            <input type="hidden" name="action" value="" data-console-action-input>
                            <input type="hidden" name="warehouse_id" value="">
                            <div data-console-selected-inputs></div>

                            <div class="mb-4">
                                <label class="form-label">Sevkiyat Deposu</label>
                                <input type="number" class="form-control" min="1" name="warehouse_selector" placeholder="Varsayılan depo" data-console-warehouse>
                                <div class="form-text">Boş bırakılırsa ayarlardaki varsayılan depo kullanılır.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" type="button" data-console-action="orders_to_shipments">Sevkiyat Oluştur</button>
                                <button class="btn btn-outline-secondary" type="button" data-console-action="shipments_pick">Pick Tamamla</button>
                                <button class="btn btn-outline-secondary" type="button" data-console-action="shipments_pack">Pack Tamamla</button>
                                <button class="btn btn-outline-success" type="button" data-console-action="shipments_ship">Ship &amp; Yazdır</button>
                                <hr>
                                <button class="btn btn-outline-primary" type="button" data-console-action="orders_to_invoices">Faturalandır</button>
                                <button class="btn btn-outline-primary" type="button" data-console-action="invoices_apply_receipt">Tahsil Et</button>
                            </div>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0">
                            <kbd>/</kbd> arama, <kbd>A</kbd> tümünü seç, <kbd>Enter</kbd> varsayılan aksiyon, <kbd>P</kbd> seçili sevkiyatı yazdır.
                        </p>
                    </div>
                </div>
            </aside>
        </div>

        <div class="console-footer bg-white border-top shadow-sm d-flex justify-content-between align-items-center px-3 py-2 mt-4" data-console-footer>
            <div>
                <strong data-console-selected-count>0</strong> kayıt seçildi.
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Toplam tutar:</span>
                <strong data-console-total-amount>0</strong>
            </div>
        </div>
    </div>
@endsection

@include('consoles::admin.partials.script')
