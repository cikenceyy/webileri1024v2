{{--
    Amaç: Gösterge paneli sayfasını TR dilinde ve ortak içerik iskeletiyle sunmak.
    İlişkiler: PROMPT-1 — TR Dil Birliği, PROMPT-2 — Blade İskeleti.
    Notlar: Başlık, toolbar ve bölümler ortak şema ile yeniden düzenlendi.
--}}
@extends('layouts.admin')

@section('title', 'Gösterge Paneli')
@section('module', 'Çekirdek')
@section('page', 'Gösterge Paneli')

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $isAccountantOnly = ($roleFlags['accountant'] ?? false) && ! ($roleFlags['owner'] ?? false) && ! ($roleFlags['super_admin'] ?? false);
    $formatDate = static function ($value): string {
        if (! $value) {
            return '—';
        }

        return $value->timezone(config('app.timezone'))->format('d.m.Y H:i');
    };
    $formatStatus = static fn ($status) => Str::headline((string) $status);
    $driveIcon = static function (?string $extension): string {
        return match (Str::lower($extension)) {
            'pdf' => 'bi bi-file-earmark-pdf',
            'doc', 'docx' => 'bi bi-file-earmark-word',
            'xls', 'xlsx', 'csv' => 'bi bi-file-earmark-spreadsheet',
            'ppt', 'pptx' => 'bi bi-file-earmark-slides',
            'jpg', 'jpeg', 'png', 'webp', 'svg' => 'bi bi-image',
            'mp4' => 'bi bi-camera-reels',
            'zip' => 'bi bi-file-earmark-zip',
            default => 'bi bi-file-earmark',
        };
    };
@endphp

@section('content')
    @php
        $selectedRange = request('range', 'today');
        $rangeLabel = match ($selectedRange) {
            '7d' => 'Son 7 Gün',
            '30d' => 'Son 30 Gün',
            default => 'Bugün',
        };
    @endphp

    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Gösterge Paneli"
            description="Rolünüze göre kritik iş akışlarını buradan izleyin."
        />

        <div class="d-flex flex-column gap-4 mt-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3" role="toolbar" aria-label="Gösterge araçları">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge text-bg-light">Seçilen aralık: {{ $rangeLabel }}</span>
                    <span class="text-muted small">Saat dilimi: {{ config('app.timezone') }}</span>
                </div>
                <div class="btn-group" role="group" aria-label="Tarih filtresi">
                    <a href="{{ request()->fullUrlWithQuery(['range' => 'today']) }}" class="btn btn-outline-secondary btn-sm @if($selectedRange === 'today') active @endif">Bugün</a>
                    <a href="{{ request()->fullUrlWithQuery(['range' => '7d']) }}" class="btn btn-outline-secondary btn-sm @if($selectedRange === '7d') active @endif">Son 7 Gün</a>
                    <a href="{{ request()->fullUrlWithQuery(['range' => '30d']) }}" class="btn btn-outline-secondary btn-sm @if($selectedRange === '30d') active @endif">Son 30 Gün</a>
                </div>
            </div>

            <x-ui-card title="Gösterge Özeti" subtitle="Bugünkü performans ve kısa özet">
                <div class="row g-3">
                    @foreach($kpiCards as $card)
                        <div class="col-sm-6 col-xl-3">
                            <div class="card shadow-sm h-100 border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <p class="text-muted text-uppercase fw-semibold small mb-1">{{ $card['label'] }}</p>
                                            <div class="d-flex align-items-baseline gap-2">
                                                <span class="display-6 fw-bold">{{ number_format($card['today']) }}</span>
                                                <span class="badge text-bg-soft-primary">Bugün</span>
                                            </div>
                                        </div>
                                        <span class="badge bg-primary-subtle text-primary-emphasis rounded-circle p-3">
                                            <i class="{{ $card['icon'] }} fs-5" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <div class="mt-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-calendar-week text-muted" aria-hidden="true"></i>
                                        <span class="text-muted small">Son 7 gün: {{ number_format($card['week']) }} kayıt</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui-card>

            <div class="row g-4">
                <div class="col-xl-8 d-flex flex-column gap-4">
                    <x-ui-card title="Hızlı Aksiyonlar" subtitle="Sıklıkla kullanılan operasyon adımlarını tek tıkla başlatın">
                        <div class="row g-2" role="group" aria-label="Hızlı aksiyon düğmeleri">
                            @forelse($quickActions as $action)
                                @php
                                    $url = Route::has($action['route']) ? route($action['route']) : '#';
                                    $isDisabled = $action['disabled'] ?? false;
                                @endphp
                                <div class="col-12 col-md-6">
                                    <a href="{{ $isDisabled ? '#' : $url }}"
                                       class="btn {{ $isDisabled ? 'btn-outline-secondary disabled' : 'btn-outline-primary' }} w-100 d-flex align-items-center justify-content-between"
                                       @if($isDisabled) aria-disabled="true" tabindex="-1" data-bs-toggle="tooltip" data-bs-title="Yetkiniz yok. Operasyon sorumlusu ile iletişime geçin." @endif>
                                        <span><i class="{{ $action['icon'] }} me-2" aria-hidden="true"></i>{{ $action['label'] }}</span>
                                        <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            @empty
                                <p class="text-muted mb-0">Yetkinize uygun hızlı aksiyon bulunmuyor.</p>
                            @endforelse
                        </div>
                    </x-ui-card>

                    <x-ui-card title="Uyarılar" subtitle="Operasyon notları ve kritik duyurular">
                        @forelse($alerts as $alert)
                            <div class="alert alert-warning d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3" role="alert" data-ui="alert">
                                <div>
                                    <h3 class="h6 mb-1">{{ $alert['title'] }}</h3>
                                    <p class="mb-2 mb-lg-0">{{ $alert['description'] }}</p>
                                    @if(!empty($alert['items']))
                                        <ul class="list-inline mb-0 small text-muted">
                                            @foreach($alert['items'] as $item)
                                                <li class="list-inline-item">#{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                @if(!empty($alert['action']))
                                    <a class="btn btn-sm btn-outline-warning" href="{{ $alert['action'] }}">Closeout’a git</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">Şu an aksiyon gerektiren kritik bir durum yok.</p>
                        @endforelse
                    </x-ui-card>
                </div>

                <div class="col-xl-4">
                    <x-ui-card title="Drive Hızlı Erişim" subtitle="Son yüklenen dosyalar">
                        <ul class="list-group list-group-flush">
                            @forelse($recentMedia as $media)
                                <li class="list-group-item p-0">
                                    <a href="{{ route('admin.drive.media.download', $media) }}" class="d-flex align-items-center justify-content-between gap-3 px-3 py-2 text-decoration-none text-body">
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge rounded-pill text-bg-secondary"><i class="{{ $driveIcon($media->ext) }}" aria-hidden="true"></i></span>
                                            <div>
                                                <p class="mb-0 fw-semibold text-truncate" style="max-width: 220px">{{ $media->original_name }}</p>
                                                <span class="text-muted small">{{ $media->created_at?->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        <span class="badge text-bg-light text-uppercase">{{ Str::upper($media->ext ?? 'N/A') }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">Henüz dosya yüklenmemiş.</li>
                            @endforelse
                        </ul>
                    </x-ui-card>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <x-ui-card title="Son Faturalar" subtitle="Gelir kalemleri">
                        <ul class="list-unstyled mb-0">
                            @forelse($recentInvoices as $invoice)
                                <li class="d-flex justify-content-between align-items-start py-2 border-bottom border-light-subtle">
                                    <div>
                                        @if(! $isAccountantOnly)
                                            <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="fw-semibold d-block">{{ $invoice->doc_no ?? 'Taslak' }}</a>
                                        @else
                                            <span class="fw-semibold d-block">{{ $invoice->doc_no ?? 'Taslak' }}</span>
                                        @endif
                                        <span class="text-muted small">{{ $formatDate($invoice->issued_at ?? $invoice->created_at) }}</span>
                                        <span class="text-muted small d-block">{{ $isAccountantOnly ? 'Gizli müşteri' : ($invoice->customer->name ?? '—') }}</span>
                                    </div>
                                    <span class="badge text-bg-soft-primary">{{ $formatStatus($invoice->status) }}</span>
                                </li>
                            @empty
                                <li class="text-muted">Kayıt bulunamadı.</li>
                            @endforelse
                        </ul>
                    </x-ui-card>
                </div>
                <div class="col-lg-4">
                    <x-ui-card title="Son Sevkiyatlar" subtitle="Lojistik hareketleri">
                        <ul class="list-unstyled mb-0">
                            @forelse($recentShipments as $shipment)
                                <li class="d-flex justify-content-between align-items-start py-2 border-bottom border-light-subtle">
                                    <div>
                                        @if(! $isAccountantOnly)
                                            <a href="{{ route('admin.logistics.shipments.show', $shipment) }}" class="fw-semibold d-block">{{ $shipment->doc_no ?? sprintf('No: %s', $shipment->id) }}</a>
                                        @else
                                            <span class="fw-semibold d-block">{{ $shipment->doc_no ?? sprintf('No: %s', $shipment->id) }}</span>
                                        @endif
                                        <span class="text-muted small">{{ $formatDate($shipment->shipped_at ?? $shipment->created_at) }}</span>
                                        <span class="text-muted small d-block">{{ $isAccountantOnly ? 'Gizli müşteri' : ($shipment->customer->name ?? '—') }}</span>
                                    </div>
                                    <span class="badge text-bg-soft-success">{{ $formatStatus($shipment->status) }}</span>
                                </li>
                            @empty
                                <li class="text-muted">Kayıt bulunamadı.</li>
                            @endforelse
                        </ul>
                    </x-ui-card>
                </div>
                <div class="col-lg-4">
                    <x-ui-card title="Son GRN Kayıtları" subtitle="Depo girişleri">
                        <ul class="list-unstyled mb-0">
                            @forelse($recentGoodsReceipts as $receipt)
                                <li class="d-flex justify-content-between align-items-start py-2 border-bottom border-light-subtle">
                                    <div>
                                        <a href="{{ route('admin.logistics.receipts.show', $receipt) }}" class="fw-semibold d-block">{{ $receipt->doc_no ?? sprintf('No: %s', $receipt->id) }}</a>
                                        <span class="text-muted small">{{ $formatDate($receipt->received_at ?? $receipt->created_at) }}</span>
                                        <span class="text-muted small d-block">{{ $receipt->warehouse->name ?? 'Depo bilgisi yok' }}</span>
                                    </div>
                                    <span class="badge text-bg-soft-info">{{ $formatStatus($receipt->status) }}</span>
                                </li>
                            @empty
                                <li class="text-muted">Kayıt bulunamadı.</li>
                            @endforelse
                        </ul>
                    </x-ui-card>
                </div>
            </div>
        </div>
    </x-ui-content>
@endsection