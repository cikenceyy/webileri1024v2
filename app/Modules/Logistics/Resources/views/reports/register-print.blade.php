@extends('layouts.print')

@section('title', __('Shipment Register'))

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Shipment Register') }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
        <div class="print-meta">
            @if(!empty($filters['date_from']))
                <div><strong>{{ __('From') }}:</strong> {{ $filters['date_from']->format('Y-m-d') }}</div>
            @endif
            @if(!empty($filters['date_to']))
                <div><strong>{{ __('To') }}:</strong> {{ $filters['date_to']->format('Y-m-d') }}</div>
            @endif
            @if(!empty($filters['carrier']))
                <div><strong>{{ __('Carrier') }}:</strong> {{ $filters['carrier'] }}</div>
            @endif
            @if(!empty($filters['status']))
                <div><strong>{{ __('Status') }}:</strong> {{ $statuses[$filters['status']] ?? ucfirst($filters['status']) }}</div>
            @endif
        </div>
    </div>

    <table class="print-table">
        <thead>
        <tr>
            <th>{{ __('Shipment') }}</th>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Carrier') }}</th>
            <th>{{ __('Customer') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($shipments as $shipment)
            <tr>
                <td>{{ $shipment->shipment_no }}</td>
                <td>{{ $shipment->ship_date?->format('d.m.Y') }}</td>
                <td>{{ ucfirst($shipment->status) }}</td>
                <td>{{ $shipment->carrier ?? '—' }}</td>
                <td>{{ $shipment->customer?->name ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">{{ __('Kayıt bulunamadı') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
