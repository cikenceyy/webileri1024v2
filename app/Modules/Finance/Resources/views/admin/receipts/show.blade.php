@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Receipt :number', ['number' => $receipt->doc_no]))

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Receipt') }} {{ $receipt->doc_no }}</h1>
            <div class="text-muted">{{ __('Customer: :customer', ['customer' => $receipt->customer?->name]) }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.receipts.index') }}" class="btn btn-outline-secondary">{{ __('Back to list') }}</a>
            @can('apply', $receipt)
                <a href="{{ route('admin.finance.receipts.apply-form', $receipt) }}" class="btn btn-primary">{{ __('Apply to invoices') }}</a>
            @endcan
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('Details') }}</h2>
                    <dl class="row mb-0">
                        <dt class="col-6 text-muted">{{ __('Received At') }}</dt>
                        <dd class="col-6 text-end">{{ optional($receipt->received_at)?->format('Y-m-d') }}</dd>
                        <dt class="col-6 text-muted">{{ __('Amount') }}</dt>
                        <dd class="col-6 text-end fw-semibold">{{ number_format($receipt->amount, 2) }}</dd>
                        <dt class="col-6 text-muted">{{ __('Applied') }}</dt>
                        <dd class="col-6 text-end">{{ number_format($receipt->appliedTotal(), 2) }}</dd>
                        <dt class="col-6 text-muted">{{ __('Available') }}</dt>
                        <dd class="col-6 text-end">{{ number_format($receipt->availableAmount(), 2) }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('Meta') }}</h2>
                    <dl class="row mb-0">
                        <dt class="col-6 text-muted">{{ __('Method') }}</dt>
                        <dd class="col-6 text-end">{{ $receipt->method ?: '—' }}</dd>
                        <dt class="col-6 text-muted">{{ __('Reference') }}</dt>
                        <dd class="col-6 text-end">{{ $receipt->reference ?: '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('Notes') }}</h2>
                    <p class="mb-0">{{ $receipt->notes ?: __('No notes provided.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-3">{{ __('Applied Invoices') }}</h2>
            @if($receipt->applications->isEmpty())
                <p class="text-muted mb-0">{{ __('No invoices are linked yet.') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>{{ __('Invoice') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Amount Applied') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($receipt->applications as $application)
                            <tr>
                                <td><a href="{{ route('admin.finance.invoices.show', $application->invoice) }}">{{ $application->invoice->doc_no }}</a></td>
                                <td>{{ __(Str::headline($application->invoice->status)) }}</td>
                                <td class="text-end">{{ number_format($application->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
