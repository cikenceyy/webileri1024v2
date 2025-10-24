@extends('layouts.admin')

@section('title', __('Kontrol Merkezi'))
@section('module', 'finance')
@section('page', 'control-center')

@push('page-styles')
    @vite('app/Modules/Finance/Resources/scss/finance.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Finance/Resources/js/finance.js')
@endpush

@section('content')
    <div class="finance-shell" data-finance-screen="home">
        <div class="finance-shell__grid">
            <section class="finance-shell__overview" aria-labelledby="finance-home-metrics">
                <header class="finance-shell__section-header">
                    <div>
                        <h1 id="finance-home-metrics">{{ __('Günlük Finans Kontrol Merkezi') }}</h1>
                        <p class="text-muted">{{ __('Bugün kimden ne alacağınızı, kime ne ödeyeceğinizi ve kasada ne olduğunu tek bakışta görün.') }}</p>
                    </div>
                    <div class="finance-shell__quick-actions" role="group" aria-label="Hızlı işlemler">
                        @can('create', App\Modules\Finance\Domain\Models\Invoice::class)
                            <x-ui-button tag="a" :href="route('admin.finance.invoices.create')" variant="primary" data-shortcut="N">{{ __('Fatura Kes (N)') }}</x-ui-button>
                        @endcan
                        @can('create', App\Modules\Finance\Domain\Models\Receipt::class)
                            <x-ui-button tag="a" :href="route('admin.finance.receipts.create')" variant="success" data-shortcut="R">{{ __('Tahsilat Al (R)') }}</x-ui-button>
                        @endcan
                        @if (config('features.finance.collections_console'))
                            <x-ui-button tag="a" :href="route('admin.finance.collections.index')" variant="outline" data-shortcut="G">{{ __('Ödemeleri Yönet (G)') }}</x-ui-button>
                        @endif
                    </div>
                </header>

                <div class="finance-metric-cards" role="list">
                    <article class="finance-metric" role="listitem">
                        <h2>{{ __('Bugün Vadesi Gelenler') }}</h2>
                        <p class="finance-metric__value">{{ number_format($summary['today_total'], 2) }} {{ config('finance.default_currency') }}</p>
                        <span class="finance-metric__hint">{{ trans_choice(':count açık fatura', $summary['today_count'], ['count' => $summary['today_count']]) }}</span>
                    </article>
                    <article class="finance-metric" role="listitem">
                        <h2>{{ __('Bu Hafta Vadesi Gelenler') }}</h2>
                        <p class="finance-metric__value">{{ number_format($summary['week_total'], 2) }} {{ config('finance.default_currency') }}</p>
                        <span class="finance-metric__hint">{{ trans_choice(':count açık fatura', $summary['week_count'], ['count' => $summary['week_count']]) }}</span>
                    </article>
                    <article class="finance-metric" role="listitem">
                        <h2>{{ __('Gecikmiş Tahsilatlar') }}</h2>
                        <p class="finance-metric__value text-danger">{{ number_format($summary['overdue_total'], 2) }} {{ config('finance.default_currency') }}</p>
                        <span class="finance-metric__hint">{{ trans_choice(':count geciken fatura', $summary['overdue_count'], ['count' => $summary['overdue_count']]) }}</span>
                    </article>
                    <article class="finance-metric" role="listitem">
                        <h2>{{ __('Nakit Pozisyonu') }}</h2>
                        <p class="finance-metric__value">{{ number_format($summary['cash_position']['net'], 2) }} {{ config('finance.default_currency') }}</p>
                        <span class="finance-metric__hint">
                            @if($summary['cash_position']['last_movement'])
                                {{ __('Son hareket: :date', ['date' => $summary['cash_position']['last_movement']->format('d.m.Y')]) }}
                            @else
                                {{ __('Hareket kaydı bulunamadı.') }}
                            @endif
                        </span>
                    </article>
                </div>
            </section>

            <section class="finance-shell__kanban" aria-labelledby="finance-home-upcoming">
                <header class="finance-shell__section-header">
                    <div>
                        <h2 id="finance-home-upcoming">{{ __('Vadesi Yaklaşanlar') }}</h2>
                        <p class="text-muted">{{ __('Bugün ve hafta içinde tahsil edilmesi gereken bakiyeler') }}</p>
                    </div>
                    @if (config('features.finance.collections_console'))
                        <x-ui-button tag="a" :href="route('admin.finance.collections.index')" variant="link">{{ __('Tahsilat Konsolu’na git') }}</x-ui-button>
                    @endif
                </header>

                <div class="finance-lists" data-finance-scrollable>
                    <div class="finance-list">
                        <h3>{{ __('Bugün') }}</h3>
                        <ul>
                            @forelse($dueToday as $invoice)
                                <li>
                                    <span class="finance-list__primary">{{ $invoice->customer?->name ?? '—' }}</span>
                                    <span class="finance-list__meta">{{ $invoice->invoice_no }} · {{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</span>
                                </li>
                            @empty
                                <li class="finance-list__empty">{{ __('Bugün vadesi olan tahsilat yok.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="finance-list">
                        <h3>{{ __('Bu Hafta') }}</h3>
                        <ul>
                            @forelse($dueThisWeek as $invoice)
                                <li>
                                    <span class="finance-list__primary">{{ $invoice->customer?->name ?? '—' }}</span>
                                    <span class="finance-list__meta">{{ $invoice->due_date?->format('d.m') }} · {{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</span>
                                </li>
                            @empty
                                <li class="finance-list__empty">{{ __('Hafta içinde vadesi gelen kayıt yok.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="finance-list">
                        <h3>{{ __('Gecikmiş') }}</h3>
                        <ul>
                            @forelse($overdue as $invoice)
                                <li>
                                    <span class="finance-list__primary">{{ $invoice->customer?->name ?? '—' }}</span>
                                    <span class="finance-list__meta text-danger">{{ $invoice->due_date?->format('d.m') }} · {{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</span>
                                </li>
                            @empty
                                <li class="finance-list__empty">{{ __('Gecikmiş tahsilat yok. Harika!') }}</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </section>

            <section class="finance-shell__activity" aria-labelledby="finance-home-activity">
                <header class="finance-shell__section-header">
                    <div>
                        <h2 id="finance-home-activity">{{ __('Son Aktiviteler') }}</h2>
                        <p class="text-muted">{{ __('Faturalar ve tahsilatlardan oluşan güncel hareketler') }}</p>
                    </div>
                </header>

                <ul class="finance-activity">
                    @forelse($recentActivities as $activity)
                        <li class="finance-activity__item">
                            <div class="finance-activity__icon" aria-hidden="true">
                                <i class="bi {{ $activity['type'] === 'invoice' ? 'bi-receipt' : 'bi-coin' }}"></i>
                            </div>
                            <div class="finance-activity__body">
                                <strong>{{ $activity['label'] }}</strong>
                                <span class="finance-activity__meta">{{ number_format($activity['amount'], 2) }} {{ $activity['currency'] }} · {{ $activity['at']->diffForHumans() }}</span>
                            </div>
                            <div class="finance-activity__actions">
                                <x-ui-button tag="a" :href="$activity['url']" size="sm" variant="ghost">{{ __('Görüntüle') }}</x-ui-button>
                            </div>
                        </li>
                    @empty
                        <li class="finance-activity__item finance-activity__item--empty">
                            <p>{{ __('Henüz kayıtlı hareket bulunmuyor. Yeni bir fatura oluşturarak başlayabilirsiniz.') }}</p>
                        </li>
                    @endforelse
                </ul>
            </section>

            <aside class="finance-shell__roles" aria-label="Rol açıklamaları">
                <x-ui-card>
                    <h2>{{ __('Yetki Rollerinin Özeti') }}</h2>
                    <ul class="finance-roles">
                        <li><strong>{{ __('Stajyer') }}</strong> · {{ __('Sadece görüntüleme, veri girişi yok.') }}</li>
                        <li><strong>{{ __('Muhasebe') }}</strong> · {{ __('Fatura oluşturur, tahsilat kaydeder, banka hareketi girer.') }}</li>
                        <li><strong>{{ __('Patron') }}</strong> · {{ __('Tüm finans panellerini tam yetkiyle yönetir.') }}</li>
                        <li><strong>{{ __('Biz (Super)') }}</strong> · {{ __('Sistem destek ekibi, tüm kayıtları görebilir ve düzenleyebilir.') }}</li>
                    </ul>
                </x-ui-card>
            </aside>
        </div>
    </div>
@endsection
