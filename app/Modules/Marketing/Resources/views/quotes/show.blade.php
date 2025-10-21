@extends('layouts.admin')

@section('content')
<x-ui.page-header :title="$quote->quote_no" :description="$quote->customer?->name">
    <x-slot name="actions">
        <a class="btn btn-icon btn-secondary" href="{{ route('admin.marketing.quotes.edit', $quote) }}">{{ __('Edit') }}</a>
        <a class="btn btn-icon btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('admin.marketing.quotes.print', $quote) }}">{{ __('Yazdır') }}</a>
    </x-slot>
</x-ui.page-header>

<x-ui.card>
    <div class="row g-3">
        <div class="col-md-3"><strong>{{ __('Status') }}:</strong> {{ ucfirst($quote->status) }}</div>
        <div class="col-md-3"><strong>{{ __('Date') }}:</strong> {{ optional($quote->date)->format('d.m.Y') }}</div>
        <div class="col-md-3"><strong>{{ __('Currency') }}:</strong> {{ $quote->currency }}</div>
        <div class="col-md-3"><strong>{{ __('Grand Total') }}:</strong> {{ number_format($quote->grand_total, 2) }}</div>
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
                @foreach($quote->lines as $line)
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
        <div>{{ __('Subtotal') }}: {{ number_format($quote->subtotal, 2) }}</div>
        <div>{{ __('Discount') }}: {{ number_format($quote->discount_total, 2) }}</div>
        <div>{{ __('Tax') }}: {{ number_format($quote->tax_total, 2) }}</div>
        <div class="fw-semibold">{{ __('Grand Total') }}: {{ number_format($quote->grand_total, 2) }}</div>
    </div>
</x-ui.card>
@endsection
