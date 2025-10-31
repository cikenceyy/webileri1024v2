{{--
    Amaç: Procure to Pay konsolu tablolarını TableKit görünümüyle eşleştirmek.
    İlişkiler: Codex Prompt — Console & TableKit Tablo Görünümü Eşleştirme.
    Notlar: Console aksiyon akışı korunarak yalnız tablo sınıfları güncellendi.
--}}
@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'p2p')

@section('content')
    <div class="container-fluid py-4 consoles-page" data-console-root data-default-action="receive_grn">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Procure to Pay Konsolu</h1>
                <p class="text-muted mb-0">Satın alma siparişinden mal kabule kadar kritik adımları hızlandırın.</p>
            </div>
            <form method="get" action="{{ route('admin.consoles.p2p.index') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label" for="filter-supplier">Tedarikçi ID</label>
                    <input class="form-control form-control-sm" id="filter-supplier" type="number" min="1" name="supplier_id" value="{{ $state['filters']['supplier_id'] ?? '' }}">
                </div>
                <div class="col-auto">
                    <label class="form-label" for="filter-status">Durum</label>
                    <select class="form-select form-select-sm" id="filter-status" name="status">
                        <option value="">Tümü</option>
                        <option value="draft" @selected(($state['filters']['status'] ?? null) === 'draft')>Taslak</option>
                        <option value="approved" @selected(($state['filters']['status'] ?? null) === 'approved')>Onaylı</option>
                        <option value="received" @selected(($state['filters']['status'] ?? null) === 'received')>Teslim Alındı</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm" type="submit">Filtrele</button>
                </div>
            </form>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Açık PO</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['open_pos'] ?? 0) }}</span>
                            <span class="text-muted small">adet</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Kabul Bekleyen</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['awaiting_receipt'] ?? 0) }}</span>
                            <span class="text-muted small">sevkiyat</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Bugün Alınan</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['received_today'] ?? 0) }}</span>
                            <span class="text-muted small">GRN</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php($steps = collect([
            ['key' => 'purchase_orders', 'label' => 'Satın Alma Siparişleri', 'items' => $state['steps']['purchase_orders']['items'] ?? []],
            ['key' => 'receipts', 'label' => 'Mal Kabuller', 'items' => $state['steps']['receipts']['items'] ?? []],
        ]))

        <div class="row g-4">
            <aside class="col-lg-2">
                <div class="list-group" data-stepper>
                    @foreach($steps as $step)
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @if($loop->first) active @endif" data-step-target="{{ $step['key'] }}">
                            <span>{{ $step['label'] }}</span>
                            <span class="badge text-bg-primary rounded-pill">{{ number_format(count($step['items'])) }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>
            <section class="col-lg-7">
                @foreach($steps as $step)
                    <div class="card shadow-sm border-0 @if(!$loop->first) d-none @endif" data-step-list="{{ $step['key'] }}">
                        <div class="card-header bg-white border-0">
                            <h2 class="h6 mb-0">{{ $step['label'] }}</h2>
                        </div>
                        <div class="card-body p-0">
                            @if(!empty($step['items']))
                                <div class="table-responsive tablekit-surface__wrapper">
                                    <table class="table table-hover mb-0 align-middle tablekit-surface">
                                        <thead>
                                        <tr>
                                            <th scope="col" class="tablekit-surface__select"><input type="checkbox" class="form-check-input" data-console-select-all="{{ $step['key'] }}"></th>
                                            <th scope="col">Belge</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col">Detay</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($step['items'] as $item)
                                            <tr data-console-row data-console-step="{{ $step['key'] }}" data-id="{{ $item['id'] ?? '' }}" data-amount="{{ $item['total'] ?? 0 }}">
                                                <td><input class="form-check-input" type="checkbox" value="{{ $item['id'] ?? '' }}" data-console-checkbox></td>
                                                <td class="fw-semibold">{{ $item['number'] ?? $item['doc_no'] ?? ('#' . ($item['id'] ?? '')) }}</td>
                                                <td><span class="badge text-bg-light text-capitalize">{{ str($item['status'] ?? '')->replace('_', ' ')->headline() }}</span></td>
                                                <td class="small text-muted">
                                                    @if($step['key'] === 'purchase_orders')
                                                        {{ __('Satır: :count', ['count' => $item['lines'] ?? 0]) }} · {{ __('Tutar: :amount', ['amount' => number_format($item['total'] ?? 0, 2)]) }}
                                                    @else
                                                        {{ __('Satır: :count', ['count' => $item['line_count'] ?? 0]) }} · {{ __('Durum: :status', ['status' => str($item['status'] ?? '')->headline()]) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <p class="mb-1 fw-semibold">Bu listede kayıt yok</p>
                                    <p class="mb-0 small">Filtreleri daraltarak uygun kayıtları bulun.</p>
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
                        <form method="post" action="{{ route('admin.consoles.p2p.action') }}" data-console-action-form>
                            @csrf
                            <input type="hidden" name="action" value="" data-console-action-input>
                            <input type="hidden" name="warehouse_id" value="">
                            <input type="hidden" name="reason" value="">
                            <div data-console-selected-inputs></div>

                            <div class="mb-3">
                                <label class="form-label">Mal Kabul Deposu</label>
                                <input type="number" class="form-control" name="warehouse_selector" data-console-warehouse min="1" placeholder="Varsayılan depo">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Varyans Nedeni</label>
                                <input type="text" class="form-control" data-console-reason placeholder="Örn. Vendor short">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" data-console-action="approve_pos">PO Onayla</button>
                                <button type="button" class="btn btn-outline-primary" data-console-action="po_to_grn">GRN Taslağı Oluştur</button>
                                <button type="button" class="btn btn-outline-success" data-console-action="receive_grn">Mal Kabulü Kaydet</button>
                                <button type="button" class="btn btn-outline-secondary" data-console-action="reconcile_grn">Varyans Kapat</button>
                            </div>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0">Kısayollar: <kbd>A</kbd> tümünü seç, <kbd>Enter</kbd> kabul et.</p>
                    </div>
                </div>
            </aside>
        </div>

        <div class="console-footer bg-white border-top shadow-sm d-flex justify-content-between align-items-center px-3 py-2 mt-4" data-console-footer>
            <div>
                <strong data-console-selected-count>0</strong> kayıt seçildi.
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Toplam sipariş tutarı:</span>
                <strong data-console-total-amount>0</strong>
            </div>
        </div>
    </div>
@endsection

@include('consoles::admin.partials.script')
