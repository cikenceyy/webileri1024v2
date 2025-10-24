@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'returns')

@section('content')
    <div class="container-fluid py-4 consoles-page" data-console-root data-default-action="approve">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Returns Konsolu</h1>
                <p class="text-muted mb-0">RMA kayıtlarını hızlıca onaylayın, iade kabulü veya kapanış işlemlerini yürütün.</p>
            </div>
            <form method="get" action="{{ route('admin.consoles.returns.index') }}" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label" for="filter-status">Durum</label>
                    <select class="form-select form-select-sm" id="filter-status" name="status">
                        <option value="">Tümü</option>
                        <option value="open" @selected(($state['filters']['status'] ?? null) === 'open')>Açık</option>
                        <option value="approved" @selected(($state['filters']['status'] ?? null) === 'approved')>Onaylı</option>
                        <option value="closed" @selected(($state['filters']['status'] ?? null) === 'closed')>Kapalı</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary btn-sm" type="submit">Filtrele</button>
                </div>
            </form>
        </div>

        <div class="row g-4">
            <aside class="col-lg-2">
                <div class="list-group" data-console-stepper>
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center active" data-step-target="returns">
                        <span>RMA Kayıtları</span>
                        <span class="badge text-bg-primary rounded-pill">{{ number_format(count($state['returns'] ?? [])) }}</span>
                    </button>
                </div>
            </aside>
            <section class="col-lg-7">
                <div class="card shadow-sm border-0" data-step-list="returns">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="h6 mb-1">Bekleyen RMA'lar</h2>
                            <p class="text-muted small mb-0">Toplam {{ number_format(count($state['returns'] ?? [])) }} kayıt</p>
                        </div>
                        <div>
                            <input class="form-check-input" type="checkbox" data-console-select-all="returns" aria-label="Tümünü seç">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if(!empty($state['returns']))
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;"></th>
                                        <th>RMA</th>
                                        <th>Müşteri</th>
                                        <th>Durum</th>
                                        <th>Detay</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($state['returns'] as $item)
                                        <tr data-console-row data-console-step="returns" data-id="{{ $item['id'] }}">
                                            <td><input class="form-check-input" type="checkbox" value="{{ $item['id'] }}" data-console-checkbox></td>
                                            <td class="fw-semibold">{{ __('RMA #:id', ['id' => $item['id']]) }}</td>
                                            <td>{{ $item['customer'] ?? '—' }}</td>
                                            <td><span class="badge text-bg-light text-capitalize">{{ str($item['status'] ?? '')->replace('_', ' ')->headline() }}</span></td>
                                            <td class="small text-muted">{{ __('Kalem: :count', ['count' => $item['lines'] ?? 0]) }} · {{ $item['reason'] ?? '—' }} · {{ $item['created_at'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <p class="mb-1 fw-semibold">Bekleyen RMA kaydı yok</p>
                                <p class="mb-0 small">Yeni bir iade talebi oluşturmak için Marketing &gt; İadeler ekranını kullanın.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
            <aside class="col-lg-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0">
                        <h2 class="h6 mb-0">Aksiyon Paneli</h2>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.consoles.returns.action') }}" data-console-action-form>
                            @csrf
                            <input type="hidden" name="action" value="" data-console-action-input>
                            <div data-console-selected-inputs></div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-success" type="button" data-console-action="approve">Onayla</button>
                                <button class="btn btn-outline-secondary" type="button" data-console-action="close">Kapat</button>
                                <button class="btn btn-outline-primary" type="button" data-console-action="create_receipt">Mal Kabul Oluştur</button>
                            </div>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0"><kbd>/</kbd> arama, <kbd>A</kbd> tümünü seç.</p>
                    </div>
                </div>
            </aside>
        </div>

        <div class="console-footer bg-white border-top shadow-sm d-flex justify-content-between align-items-center px-3 py-2 mt-4" data-console-footer>
            <div>
                <strong data-console-selected-count>0</strong> kayıt seçildi.
            </div>
        </div>
    </div>
@endsection

@include('consoles::admin.partials.script')
