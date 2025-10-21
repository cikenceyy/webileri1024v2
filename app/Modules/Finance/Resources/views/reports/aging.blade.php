@extends('layouts.admin')

@section('title', __('A/R Aging'))

@section('content')
    <x-ui-page-header :title="__('Accounts Receivable Aging')">
        <x-slot name="actions">
            <a class="btn btn-icon btn-outline-secondary" href="{{ route('admin.finance.reports.aging', ['print' => 1]) }}" target="_blank" rel="noopener">{{ __('Yazdır') }}</a>
            <a class="btn btn-icon btn-outline-primary" href="{{ route('admin.finance.reports.aging', ['format' => 'csv']) }}">{{ __('CSV Dışa Aktar') }}</a>
        </x-slot>
    </x-ui-page-header>

    <div class="row g-3">
        <div class="col-lg-4">
            <x-ui-card>
                <h6 class="fw-semibold mb-3">{{ __('Summary') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li class="d-flex justify-content-between"><span>{{ __('Current') }}</span><span>{{ number_format($buckets['current'], 2) }}</span></li>
                    <li class="d-flex justify-content-between"><span>{{ __('1-30 days') }}</span><span>{{ number_format($buckets['1_30'], 2) }}</span></li>
                    <li class="d-flex justify-content-between"><span>{{ __('31-60 days') }}</span><span>{{ number_format($buckets['31_60'], 2) }}</span></li>
                    <li class="d-flex justify-content-between"><span>{{ __('61-90 days') }}</span><span>{{ number_format($buckets['61_90'], 2) }}</span></li>
                    <li class="d-flex justify-content-between"><span>{{ __('90+ days') }}</span><span>{{ number_format($buckets['over_90'], 2) }}</span></li>
                </ul>
            </x-ui-card>
        </div>
        <div class="col-lg-8">
            <x-ui-card>
                <div class="table-responsive">
                    <x-ui-table class="table-compact">
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
                                    <td colspan="4"><x-ui-empty title="{{ __('All caught up!') }}" /></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui-table>
                </div>
            </x-ui-card>
        </div>
    </div>
@endsection
