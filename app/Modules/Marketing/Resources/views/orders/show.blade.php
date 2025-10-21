@extends('layouts.admin')

@php use Illuminate\Support\Facades\Route; @endphp

@section('content')
<x-ui.page-header :title="$order->order_no" :description="$order->customer?->name">
    <x-slot name="actions">
        <a class="btn btn-icon btn-secondary" href="{{ route('admin.marketing.orders.edit', $order) }}">{{ __('Edit') }}</a>
        <a class="btn btn-icon btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('admin.marketing.orders.print', $order) }}">{{ __('Yazdır') }}</a>
        @if(function_exists('route') && Route::has('admin.finance.invoices.from-order'))
            <x-ui.button variant="primary" href="{{ route('admin.finance.invoices.from-order', $order) }}">{{ __('Fatura Oluştur') }}</x-ui.button>
        @endif
        @if(function_exists('route') && Route::has('admin.logistics.shipments.create'))
            <x-ui.button variant="outline" href="{{ route('admin.logistics.shipments.create', ['order_id' => $order->getKey(), 'customer_id' => $order->customer_id]) }}">{{ __('Sevkiyat Oluştur') }}</x-ui.button>
        @endif
    </x-slot>
</x-ui.page-header>

<x-ui.card>
    <div class="row g-3">
        <div class="col-md-3"><strong>{{ __('Status') }}:</strong> {{ ucfirst($order->status) }}</div>
        <div class="col-md-3"><strong>{{ __('Order Date') }}:</strong> {{ optional($order->order_date)->format('d.m.Y') }}</div>
        <div class="col-md-3"><strong>{{ __('Due Date') }}:</strong> {{ optional($order->due_date)->format('d.m.Y') ?: '—' }}</div>
        <div class="col-md-3"><strong>{{ __('Total') }}:</strong> {{ number_format($order->total_amount, 2) }}</div>
    </div>
</x-ui.card>

<x-ui.card class="mt-4">
    <div class="table-responsive">
        <table class="table">
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
                @foreach($order->lines as $line)
                    <tr>
                        <td>{{ $line->description }}</td>
                        <td class="text-end">{{ number_format($line->qty, 2) }}</td>
                        <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format($line->discount_rate, 2) }}</td>
                        <td class="text-end">{{ number_format($line->tax_rate, 2) }}</td>
                        <td class="text-end">{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-end mt-3">
        <div>{{ __('Subtotal') }}: {{ number_format($order->subtotal, 2) }}</div>
        <div>{{ __('Discount') }}: {{ number_format($order->discount_total, 2) }}</div>
        <div>{{ __('Tax') }}: {{ number_format($order->tax_total, 2) }}</div>
        <div class="fw-semibold">{{ __('Total') }}: {{ number_format($order->total_amount, 2) }}</div>
    </div>
</x-ui.card>
@endsection
