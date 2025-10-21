@extends('layouts.print')

@section('title', __('Teklif') . ' ' . $quote->quote_no)

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Teklif') }} {{ $quote->quote_no }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
        <div class="print-meta">
            <div><strong>{{ __('Tarih') }}:</strong> {{ optional($quote->date)->format('d.m.Y') }}</div>
            <div><strong>{{ __('Müşteri') }}:</strong> {{ $quote->customer?->name ?? '—' }}</div>
            <div><strong>{{ __('Para Birimi') }}:</strong> {{ $quote->currency }}</div>
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
        @foreach($quote->lines as $line)
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
            <td>{{ number_format($quote->subtotal, 2) }} {{ $quote->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('İndirim') }}</td>
            <td>{{ number_format($quote->discount_total, 2) }} {{ $quote->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('Vergi') }}</td>
            <td>{{ number_format($quote->tax_total, 2) }} {{ $quote->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('Genel Toplam') }}</td>
            <td>{{ number_format($quote->grand_total, 2) }} {{ $quote->currency }}</td>
        </tr>
    </table>
@endsection
