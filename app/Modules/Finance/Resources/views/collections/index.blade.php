@extends('layouts.admin')

@section('title', __('Tahsilat Konsolu'))
@section('module', 'finance')
@section('page', 'collections-console')

@push('page-styles')
    @vite('app/Modules/Finance/Resources/scss/finance.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Finance/Resources/js/finance.js')
@endpush

@section('content')
    <div class="finance-shell" data-finance-screen="collections" data-summary-endpoint="{{ route('admin.finance.collections.show', ['invoice' => '__INVOICE__']) }}" data-lane-endpoint="{{ route('admin.finance.collections.lane', ['invoice' => '__INVOICE__']) }}">
        <header class="finance-shell__section-header">
            <div>
                <h1>{{ __('Tahsilat Konsolu') }}</h1>
                <p class="text-muted">{{ __('Bugün, bu hafta, gecikmiş ve takipteki tahsilatlarınızı kart-first akışta yönetin.') }}</p>
            </div>
            <form method="GET" class="finance-filters" role="search" aria-label="Tahsilat araması">
                <x-ui-input name="q" :value="$filters['q'] ?? ''" :label="__('Ara')" placeholder="{{ __('Müşteri veya fatura no') }}" data-shortcut="/" />
                <x-ui-button type="submit" variant="outline">{{ __('Filtrele') }}</x-ui-button>
            </form>
        </header>

        <div class="finance-kanban" data-finance-kanban role="list">
            @foreach($lanes as $laneKey => $lane)
                <section class="finance-kanban__column" role="region" aria-label="{{ $lane['meta']['label'] }}" data-lane="{{ $laneKey }}">
                    <header class="finance-kanban__header">
                        <h2>{{ $lane['meta']['label'] }}</h2>
                        <span class="finance-kanban__meta" data-lane-currency="{{ config('finance.default_currency') }}">{{ $lane['meta']['count'] }} · {{ number_format($lane['meta']['total'], 2) }} {{ config('finance.default_currency') }}</span>
                        <p class="finance-kanban__hint">{{ $lane['meta']['description'] }}</p>
                    </header>

                    <div class="finance-kanban__list" role="list" data-droppable="{{ $laneKey }}">
                        @forelse($lane['items'] as $item)
                            <article class="finance-card" role="listitem" draggable="true" data-invoice-id="{{ $item['id'] }}" data-current-lane="{{ $item['lane'] }}">
                                <header class="finance-card__header">
                                    <span class="finance-card__title">{{ $item['customer'] }}</span>
                                    <span class="finance-card__badge" data-variant="{{ $item['due_state']['variant'] }}">{{ $item['due_state']['label'] }}</span>
                                </header>
                                <p class="finance-card__subtitle">{{ $item['invoice_no'] }}</p>
                                <p class="finance-card__amount">{{ number_format($item['balance_due'], 2) }} {{ $item['currency'] }}</p>
                                <footer class="finance-card__actions">
                                    <button type="button" class="finance-card__action" data-action="call" title="{{ __('Ara') }}"><i class="bi bi-telephone"></i></button>
                                    <button type="button" class="finance-card__action" data-action="partial" title="{{ __('Kısmi tahsilat') }}"><i class="bi bi-cash-coin"></i></button>
                                    <button type="button" class="finance-card__action" data-action="remind" title="{{ __('Hatırlatma gönder') }}"><i class="bi bi-envelope"></i></button>
                                    <button type="button" class="finance-card__expand" data-action="expand" aria-label="{{ __('Müşteri finans özetini aç') }}"><i class="bi bi-chevron-right"></i></button>
                                </footer>
                            </article>
                        @empty
                            <div class="finance-card finance-card--empty">
                                <p>{{ __('Bu kolon için kayıt yok. Kartları sürükleyip buraya bırakabilirsiniz.') }}</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>

        <aside class="finance-slideover" data-finance-slideover aria-hidden="true">
            <div class="finance-slideover__dialog" role="dialog" aria-modal="true" aria-labelledby="finance-slideover-title">
                <header class="finance-slideover__header">
                    <h2 id="finance-slideover-title">{{ __('Müşteri Finans Özeti') }}</h2>
                    <button type="button" class="finance-slideover__close" data-action="close" aria-label="{{ __('Kapat') }}">
                        <i class="bi bi-x"></i>
                    </button>
                </header>
                <div class="finance-slideover__body" data-slideover-content>
                    <p class="text-muted">{{ __('Kart seçerek müşteri finans özetini görüntüleyin.') }}</p>
                </div>
            </div>
        </aside>
    </div>
@endsection
