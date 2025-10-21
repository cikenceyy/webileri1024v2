@extends('layouts.admin')

@section('title', __('Receipt Register'))

@section('content')
    <x-ui-page-header :title="__('Receipt Register')">
        <x-slot name="actions">
            <a class="btn btn-icon btn-outline-secondary" href="{{ route('admin.finance.reports.receipts', array_merge(request()->query(), ['print' => 1])) }}" target="_blank" rel="noopener">{{ __('Yazdır') }}</a>
            <a class="btn btn-icon btn-outline-primary" href="{{ route('admin.finance.reports.receipts', array_merge(request()->query(), ['format' => 'csv'])) }}">{{ __('CSV Dışa Aktar') }}</a>
        </x-slot>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" class="row g-2 align-items-end mb-3" data-prevent-double-submit>
            <div class="col-md-3">
                <x-ui-input type="date" name="date_from" :label="__('From')" :value="$filters['date_from'] ?? ''" />
            </div>
            <div class="col-md-3">
                <x-ui-input type="date" name="date_to" :label="__('To')" :value="$filters['date_to'] ?? ''" />
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
            </div>
        </form>

        <div class="table-responsive">
            <x-ui-table class="table-compact">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Receipt') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th class="text-end">{{ __('Allocated') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr>
                            <td>{{ $receipt->receipt_date?->format('d.m.Y') }}</td>
                            <td>{{ $receipt->receipt_no }}</td>
                            <td>{{ $receipt->customer?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($receipt->amount, 2) }} {{ $receipt->currency }}</td>
                            <td class="text-end">{{ number_format($receipt->allocated_total, 2) }} {{ $receipt->currency }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><x-ui-empty title="{{ __('No receipts in range') }}" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui-table>
        </div>

        <div class="mt-3">
            {{ $receipts->links() }}
        </div>
    </x-ui-card>
@endsection
