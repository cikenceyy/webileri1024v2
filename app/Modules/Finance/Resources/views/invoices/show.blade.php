@extends('layouts.admin')

@section('title', $invoice->invoice_no)

@section('content')
    <x-ui.page-header :title="$invoice->invoice_no" :description="$invoice->customer?->name">
        <x-slot name="actions">
            <a class="btn btn-icon btn-secondary" href="{{ route('admin.finance.invoices.edit', $invoice) }}">{{ __('Edit') }}</a>
            <a class="btn btn-icon btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('admin.finance.invoices.print', $invoice) }}">{{ __('Yazdır') }}</a>
            <a class="btn btn-icon btn-primary" href="{{ route('admin.finance.allocations.index', ['invoice_id' => $invoice->getKey()]) }}">{{ __('Tahsilat ile Eşle') }}</a>
        </x-slot>
    </x-ui.page-header>

    <div class="row g-3">
        <div class="col-lg-8">
            <x-ui.card>
                <h6 class="fw-semibold mb-3">{{ __('Line Items') }}</h6>
                <div class="table-responsive">
                    <x-ui.table class="table-compact">
                        <thead>
                            <tr>
                                <th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Qty') }}</th>
                                <th class="text-end">{{ __('Unit Price') }}</th>
                                <th class="text-end">{{ __('Discount') }}</th>
                                <th class="text-end">{{ __('Tax') }}</th>
                                <th class="text-end">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->lines as $line)
                                <tr>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ number_format($line->qty, 3) }}</td>
                                    <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                                    <td class="text-end">{{ $line->discount_rate }}%</td>
                                    <td class="text-end">{{ $line->tax_rate }}%</td>
                                    <td class="text-end">{{ number_format($line->line_total, 2) }} {{ $invoice->currency }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                </div>
            </x-ui.card>

            <x-ui.card class="mt-3">
                <h6 class="fw-semibold mb-3">{{ __('Allocations') }}</h6>
                <div class="table-responsive">
                    <x-ui.table class="table-compact">
                        <thead>
                            <tr>
                                <th>{{ __('Receipt') }}</th>
                                <th>{{ __('Allocated At') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->allocations as $allocation)
                                <tr>
                                    <td>{{ $allocation->receipt->receipt_no ?? '—' }}</td>
                                    <td>{{ $allocation->allocated_at?->format('d.m.Y H:i') }}</td>
                                    <td class="text-end">{{ number_format($allocation->amount, 2) }} {{ $invoice->currency }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3"><x-ui.empty title="{{ __('No allocations yet') }}" /></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui.table>
                </div>
            </x-ui.card>
        </div>
        <div class="col-lg-4">
            <x-ui.card>
                <div class="d-flex justify-content-between">
                    <span>{{ __('Subtotal') }}</span>
                    <span>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>{{ __('Discounts') }}</span>
                    <span>{{ number_format($invoice->discount_total, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>{{ __('Tax') }}</span>
                    <span>{{ number_format($invoice->tax_total, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>{{ __('Shipping') }}</span>
                    <span>{{ number_format($invoice->shipping_total, 2) }} {{ $invoice->currency }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-semibold">
                    <span>{{ __('Total') }}</span>
                    <span>{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="d-flex justify-content-between text-danger fw-semibold">
                    <span>{{ __('Balance Due') }}</span>
                    <span>{{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</span>
                </div>
                <div class="mt-3">
                    <p class="mb-1 text-muted">{{ __('Status') }}</p>
                    <x-ui.badge type="info">{{ ucfirst($invoice->status) }}</x-ui.badge>
                </div>
            </x-ui.card>
        </div>
    </div>
@endsection
