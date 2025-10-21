@extends('layouts.print')

@section('title', __('Receipt Register'))

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Receipt Register') }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
        <div class="print-meta">
            @if(!empty($filters['date_from']))
                <div><strong>{{ __('From') }}:</strong> {{ $filters['date_from'] }}</div>
            @endif
            @if(!empty($filters['date_to']))
                <div><strong>{{ __('To') }}:</strong> {{ $filters['date_to'] }}</div>
            @endif
        </div>
    </div>

    <table class="print-table">
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
                <td colspan="5">{{ __('Kayıt bulunamadı') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
