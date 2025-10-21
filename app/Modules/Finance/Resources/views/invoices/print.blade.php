@extends('layouts.print')

@section('title', __('Fatura') . ' ' . $invoice->invoice_no)

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Fatura') }} {{ $invoice->invoice_no }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
        <div class="print-meta">
            <div><strong>{{ __('Düzenlenme Tarihi') }}:</strong> {{ optional($invoice->issue_date)->format('d.m.Y') }}</div>
            <div><strong>{{ __('Vade') }}:</strong> {{ optional($invoice->due_date)->format('d.m.Y') }}</div>
            <div><strong>{{ __('Müşteri') }}:</strong> {{ $invoice->customer?->name ?? '—' }}</div>
            <div><strong>{{ __('Para Birimi') }}:</strong> {{ $invoice->currency }}</div>
        </div>
    </div>

    <table class="print-table">
        <thead>
        <tr>
            <th>{{ __('Açıklama') }}</th>
            <th class="text-end">{{ __('Miktar') }}</th>
            <th class="text-end">{{ __('Birim Fiyat') }}</th>
            <th class="text-end">{{ __('İndirim %') }}</th>
            <th class="text-end">{{ __('KDV %') }}</th>
            <th class="text-end">{{ __('Satır Toplamı') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td class="text-end">{{ number_format($line->qty, 3) }}</td>
                <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($line->discount_rate, 2) }}</td>
                <td class="text-end">{{ number_format($line->tax_rate, 2) }}</td>
                <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="print-summary">
        <tr>
            <td>{{ __('Ara Toplam') }}</td>
            <td>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('İndirim') }}</td>
            <td>{{ number_format($invoice->discount_total, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('Vergi') }}</td>
            <td>{{ number_format($invoice->tax_total, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('Kargo') }}</td>
            <td>{{ number_format($invoice->shipping_total, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('Genel Toplam') }}</td>
            <td>{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('Kalan') }}</td>
            <td>{{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</td>
        </tr>
    </table>
@endsection
