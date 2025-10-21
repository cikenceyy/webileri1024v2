@extends('layouts.print')

@section('title', __('A/R Aging'))

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Accounts Receivable Aging') }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
    </div>

    <h2 class="h6 mt-3">{{ __('Özet') }}</h2>
    <table class="print-table">
        <tbody>
        <tr><td>{{ __('Current') }}</td><td class="text-end">{{ number_format($buckets['current'], 2) }}</td></tr>
        <tr><td>{{ __('1-30 days') }}</td><td class="text-end">{{ number_format($buckets['1_30'], 2) }}</td></tr>
        <tr><td>{{ __('31-60 days') }}</td><td class="text-end">{{ number_format($buckets['31_60'], 2) }}</td></tr>
        <tr><td>{{ __('61-90 days') }}</td><td class="text-end">{{ number_format($buckets['61_90'], 2) }}</td></tr>
        <tr><td>{{ __('90+ days') }}</td><td class="text-end">{{ number_format($buckets['over_90'], 2) }}</td></tr>
        </tbody>
    </table>

    <h2 class="h6 mt-4">{{ __('Detay') }}</h2>
    <table class="print-table">
        <thead>
        <tr>
            <th>{{ __('Invoice') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Due Date') }}</th>
            <th class="text-end">{{ __('Balance Due') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_no }}</td>
                <td>{{ $invoice->customer?->name ?? '—' }}</td>
                <td>{{ $invoice->due_date?->format('d.m.Y') ?? '—' }}</td>
                <td class="text-end">{{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">{{ __('Kayıt bulunamadı') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
