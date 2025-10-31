{{--
    Amaç: Quality konsolu tablolarını TableKit görünümüyle eşitlemek.
    İlişkiler: Codex Prompt — Console & TableKit Tablo Görünümü Eşleştirme.
    Notlar: Seçim kancaları korunarak görsel sınıflar güncellendi.
--}}
@extends('layouts.admin')

@php($module = 'Consoles')
@php($page = 'quality')

@section('content')
    <div class="container-fluid py-4 consoles-page" data-console-root data-default-action="pass" data-selection-mode="objects">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Quality Konsolu</h1>
                <p class="text-muted mb-0">Gelen ve giden sevkiyatlar için kalite kontrollerini kaydedin, başarısız kayıtları engelleyin.</p>
            </div>
        </div>

        <div class="row g-4">
            <aside class="col-lg-2">
                <div class="list-group" data-console-stepper>
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center active" data-step-target="incoming">
                        <span>Gelen Kontroller</span>
                        <span class="badge text-bg-primary rounded-pill">{{ number_format(count($state['incoming'] ?? [])) }}</span>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-step-target="outgoing">
                        <span>Giden Kontroller</span>
                        <span class="badge text-bg-primary rounded-pill">{{ number_format(count($state['outgoing'] ?? [])) }}</span>
                    </button>
                </div>
            </aside>
            <section class="col-lg-7">
                @foreach(['incoming' => \App\Modules\Logistics\Domain\Models\GoodsReceipt::class, 'outgoing' => \App\Modules\Logistics\Domain\Models\Shipment::class] as $direction => $modelClass)
                    <div class="card shadow-sm border-0 @if($loop->first) @else d-none @endif" data-step-list="{{ $direction }}">
                        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h6 mb-1">{{ $direction === 'incoming' ? 'Gelen Mal Kabul' : 'Giden Sevkiyat' }}</h2>
                                <p class="text-muted small mb-0">{{ __('Toplam :count kayıt', ['count' => number_format(count($state[$direction] ?? []))]) }}</p>
                            </div>
                            <div>
                                <input class="form-check-input" type="checkbox" data-console-select-all="{{ $direction }}" aria-label="Tümünü seç">
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(!empty($state[$direction]))
                                <div class="table-responsive tablekit-surface__wrapper">
                                    <table class="table table-hover mb-0 align-middle tablekit-surface">
                                        <thead>
                                        <tr>
                                            <th scope="col" class="tablekit-surface__select"></th>
                                            <th scope="col">Belge</th>
                                            <th scope="col">Durum</th>
                                            <th scope="col">Son Kontrol</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($state[$direction] as $item)
                                            <tr
                                                data-console-row
                                                data-console-step="{{ $direction }}"
                                                data-selection-subject-type="{{ $modelClass }}"
                                                data-selection-subject-id="{{ $item['id'] }}"
                                                data-selection-direction="{{ $direction }}"
                                            >
                                                <td><input class="form-check-input" type="checkbox" value="{{ $item['id'] }}" data-console-checkbox></td>
                                                <td class="fw-semibold">{{ $item['doc_no'] ?? ('#' . $item['id']) }}</td>
                                                <td><span class="badge text-bg-light text-capitalize">{{ str($item['status'] ?? '')->replace('_', ' ')->headline() }}</span></td>
                                                <td class="small text-muted">
                                                    @if(!empty($item['last_check']))
                                                        {{ __('Sonuç: :result', ['result' => strtoupper($item['last_check']['result'])]) }} · {{ $item['last_check']['checked_at'] ?? '' }}
                                                    @else
                                                        {{ __('Kayıt bulunamadı') }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted py-5">
                                    <p class="mb-1 fw-semibold">Bu listede bekleyen kayıt yok</p>
                                    <p class="mb-0 small">Yeni kabul veya sevkiyat oluşturulduğunda otomatik olarak görünecek.</p>
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
                        <form method="post" action="{{ route('admin.consoles.quality.record') }}" data-console-action-form>
                            @csrf
                            <input type="hidden" name="result" value="" data-console-action-input>
                            <input type="hidden" name="notes" value="" data-console-notes-input>
                            <div data-console-selected-inputs></div>

                            <div class="mb-3">
                                <label class="form-label" for="quality-note">Not</label>
                                <textarea class="form-control" id="quality-note" rows="3" placeholder="Kontrol notunuzu girin" data-console-reason></textarea>
                                <div class="form-text">Başarısız kontrollerde kısa açıklama yazın.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-success" type="button" data-console-action="pass">Başarılı</button>
                                <button class="btn btn-outline-danger" type="button" data-console-action="fail">Başarısız</button>
                            </div>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0"><kbd>A</kbd> tümünü seçer, <kbd>Enter</kbd> varsayılan olarak başarılı kaydeder.</p>
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
