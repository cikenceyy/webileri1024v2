@extends('layouts.admin')

@section('title', __('Fatura Stüdyosu'))
@section('module', 'finance')
@section('page', 'invoice-studio')

@push('page-styles')
    @vite('app/Modules/Finance/Resources/scss/finance.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Finance/Resources/js/finance.js')
@endpush

@section('content')
    <div class="finance-shell" data-finance-screen="invoices" data-summary-endpoint="{{ config('features.finance.collections_console') ? route('admin.finance.collections.show', ['invoice' => '__INVOICE__']) : '' }}">
        <header class="finance-shell__section-header">
            <div>
                <h1>{{ __('Fatura Stüdyosu') }}</h1>
                <p class="text-muted">{{ __('Siparişten satır çekin, vergileri yönetin ve tek ekranda tahsilat akışını tamamlayın.') }}</p>
            </div>
            <div class="finance-shell__quick-actions" role="group">
                @can('create', App\Modules\Finance\Domain\Models\Invoice::class)
                    <x-ui-button tag="a" :href="route('admin.finance.invoices.create')" variant="primary" data-shortcut="N">{{ __('Yeni Fatura (N)') }}</x-ui-button>
                @endcan
                @if (config('features.finance.collections_console'))
                    <x-ui-button tag="a" :href="route('admin.finance.collections.index')" variant="outline">{{ __('Tahsilat Konsolu') }}</x-ui-button>
                @endif
            </div>
        </header>

        <section class="finance-metric-cards" role="list">
            <article class="finance-metric" role="listitem">
                <h2>{{ __('Taslaklar') }}</h2>
                <p class="finance-metric__value">{{ $metrics['draft'] }}</p>
                <span class="finance-metric__hint">{{ __('Yayınlanmayı bekleyen faturalar') }}</span>
            </article>
            <article class="finance-metric" role="listitem">
                <h2>{{ __('Yayınlanan') }}</h2>
                <p class="finance-metric__value">{{ $metrics['published'] }}</p>
                <span class="finance-metric__hint">{{ __('Müşteriyle paylaşılan faturalar') }}</span>
            </article>
            <article class="finance-metric" role="listitem">
                <h2>{{ __('Gecikmiş') }}</h2>
                <p class="finance-metric__value text-danger">{{ $metrics['overdue'] }}</p>
                <span class="finance-metric__hint">{{ __('Vadesi geçen yayınlanmış faturalar') }}</span>
            </article>
            <article class="finance-metric" role="listitem">
                <h2>{{ __('Toplam Açık Bakiye') }}</h2>
                <p class="finance-metric__value">{{ number_format($metrics['total_due'], 2) }} {{ config('finance.default_currency') }}</p>
                <span class="finance-metric__hint">{{ __('Tahsil edilmeyi bekleyen tutar') }}</span>
            </article>
        </section>

        <section class="finance-filters" aria-label="Fatura filtreleri">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <x-ui-input name="q" :value="$filters['q'] ?? ''" :label="__('Arama')" placeholder="{{ __('Fatura no. veya müşteri') }}" data-shortcut="/" />
                </div>
                <div class="col-12 col-md-3">
                    <x-ui-select name="customer_id" :label="__('Müşteri')" :options="$customers->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()" :placeholder="__('Tümü')" :value="$filters['customer_id'] ?? ''" />
                </div>
                <div class="col-12 col-md-3">
                    <x-ui-input name="status" :label="__('Durum')" :value="$filters['status'] ?? ''" placeholder="{{ __('örn. draft') }}" />
                </div>
                <div class="col-12 col-md-2">
                    <x-ui-button type="submit" class="w-100">{{ __('Uygula') }}</x-ui-button>
                </div>
            </form>
            <div class="finance-filter-presets" role="list">
                <button type="button" class="finance-filter-presets__item" data-preset="today">{{ __('Bugün') }}</button>
                <button type="button" class="finance-filter-presets__item" data-preset="week">{{ __('Bu Hafta') }}</button>
                <button type="button" class="finance-filter-presets__item" data-preset="published">{{ __('Yayınlanan') }}</button>
            </div>
        </section>

        <div class="finance-table-wrapper">
            <table class="finance-table" data-finance-table>
                <thead>
                    <tr>
                        <th>{{ __('Fatura #') }}</th>
                        <th>{{ __('Müşteri') }}</th>
                        <th>{{ __('Düzenleme') }}</th>
                        <th>{{ __('Vade') }}</th>
                        <th class="text-end">{{ __('Toplam') }}</th>
                        <th class="text-end">{{ __('Bakiye') }}</th>
                        <th>{{ __('Durum') }}</th>
                        <th class="text-end">{{ __('İşlemler') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr data-invoice-row data-invoice-id="{{ $invoice->id }}">
                            <td><a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="finance-table__link">{{ $invoice->invoice_no }}</a></td>
                            <td>{{ $invoice->customer?->name ?? '—' }}</td>
                            <td>{{ $invoice->issue_date?->format('d.m.Y') }}</td>
                            <td>{{ $invoice->due_date?->format('d.m.Y') ?? '—' }}</td>
                            <td class="text-end">{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency }}</td>
                            <td class="text-end">{{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</td>
                            <td><x-ui-badge type="info">{{ ucfirst($invoice->status) }}</x-ui-badge></td>
                            <td class="text-end">
                                <div class="finance-table__actions">
                                    <x-ui-button tag="a" size="sm" :href="route('admin.finance.invoices.edit', $invoice)" variant="ghost">{{ __('Düzenle') }}</x-ui-button>
                                    <x-ui-button tag="a" size="sm" :href="route('admin.finance.invoices.print', $invoice)" variant="ghost">{{ __('Yazdır') }}</x-ui-button>
                                    <button type="button" class="finance-table__action" data-action="quick-pay" aria-label="{{ __('Hızlı tahsilat') }}" data-invoice-id="{{ $invoice->id }}">
                                        <i class="bi bi-wallet2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-ui-empty title="{{ __('Fatura bulunamadı') }}" description="{{ __('Filtreleri temizleyin veya yeni bir fatura oluşturun.') }}">
                                    @can('create', App\Modules\Finance\Domain\Models\Invoice::class)
                                        <x-slot name="actions">
                                            <x-ui-button tag="a" :href="route('admin.finance.invoices.create')" variant="primary">{{ __('Yeni Fatura Oluştur') }}</x-ui-button>
                                        </x-slot>
                                    @endcan
                                </x-ui-empty>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $invoices->links() }}
        </div>

        <aside class="finance-slideover" data-finance-slideover aria-hidden="true">
            <div class="finance-slideover__dialog" role="dialog" aria-modal="true" aria-labelledby="invoice-slideover-title">
                <header class="finance-slideover__header">
                    <h2 id="invoice-slideover-title">{{ __('Hızlı Tahsilat') }}</h2>
                    <button type="button" class="finance-slideover__close" data-action="close" aria-label="{{ __('Kapat') }}">
                        <i class="bi bi-x"></i>
                    </button>
                </header>
                <div class="finance-slideover__body" data-slideover-content>
                    <p class="text-muted">{{ __('Bir satır seçerek tahsilat panelini açın.') }}</p>
                </div>
            </div>
        </aside>
    </div>
@endsection
