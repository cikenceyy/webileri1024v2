@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Invoice :number', ['number' => $invoice->doc_no ?? __('Draft')]))

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $invoice->doc_no ?? __('Draft Invoice') }}</h1>
            <div class="text-muted">{{ __('Customer: :customer', ['customer' => $invoice->customer?->name ?? __('Unknown')]) }}</div>
            <div class="mt-2">
                <span class="badge bg-primary text-uppercase">{{ __(Str::headline($invoice->status)) }}</span>
                @if($invoice->due_date)
                    <span class="badge bg-light text-dark">{{ __('Due :date', ['date' => $invoice->due_date->format('Y-m-d')]) }}</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.invoices.index') }}" class="btn btn-outline-secondary">{{ __('Back to list') }}</a>
            @if($invoice->isDraft())
                @can('update', $invoice)
                    <a href="{{ route('admin.finance.invoices.edit', $invoice) }}" class="btn btn-outline-primary">{{ __('Edit Draft') }}</a>
                @endcan
                @can('issue', $invoice)
                    <form method="post" action="{{ route('admin.finance.invoices.issue', $invoice) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary" onclick="return confirm('{{ __('Issue this invoice?') }}');">{{ __('Issue Invoice') }}</button>
                    </form>
                @endcan
            @else
                @can('print', $invoice)
                    <a href="{{ route('admin.finance.invoices.print', $invoice) }}" class="btn btn-outline-primary" target="_blank">{{ __('Print') }}</a>
                @endcan
                @can('cancel', $invoice)
                    <form method="post" action="{{ route('admin.finance.invoices.cancel', $invoice) }}" class="d-inline" onsubmit="return confirm('{{ __('Cancel this invoice?') }}');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">{{ __('Cancel Invoice') }}</button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('Amounts') }}</h2>
                    <dl class="row mb-0">
                        <dt class="col-6 text-muted">{{ __('Subtotal') }}</dt>
                        <dd class="col-6 text-end">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</dd>
                        <dt class="col-6 text-muted">{{ __('Tax') }}</dt>
                        <dd class="col-6 text-end">{{ number_format($invoice->tax_total, 2) }} {{ $invoice->currency }}</dd>
                        <dt class="col-6 text-muted">{{ __('Grand Total') }}</dt>
                        <dd class="col-6 text-end fw-semibold">{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency }}</dd>
                        <dt class="col-6 text-muted">{{ __('Paid') }}</dt>
                        <dd class="col-6 text-end">{{ number_format($invoice->paid_amount, 2) }} {{ $invoice->currency }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('Meta') }}</h2>
                    <dl class="row mb-0">
                        <dt class="col-6 text-muted">{{ __('Status') }}</dt>
                        <dd class="col-6 text-end">{{ __(Str::headline($invoice->status)) }}</dd>
                        <dt class="col-6 text-muted">{{ __('Issued At') }}</dt>
                        <dd class="col-6 text-end">{{ optional($invoice->issued_at)?->format('Y-m-d') ?? '—' }}</dd>
                        <dt class="col-6 text-muted">{{ __('Payment Terms') }}</dt>
                        <dd class="col-6 text-end">{{ $invoice->payment_terms_days }} {{ __('days') }}</dd>
                        <dt class="col-6 text-muted">{{ __('Tax Inclusive') }}</dt>
                        <dd class="col-6 text-end">{{ $invoice->tax_inclusive ? __('Yes') : __('No') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('Notes') }}</h2>
                    <p class="mb-0">{{ $invoice->notes ?: __('No notes provided.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">{{ __('Line Items') }}</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th class="text-end">{{ __('Qty') }}</th>
                        <th class="text-end">{{ __('Unit Price') }}</th>
                        <th class="text-end">{{ __('Discount %') }}</th>
                        <th class="text-end">{{ __('Tax %') }}</th>
                        <th class="text-end">{{ __('Line Total') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($invoice->lines as $line)
                        <tr>
                            <td>{{ $line->description }}</td>
                            <td class="text-end">{{ number_format($line->qty, 3) }} {{ $line->uom }}</td>
                            <td class="text-end">{{ number_format($line->unit_price, 4) }}</td>
                            <td class="text-end">{{ number_format($line->discount_pct, 2) }}</td>
                            <td class="text-end">{{ number_format($line->tax_rate ?? 0, 2) }}</td>
                            <td class="text-end">{{ number_format($line->line_total, 2) }} {{ $invoice->currency }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">{{ __('Receipt Applications') }}</h2>
                <a href="{{ route('admin.finance.receipts.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Manage receipts') }}</a>
            </div>
            @if($invoice->applications->isEmpty())
                <p class="text-muted mb-0">{{ __('No receipts have been applied to this invoice yet.') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>{{ __('Receipt No') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-end">{{ __('Amount Applied') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($invoice->applications as $application)
                            <tr>
                                <td><a href="{{ route('admin.finance.receipts.show', $application->receipt) }}">{{ $application->receipt->doc_no }}</a></td>
                                <td>{{ optional($application->receipt->received_at)?->format('Y-m-d') }}</td>
                                <td class="text-end">{{ number_format($application->amount, 2) }} {{ $invoice->currency }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
