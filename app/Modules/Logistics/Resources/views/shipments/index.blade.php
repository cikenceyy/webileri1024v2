@extends('layouts.admin')

@section('title', 'Sevkiyatlar')

@section('module', 'logistics')

@section('content')
<x-ui-page-header title="Sevkiyatlar" description="Gönderim süreçlerinizi yönetin">
    <x-slot name="actions">
        @can('create', \App\Modules\Logistics\Domain\Models\Shipment::class)
            <x-ui-button variant="primary" href="{{ route('admin.logistics.shipments.create') }}">
                Yeni Sevkiyat
            </x-ui-button>
        @endcan
    </x-slot>
</x-ui-page-header>

@if(session('status'))
    <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
@endif

@php
    $filters = $filters ?? [];
    $sort = $sort ?? 'ship_date';
    $dir = $direction ?? 'desc';
    $baseQuery = array_filter([
        'q' => $filters['q'] ?? null,
        'status' => $filters['status'] ?? null,
        'carrier' => $filters['carrier'] ?? null,
        'customer_id' => $filters['customer_id'] ?? null,
        'order_id' => $filters['order_id'] ?? null,
        'date_from' => $filters['date_from'] ?? null,
        'date_to' => $filters['date_to'] ?? null,
    ], fn ($value) => $value !== null && $value !== '');
    $sortUrl = function (string $column) use ($baseQuery, $sort, $dir) {
        $direction = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';

        return route('admin.logistics.shipments.index', array_merge($baseQuery, [
            'sort' => $column,
            'dir' => $direction,
        ]));
    };
    $statusLabels = [
        'draft' => 'Taslak',
        'preparing' => 'Hazırlanıyor',
        'in_transit' => 'Yolda',
        'delivered' => 'Teslim Edildi',
        'cancelled' => 'İptal',
    ];
@endphp

<x-ui-card class="mb-4" data-logistics-filters>
    <form method="GET" action="{{ route('admin.logistics.shipments.index') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <x-ui-input
                name="q"
                label="Ara"
                :value="$filters['q'] ?? ''"
                placeholder="Sevkiyat veya takip no"
            />
        </div>
        <div class="col-md-3">
            <x-ui-select name="status" label="Durum">
                @php($statusValue = $filters['status'] ?? '')
                <option value="">Tümü</option>
                <option value="draft" @selected($statusValue === 'draft')>Taslak</option>
                <option value="preparing" @selected($statusValue === 'preparing')>Hazırlanıyor</option>
                <option value="in_transit" @selected($statusValue === 'in_transit')>Yolda</option>
                <option value="delivered" @selected($statusValue === 'delivered')>Teslim Edildi</option>
                <option value="cancelled" @selected($statusValue === 'cancelled')>İptal</option>
            </x-ui-select>
        </div>
        <div class="col-md-3">
            <x-ui-input
                name="carrier"
                label="Kargo Firması"
                :value="$filters['carrier'] ?? ''"
                placeholder="Aras, Yurtiçi..."
            />
        </div>
        <div class="col-md-3">
            <x-ui-select name="customer_id" label="Müşteri">
                <option value="">Tümü</option>
                @foreach($customerOptions as $option)
                    <option value="{{ $option['value'] }}" @selected((int) ($filters['customer_id'] ?? 0) === (int) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-ui-select>
        </div>
        <div class="col-md-3">
            <x-ui-select name="order_id" label="Sipariş">
                <option value="">Tümü</option>
                @foreach($orderOptions as $option)
                    <option value="{{ $option['value'] }}" @selected((int) ($filters['order_id'] ?? 0) === (int) $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </x-ui-select>
        </div>
        <div class="col-md-3">
            <x-ui-input
                type="date"
                name="date_from"
                label="Başlangıç Tarihi"
                :value="$filters['date_from'] ?? ''"
            />
        </div>
        <div class="col-md-3">
            <x-ui-input
                type="date"
                name="date_to"
                label="Bitiş Tarihi"
                :value="$filters['date_to'] ?? ''"
            />
        </div>
        <div class="col-md-3 d-flex gap-2">
            <x-ui-button type="submit" class="flex-grow-1">Filtrele</x-ui-button>
            <a class="btn btn-outline-secondary" href="{{ route('admin.logistics.shipments.index') }}">Sıfırla</a>
        </div>
    </form>
</x-ui-card>

@if($shipments->count())
    <x-ui-card data-logistics-table>
        <x-ui-table dense>
            <thead>
                <tr>
                    <th scope="col"><a href="{{ $sortUrl('shipment_no') }}" class="table-sort {{ $sort === 'shipment_no' ? 'active' : '' }}">No @if($sort === 'shipment_no')<span aria-hidden="true">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                    <th scope="col"><a href="{{ $sortUrl('ship_date') }}" class="table-sort {{ $sort === 'ship_date' ? 'active' : '' }}">Sevk Tarihi @if($sort === 'ship_date')<span aria-hidden="true">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                    <th scope="col">Müşteri</th>
                    <th scope="col">Sipariş</th>
                    <th scope="col"><a href="{{ $sortUrl('carrier') }}" class="table-sort {{ $sort === 'carrier' ? 'active' : '' }}">Kargo @if($sort === 'carrier')<span aria-hidden="true">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                    <th scope="col">Takip No</th>
                    <th scope="col"><a href="{{ $sortUrl('status') }}" class="table-sort {{ $sort === 'status' ? 'active' : '' }}">Durum @if($sort === 'status')<span aria-hidden="true">{{ $dir === 'asc' ? '↑' : '↓' }}</span>@endif</a></th>
                    <th scope="col" class="text-end">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shipments as $shipment)
                    <tr data-logistics-row data-status="{{ $shipment->status }}">
                        <td class="align-middle">
                            @can('update', $shipment)
                                <a href="{{ route('admin.logistics.shipments.edit', $shipment) }}" class="fw-semibold text-decoration-none">
                                    {{ $shipment->shipment_no }}
                                </a>
                            @else
                                <span class="fw-semibold">{{ $shipment->shipment_no }}</span>
                            @endcan
                        </td>
                        <td class="align-middle">{{ optional($shipment->ship_date)->format('d.m.Y') }}</td>
                        <td class="align-middle">{{ $shipment->customer?->name ?? '—' }}</td>
                        <td class="align-middle">{{ $shipment->order?->order_no ?? '—' }}</td>
                        <td class="align-middle">{{ $shipment->carrier ?? '—' }}</td>
                        <td class="align-middle">{{ $shipment->tracking_no ?? '—' }}</td>
                        <td class="align-middle">
                            <x-ui-badge
                                :type="match($shipment->status) {
                                    'delivered' => 'success',
                                    'in_transit' => 'info',
                                    'preparing' => 'primary',
                                    'cancelled' => 'danger',
                                    default => 'secondary',
                                }"
                                soft
                                data-logistics-status="{{ $shipment->status }}"
                            >
                                {{ $statusLabels[$shipment->status] ?? ucfirst($shipment->status) }}
                            </x-ui-badge>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.logistics.shipments.show', $shipment) }}" class="btn btn-sm btn-outline-secondary">Görüntüle</a>
                                @can('update', $shipment)
                                    <a href="{{ route('admin.logistics.shipments.edit', $shipment) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                                @endcan
                                @can('delete', $shipment)
                                    <form
                                        method="POST"
                                        action="{{ route('admin.logistics.shipments.destroy', $shipment) }}"
                                        data-logistics-delete
                                        data-confirm-message="Bu sevkiyatı silmek istediğinize emin misiniz?"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <x-ui-button type="submit" size="sm" variant="danger">Sil</x-ui-button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui-table>
    </x-ui-card>

    <div class="mt-4">
        {{ $shipments->links() }}
    </div>
@else
    <x-ui-empty title="Sevkiyat bulunamadı" description="İlk sevkiyat kaydınızı oluşturarak başlayın.">
        @can('create', \App\Modules\Logistics\Domain\Models\Shipment::class)
            <x-slot name="actions">
                <x-ui-button variant="primary" href="{{ route('admin.logistics.shipments.create') }}">Sevkiyat Oluştur</x-ui-button>
            </x-slot>
        @endcan
    </x-ui-empty>
@endif
@endsection
