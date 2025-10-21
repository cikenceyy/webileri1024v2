@extends('layouts.print')

@section('title', __('Teslimat Notu') . ' ' . $shipment->shipment_no)

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Teslimat Notu') }} {{ $shipment->shipment_no }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
        <div class="print-meta">
            <div><strong>{{ __('Sevkiyat Tarihi') }}:</strong> {{ optional($shipment->ship_date)->format('d.m.Y') }}</div>
            <div><strong>{{ __('Müşteri') }}:</strong> {{ $shipment->customer?->name ?? '—' }}</div>
            <div><strong>{{ __('Durum') }}:</strong> {{ ucfirst($shipment->status) }}</div>
            <div><strong>{{ __('Taşıyıcı') }}:</strong> {{ $shipment->carrier ?? '—' }}</div>
            <div><strong>{{ __('Takip No') }}:</strong> {{ $shipment->tracking_no ?? '—' }}</div>
        </div>
    </div>

    @if($shipment->order)
        <p class="mb-3"><strong>{{ __('Sipariş') }}:</strong> {{ $shipment->order->order_no }}</p>
    @endif

    <table class="print-table">
        <thead>
        <tr>
            <th>{{ __('Ürün') }}</th>
            <th class="text-end">{{ __('Miktar') }}</th>
            <th class="text-end">{{ __('Birim') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($shipment->lines as $line)
            <tr>
                <td>{{ $line->description ?? $line->product?->name ?? '—' }}</td>
                <td class="text-end">{{ number_format($line->qty, 3) }}</td>
                <td class="text-end">{{ $line->unit ?? $line->product?->baseUnit?->code ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">{{ __('Sevkiyat satırı bulunamadı.') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @if($shipment->packages && $shipment->packages->isNotEmpty())
        <h2 class="h6 mt-4">{{ __('Paketler') }}</h2>
        <table class="print-table">
            <thead>
            <tr>
                <th>{{ __('Paket No') }}</th>
                <th class="text-end">{{ __('Kilo (kg)') }}</th>
                <th class="text-end">{{ __('Hacim (dm³)') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($shipment->packages as $package)
                <tr>
                    <td>{{ $package->package_no ?? ('PKG-' . $loop->iteration) }}</td>
                    <td class="text-end">{{ number_format((float) $package->weight_kg, 2) }}</td>
                    <td class="text-end">{{ number_format((float) $package->volume_dm3, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
