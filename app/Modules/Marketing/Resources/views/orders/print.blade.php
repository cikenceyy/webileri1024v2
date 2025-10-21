@extends('layouts.print')

@section('title', __('Sipariş') . ' ' . $order->order_no)

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Sipariş') }} {{ $order->order_no }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
        <div class="print-meta">
            <div><strong>{{ __('Sipariş Tarihi') }}:</strong> {{ optional($order->order_date)->format('d.m.Y') }}</div>
            <div><strong>{{ __('Teslim Tarihi') }}:</strong> {{ optional($order->due_date)->format('d.m.Y') ?: '—' }}</div>
            <div><strong>{{ __('Müşteri') }}:</strong> {{ $order->customer?->name ?? '—' }}</div>
            <div><strong>{{ __('Para Birimi') }}:</strong> {{ $order->currency }}</div>
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
        @foreach($order->lines as $line)
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
            <td>{{ number_format($order->subtotal, 2) }} {{ $order->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('İndirim') }}</td>
            <td>{{ number_format($order->discount_total, 2) }} {{ $order->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('Vergi') }}</td>
            <td>{{ number_format($order->tax_total, 2) }} {{ $order->currency }}</td>
        </tr>
        <tr>
            <td>{{ __('Genel Toplam') }}</td>
            <td>{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</td>
        </tr>
    </table>
@endsection
