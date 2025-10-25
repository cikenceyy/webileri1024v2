@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Invoices'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Satış Faturaları') }}</h1>
            <p class="text-muted mb-0">{{ __('Taslak, kesilmiş ve kapatılmış faturaları filtreleyin.') }}</p>
        </div>
        @can('create', \App\Modules\Finance\Domain\Models\Invoice::class)
            <a href="{{ route('admin.finance.invoices.create') }}" class="btn btn-primary">{{ __('Yeni Fatura') }}</a>
        @endcan
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Taslak') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['draft'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Kesildi') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['issued'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Kısmi Ödendi') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['partially_paid'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Ödendi') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['paid'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <x-table
        :mode="$mode"
        :dataset="$tableDataset"
        :columns="$tableColumns"
        :filters="$tableFilters"
        :page-size-options="$pageSizeOptions"
        :default-page-size="$pageSize"
        search-placeholder="{{ __('Fatura no veya müşteri ara') }}"
        :search-value="$filters['q'] ?? ''"
        :paginator="$paginatorHtml"
    >
        @if($mode === 'server' && $invoices)
            @forelse($invoices as $invoice)
                @php
                    $docNo = $invoice->doc_no ?? __('Taslak');
                    $statusLabel = Str::headline($invoice->status);
                    $statusClass = match ($invoice->status) {
                        \App\Modules\Finance\Domain\Models\Invoice::STATUS_ISSUED => 'text-bg-primary',
                        \App\Modules\Finance\Domain\Models\Invoice::STATUS_PARTIALLY_PAID => 'text-bg-warning',
                        \App\Modules\Finance\Domain\Models\Invoice::STATUS_PAID => 'text-bg-success',
                        \App\Modules\Finance\Domain\Models\Invoice::STATUS_CANCELLED => 'text-bg-danger',
                        default => 'text-bg-secondary',
                    };
                @endphp
                <tr>
                    <td><a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="fw-semibold">{{ $docNo }}</a></td>
                    <td>{{ $invoice->customer?->name ?? '—' }}</td>
                    <td><span class="badge {{ $statusClass }}">{{ __($statusLabel) }}</span></td>
                    <td class="text-end">{{ number_format((float) $invoice->grand_total, 2) }} {{ $invoice->currency }}</td>
                    <td class="text-end">{{ number_format((float) $invoice->paid_amount, 2) }} {{ $invoice->currency }}</td>
                    <td>{{ optional($invoice->due_date)?->format('Y-m-d') ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>{{ __('Aç') }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">{{ __('Kayıt bulunamadı.') }}</td>
                </tr>
            @endforelse
        @endif
    </x-table>
@endsection
