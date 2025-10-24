@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'mto')

@section('content')
    <div class="container-fluid py-4 consoles-page" data-console-root data-default-action="issue">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Make to Order Konsolu</h1>
                <p class="text-muted mb-0">Sipariş satırlarını iş emri haline getirin, malzeme çıkışını ve tamamlamayı hızlandırın.</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Taslak İş Emirleri</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['draft'] ?? 0) }}</span>
                            <span class="text-muted small">adet</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Devam Eden</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['released'] ?? 0) }}</span>
                            <span class="text-muted small">adet</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <span class="text-uppercase small text-muted">Tamamlanan</span>
                        <div class="d-flex align-items-baseline gap-2 mt-2">
                            <span class="h2 fw-bold text-primary mb-0">{{ number_format($state['totals']['completed'] ?? 0) }}</span>
                            <span class="text-muted small">adet</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php($steps = collect([
            ['key' => 'candidates', 'label' => 'MTO Adayları', 'items' => $state['steps'][0]['items'] ?? []],
            ['key' => 'work_orders', 'label' => 'İş Emirleri', 'items' => $state['steps'][1]['items'] ?? []],
        ]))

        <div class="row g-4">
            <aside class="col-lg-2">
                <div class="list-group">
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
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                        <tr>
                                            <th style="width:40px;"><input type="checkbox" class="form-check-input" data-console-select-all="{{ $step['key'] }}"></th>
                                            <th>Belge</th>
                                            <th>Durum</th>
                                            <th>Detay</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($step['items'] as $item)
                                            <tr data-console-row data-console-step="{{ $step['key'] }}" data-id="{{ $item['id'] ?? '' }}" data-amount="{{ $item['target_qty'] ?? $item['qty'] ?? 0 }}">
                                                <td><input class="form-check-input" type="checkbox" value="{{ $item['id'] ?? '' }}" data-console-checkbox></td>
                                                <td class="fw-semibold">{{ $item['doc_no'] ?? $item['order_no'] ?? ('#' . ($item['id'] ?? '')) }}</td>
                                                <td><span class="badge text-bg-light text-capitalize">{{ str($item['status'] ?? '')->replace('_', ' ')->headline() }}</span></td>
                                                <td class="small text-muted">
                                                    @if($step['key'] === 'candidates')
                                                        {{ $item['customer'] ?? '—' }} · {{ __('Miktar: :qty', ['qty' => number_format($item['qty'] ?? 0, 2)]) }} · {{ __('Stok: :signal', ['signal' => ucfirst($item['signal'] ?? '—')]) }}
                                                    @else
                                                        {{ __('Hedef: :qty', ['qty' => number_format($item['target_qty'] ?? 0, 2)]) }} · {{ __('İssue: :count / Receipt: :receipt', ['count' => $item['issues'] ?? 0, 'receipt' => $item['receipts'] ?? 0]) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <p class="mb-1 fw-semibold">Gösterilecek kayıt yok</p>
                                    <p class="mb-0 small">İş emri oluşturmak için uygun aday bulunamadı.</p>
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
                        <form method="post" action="{{ route('admin.consoles.mto.action') }}" data-console-action-form>
                            @csrf
                            <input type="hidden" name="action" value="" data-console-action-input>
                            <input type="hidden" name="qty" value="">
                            <div data-console-selected-inputs></div>

                            <div class="mb-3">
                                <label class="form-label">Tamamlama Miktarı</label>
                                <input type="number" class="form-control" min="0" step="0.01" placeholder="İş emri hedefi" data-console-complete-qty>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" data-console-action="plan">İş Emri Oluştur</button>
                                <button type="button" class="btn btn-outline-secondary" data-console-action="issue">Malzeme Çık</button>
                                <button type="button" class="btn btn-outline-success" data-console-action="complete">Tamamla</button>
                                <button type="button" class="btn btn-outline-secondary" data-console-action="close">Kapat</button>
                            </div>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0">Enter tuşu hızlı malzeme çıkışı yapar.</p>
                    </div>
                </div>
            </aside>
        </div>

        <div class="console-footer bg-white border-top shadow-sm d-flex justify-content-between align-items-center px-3 py-2 mt-4" data-console-footer>
            <div>
                <strong data-console-selected-count>0</strong> kayıt seçildi.
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted">Toplam miktar:</span>
                <strong data-console-total-amount>0</strong>
            </div>
        </div>
    </div>
@endsection

@include('consoles::admin.partials.script')

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-console-root]').forEach((root) => {
                const qtyInput = root.querySelector('[data-console-complete-qty]');
                const actionForm = root.querySelector('[data-console-action-form]');
                if (!qtyInput || !actionForm) return;
                const qtyField = actionForm.querySelector('input[name="qty"]');
                root.querySelectorAll('[data-console-action]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (qtyField) {
                            qtyField.value = qtyInput.value || '';
                        }
                    });
                });
            });
        });
    </script>
@endpush
