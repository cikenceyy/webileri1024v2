@extends('layouts.admin')

@section('title', __('Shipment Register'))

@section('content')
    <x-ui.page-header :title="__('Shipment Register')">
        <x-slot name="actions">
            <a class="btn btn-icon btn-outline-secondary" href="{{ route('admin.logistics.reports.register', array_merge(request()->query(), ['print' => 1])) }}" target="_blank" rel="noopener">{{ __('Yazdır') }}</a>
            <a class="btn btn-icon btn-outline-primary" href="{{ route('admin.logistics.reports.register', array_merge(request()->query(), ['format' => 'csv'])) }}">{{ __('CSV Dışa Aktar') }}</a>
        </x-slot>
    </x-ui.page-header>

    <x-ui.card>
        <form method="GET" class="row g-2 align-items-end mb-3" data-prevent-double-submit>
            <div class="col-md-3">
                <x-ui.input type="date" name="date_from" :label="__('From')" :value="$filters['date_from'] ?? ''" />
            </div>
            <div class="col-md-3">
                <x-ui.input type="date" name="date_to" :label="__('To')" :value="$filters['date_to'] ?? ''" />
            </div>
            <div class="col-md-2">
                <x-ui.input name="carrier" :label="__('Carrier')" :value="$filters['carrier'] ?? ''" />
            </div>
            <div class="col-md-2">
                <x-ui.select name="status" :label="__('Status')" :value="$filters['status'] ?? ''">
                    <option value="">{{ __('All') }}</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
            </div>
        </form>

        <div class="table-responsive">
            <x-ui.table class="table-compact">
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
                            <td colspan="5"><x-ui.empty title="{{ __('No shipments found') }}" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-3">
            {{ $shipments->links() }}
        </div>
    </x-ui.card>
@endsection
