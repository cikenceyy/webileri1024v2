@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'closeout')

@section('content')
    <div class="container-fluid py-4 consoles-page" data-console-root data-selection-mode="objects" data-print-action="batch_print">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Closeout Konsolu</h1>
                <p class="text-muted mb-0">Gün sonu belgelerini gözden geçirip toplu yazdırma ve istisna kontrollerini yönetin.</p>
            </div>
            <form method="get" action="{{ route('admin.consoles.closeout.index') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label" for="closeout-date">Tarih</label>
                    <input class="form-control form-control-sm" type="date" id="closeout-date" name="date" value="{{ $state['date'] ?? now()->toDateString() }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm" type="submit">Getir</button>
                </div>
            </form>
        </div>

        @php($steps = [
            ['key' => 'shipments', 'label' => 'Sevkiyatlar', 'items' => $state['shipments'] ?? [], 'type' => 'shipment'],
            ['key' => 'invoices', 'label' => 'Faturalar', 'items' => $state['invoices'] ?? [], 'type' => 'invoice'],
            ['key' => 'receipts', 'label' => 'Tahsilatlar', 'items' => $state['receipts'] ?? [], 'type' => 'receipt'],
            ['key' => 'goods_receipts', 'label' => 'Mal Kabul', 'items' => $state['goods_receipts'] ?? [], 'type' => 'goods_receipt'],
        ])
        @php($firstKey = $steps[0]['key'])

        <div class="row g-4">
            <aside class="col-lg-2">
                <div class="list-group" data-console-stepper>
                    @foreach($steps as $step)
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center @if($step['key'] === $firstKey) active @endif" data-step-target="{{ $step['key'] }}">
                            <span>{{ $step['label'] }}</span>
                            <span class="badge text-bg-primary rounded-pill">{{ number_format(count($step['items'])) }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>
            <section class="col-lg-7">
                @foreach($steps as $step)
                    <div class="card shadow-sm border-0 @if($step['key'] !== $firstKey) d-none @endif" data-step-list="{{ $step['key'] }}">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h6 mb-1">{{ $step['label'] }}</h2>
                                <p class="text-muted small mb-0">{{ __('Toplam :count kayıt', ['count' => number_format(count($step['items']))]) }}</p>
                            </div>
                            <div>
                                <input class="form-check-input" type="checkbox" data-console-select-all="{{ $step['key'] }}" aria-label="Tümünü seç">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(!empty($step['items']))
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width: 40px;"></th>
                                            <th>Belge</th>
                                            <th>Durum</th>
                                            <th>Detay</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($step['items'] as $item)
                                            @php($amount = $item->grand_total ?? $item->amount ?? 0)
                                            <tr
                                                data-console-row
                                                data-console-step="{{ $step['key'] }}"
                                                data-selection-type="{{ $step['type'] }}"
                                                data-selection-id="{{ $item->getKey() }}"
                                                data-amount="{{ $amount }}"
                                            >
                                                <td><input class="form-check-input" type="checkbox" value="{{ $item->getKey() }}" data-console-checkbox></td>
                                                <td class="fw-semibold">{{ $item->doc_no ?? ('#' . $item->getKey()) }}</td>
                                                <td><span class="badge text-bg-light text-capitalize">{{ str($item->status ?? 'kaydedildi')->replace('_', ' ')->headline() }}</span></td>
                                                <td class="small text-muted">
                                                    @switch($step['key'])
                                                        @case('shipments')
                                                            {{ $item->shipped_at?->format('H:i') ?? '—' }} · {{ __('Satır: :count', ['count' => $item->lines()->count()]) }}
                                                            @break
                                                        @case('invoices')
                                                            {{ __('Tutar: :amount', ['amount' => number_format($item->grand_total ?? 0, 2)]) }} · {{ __('Vade: :due', ['due' => optional($item->due_date)->format('Y-m-d')]) }}
                                                            @break
                                                        @case('receipts')
                                                            {{ __('Tutar: :amount', ['amount' => number_format($item->amount ?? 0, 2)]) }} · {{ __('Müşteri #:id', ['id' => $item->customer_id ?? '—']) }}
                                                            @break
                                                        @default
                                                            {{ __('Tutar: :amount', ['amount' => number_format($item->qty_received ?? 0, 2)]) }} · {{ $item->received_at?->format('H:i') ?? '—' }}
                                                    @endswitch
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <p class="mb-1 fw-semibold">Kayıt bulunamadı</p>
                                    <p class="mb-0 small">Bu adımda gün sonu belgesi oluşmadı.</p>
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
                        <form method="post" action="{{ route('admin.consoles.closeout.print') }}" data-console-action-form>
                            @csrf
                            <input type="hidden" name="action" value="" data-console-action-input>
                            <div data-console-selected-inputs></div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" type="button" data-console-action="batch_print">Seçili Belgeleri Yazdır</button>
                            </div>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0">Seçili kayıtların yazdırılabilir bağlantıları yeni sekmede açılacaktır.</p>
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
