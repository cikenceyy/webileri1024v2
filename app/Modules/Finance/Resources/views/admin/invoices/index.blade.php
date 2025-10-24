@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Invoices'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('Sales Invoices') }}</h1>
            <p class="text-muted mb-0">{{ __('Draft, issued and settled invoices for the current company.') }}</p>
        </div>
        @can('create', \App\Modules\Finance\Domain\Models\Invoice::class)
            <a href="{{ route('admin.finance.invoices.create') }}" class="btn btn-primary">{{ __('New Invoice') }}</a>
        @endcan
    </div>

    <form method="get" class="card card-body mb-4 shadow-sm">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label">{{ __('Search') }}</label>
                <input type="text" name="q" id="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Doc no. or customer') }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">{{ __('Status') }}</label>
                <select name="status" id="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach(\App\Modules\Finance\Domain\Models\Invoice::statuses() as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __(Str::headline($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="customer_id" class="form-label">{{ __('Customer') }}</label>
                <select name="customer_id" id="customer_id" class="form-select">
                    <option value="">{{ __('All customers') }}</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? null) == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Draft') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['draft'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Issued') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['issued'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Partially Paid') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['partially_paid'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Paid') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['paid'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>{{ __('Doc No') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Grand Total') }}</th>
                    <th class="text-end">{{ __('Paid') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->doc_no ?? __('(Draft)') }}</td>
                        <td>{{ $invoice->customer?->name }}</td>
                        <td><span class="badge bg-secondary text-uppercase">{{ __(Str::headline($invoice->status)) }}</span></td>
                        <td class="text-end">{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency }}</td>
                        <td class="text-end">{{ number_format($invoice->paid_amount, 2) }} {{ $invoice->currency }}</td>
                        <td>{{ optional($invoice->due_date)?->format('Y-m-d') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">{{ __('No invoices found.') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
    </div>
@endsection
