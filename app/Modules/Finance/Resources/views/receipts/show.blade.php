@extends('layouts.admin')

@section('title', $receipt->receipt_no)

@section('content')
    <x-ui.page-header :title="$receipt->receipt_no" :description="$receipt->customer?->name">
        <x-slot name="actions">
            <x-ui.button tag="a" :href="route('admin.finance.receipts.edit', $receipt)" variant="secondary">{{ __('Edit') }}</x-ui.button>
        </x-slot>
    </x-ui.page-header>

    <div class="row g-3">
        <div class="col-lg-8">
            <x-ui.card>
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Receipt Date') }}</span>
                    <span>{{ $receipt->receipt_date?->format('d.m.Y') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Amount') }}</span>
                    <span>{{ number_format($receipt->amount, 2) }} {{ $receipt->currency }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Allocated Total') }}</span>
                    <span>{{ number_format($receipt->allocated_total, 2) }} {{ $receipt->currency }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ __('Bank Account') }}</span>
                    <span>{{ $receipt->bankAccount?->name ?? '—' }}</span>
                </div>
                <div class="mt-3">
                    <p class="mb-1 text-muted">{{ __('Notes') }}</p>
                    <p class="mb-0">{{ $receipt->notes ?: '—' }}</p>
                </div>
            </x-ui.card>
        </div>
        <div class="col-lg-4">
            <x-ui.card>
                <h6 class="fw-semibold mb-3">{{ __('Allocations') }}</h6>
                <ul class="list-unstyled mb-0">
                    @forelse($receipt->allocations as $allocation)
                        <li class="d-flex justify-content-between border-bottom py-2">
                            <span>{{ $allocation->invoice->invoice_no ?? '—' }}</span>
                            <span>{{ number_format($allocation->amount, 2) }}</span>
                        </li>
                    @empty
                        <li>{{ __('No allocations yet.') }}</li>
                    @endforelse
                </ul>
            </x-ui.card>
        </div>
    </div>
@endsection
