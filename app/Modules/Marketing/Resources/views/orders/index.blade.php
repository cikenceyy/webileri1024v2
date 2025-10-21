@extends('layouts.admin')

@section('module', 'Marketing')

@section('content')
    <x-ui.page-header :title="__('Orders')" :description="__('Manage customer sales orders')">
        <x-slot name="actions">
            <x-ui.button variant="primary" href="{{ route('admin.marketing.orders.create') }}">{{ __('New Order') }}</x-ui.button>
        </x-slot>
    </x-ui.page-header>

    <x-ui.card class="mb-4" data-crm-filters data-marketing-filters>
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <x-ui.input name="q" :label="__('Search')" :value="request('q')" placeholder="{{ __('Order number or reference') }}" />
            </div>
            <div class="col-md-3">
                <x-ui.select name="status" :label="__('Status')">
                    <option value="">{{ __('All') }}</option>
                    @foreach(['draft','confirmed','shipped','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </x-ui.select>
            </div>
            <div class="col-md-3">
                <x-ui.input name="customer_id" :label="__('Customer ID')" :value="request('customer_id')" placeholder="12345" />
            </div>
            <div class="col-md-2 d-flex gap-2">
                <x-ui.button type="submit" class="flex-grow-1">{{ __('Filter') }}</x-ui.button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.marketing.orders.index') }}">{{ __('Reset') }}</a>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card data-crm-orders data-marketing-orders>
        <x-ui.table dense :searchable="false">
            <thead>
                <tr>
                    <th scope="col">{{ __('Order No') }}</th>
                    <th scope="col">{{ __('Customer') }}</th>
                    <th scope="col">{{ __('Order Date') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col" class="text-end">{{ __('Total') }}</th>
                    <th scope="col" class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr data-crm-order-row data-marketing-order-row data-status="{{ $order->status }}">
                        <td class="align-middle">
                            <a class="fw-semibold" href="{{ route('admin.marketing.orders.show', $order) }}">{{ $order->order_no }}</a>
                            <div class="text-muted small">{{ $order->reference ?? __('No reference') }}</div>
                        </td>
                        <td class="align-middle">{{ $order->customer?->name ?? __('Unknown') }}</td>
                        <td class="align-middle">{{ optional($order->order_date)->format('d.m.Y') }}</td>
                        <td class="align-middle">
                            <x-ui.badge :type="match($order->status) {
                                'confirmed' => 'success',
                                'shipped' => 'info',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            }" soft>
                                {{ ucfirst($order->status) }}
                            </x-ui.badge>
                        </td>
                        <td class="align-middle text-end fw-semibold">
                            {{ number_format($order->total_amount, 2) }} {{ $order->currency }}
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.marketing.orders.edit', $order) }}">{{ __('Edit') }}</a>
                                <form method="post" action="{{ route('admin.marketing.orders.destroy', $order) }}" data-crm-delete-form data-marketing-delete-form data-confirm-message="{{ __('Delete order?') }}">
                                    @csrf
                                    @method('delete')
                                    <x-ui.button type="submit" size="sm" variant="danger">{{ __('Delete') }}</x-ui.button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <x-ui.empty :title="__('No orders found.')" description="{{ __('Adjust your filters or create a new order.') }}">
                                <x-slot name="actions">
                                    <x-ui.button variant="primary" href="{{ route('admin.marketing.orders.create') }}">{{ __('Create order') }}</x-ui.button>
                                </x-slot>
                            </x-ui.empty>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-ui.table>
        <div class="mt-3">{{ $orders->links() }}</div>
    </x-ui.card>
@endsection
