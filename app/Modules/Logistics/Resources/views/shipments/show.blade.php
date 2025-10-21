@extends('layouts.admin')

@section('module', 'logistics')

@section('content')
    <x-ui-page-header :title="$shipment->shipment_no" :description="$shipment->customer?->name">
        <x-slot name="actions">
            <a class="btn btn-icon btn-secondary" href="{{ route('admin.logistics.shipments.edit', $shipment) }}">{{ __('Düzenle') }}</a>
            <a class="btn btn-icon btn-outline-secondary" target="_blank" rel="noopener" href="{{ route('admin.logistics.shipments.print', $shipment) }}">{{ __('Teslimat Notu') }}</a>
            @if($shipment->order_id)
                <a class="btn btn-icon btn-outline-primary" href="{{ route('admin.marketing.orders.show', $shipment->order_id) }}">{{ __('Siparişe Git') }}</a>
            @endif
            @if(!empty($invoice))
                <a class="btn btn-icon btn-primary" href="{{ route('admin.finance.invoices.show', $invoice) }}">{{ __('Faturaya Git') }}</a>
            @endif
        </x-slot>
    </x-ui-page-header>

    <div class="row g-3">
        <div class="col-lg-6">
            <x-ui-card>
                <h6 class="fw-semibold mb-3">{{ __('Shipment Details') }}</h6>
                <div class="mb-2"><strong>{{ __('Status') }}:</strong> {{ ucfirst($shipment->status) }}</div>
                <div class="mb-2"><strong>{{ __('Ship Date') }}:</strong> {{ optional($shipment->ship_date)->format('d.m.Y') }}</div>
                <div class="mb-2"><strong>{{ __('Carrier') }}:</strong> {{ $shipment->carrier ?? '—' }}</div>
                <div class="mb-2"><strong>{{ __('Tracking No') }}:</strong> {{ $shipment->tracking_no ?? '—' }}</div>
                <div class="mb-2"><strong>{{ __('Shipping Cost') }}:</strong> {{ number_format($shipment->shipping_cost ?? 0, 2) }} {{ $shipment->order?->currency ?? 'TRY' }}</div>
                <div class="mb-2"><strong>{{ __('Packages') }}:</strong> {{ $shipment->package_count ?? '—' }}</div>
                <div class="mb-2"><strong>{{ __('Notes') }}:</strong> {{ $shipment->notes ?? '—' }}</div>
            </x-ui-card>
        </div>
        <div class="col-lg-6">
            <x-ui-card>
                <h6 class="fw-semibold mb-3">{{ __('Customer') }}</h6>
                <div class="mb-2"><strong>{{ __('Name') }}:</strong> {{ $shipment->customer?->name ?? '—' }}</div>
                <div class="mb-2"><strong>{{ __('Order') }}:</strong> {{ $shipment->order?->order_no ?? '—' }}</div>
                <div class="mb-2"><strong>{{ __('Status Timeline') }}:</strong></div>
                <ul class="list-unstyled ms-3">
                    <li>{{ __('Created') }}: {{ optional($shipment->created_at)->format('d.m.Y H:i') }}</li>
                    <li>{{ __('Shipped') }}: {{ optional($shipment->shipped_at)->format('d.m.Y H:i') ?? '—' }}</li>
                    <li>{{ __('Delivered') }}: {{ optional($shipment->delivered_at)->format('d.m.Y H:i') ?? '—' }}</li>
                </ul>
            </x-ui-card>
        </div>
    </div>
@endsection
