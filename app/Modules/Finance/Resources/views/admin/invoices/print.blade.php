@php use Illuminate\Support\Str; @endphp
@extends('layouts.print')

@section('title', __('Invoice :number', ['number' => $invoice->doc_no]))

@section('content')
    <div class="mb-4">
        <h1 class="h2 mb-1">{{ __('Invoice') }} {{ $invoice->doc_no }}</h1>
        <div class="text-muted">{{ __('Issued :date', ['date' => optional($invoice->issued_at)?->format('Y-m-d') ?? now()->format('Y-m-d')]) }}</div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <h2 class="h6 text-uppercase text-muted">{{ __('Bill To') }}</h2>
            <p class="mb-0 fw-semibold">{{ $invoice->customer?->name }}</p>
        </div>
        <div class="col-6 text-end">
            <h2 class="h6 text-uppercase text-muted">{{ __('Summary') }}</h2>
            <p class="mb-0">{{ __('Currency: :currency', ['currency' => $invoice->currency]) }}</p>
            <p class="mb-0">{{ __('Payment Terms: :days days', ['days' => $invoice->payment_terms_days]) }}</p>
            <p class="mb-0">{{ __('Due Date: :date', ['date' => optional($invoice->due_date)?->format('Y-m-d') ?? __('TBD')]) }}</p>
        </div>
    </div>

    <table class="table table-sm">
        <thead>
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

    <div class="text-end mt-4">
        <p class="mb-1">{{ __('Subtotal: :amount', ['amount' => number_format($invoice->subtotal, 2) . ' ' . $invoice->currency]) }}</p>
        <p class="mb-1">{{ __('Tax: :amount', ['amount' => number_format($invoice->tax_total, 2) . ' ' . $invoice->currency]) }}</p>
        <h2 class="h4">{{ __('Grand Total: :amount', ['amount' => number_format($invoice->grand_total, 2) . ' ' . $invoice->currency]) }}</h2>
    </div>
@endsection
