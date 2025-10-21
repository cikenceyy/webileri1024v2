@extends('layouts.admin')

@section('content')
<x-ui-page-header :title="$customer->name" :description="$customer->code">
    <x-slot:name>actions</x-slot:name>
    <x-ui-button variant="secondary" href="{{ route('admin.marketing.customers.edit', $customer) }}">{{ __('Edit') }}</x-ui-button>
</x-ui-page-header>

<div class="row g-4">
    <div class="col-lg-4">
        <x-ui-card>
            <div class="d-flex flex-column gap-2">
                <div><strong>{{ __('Email') }}:</strong> {{ $customer->email ?: '—' }}</div>
                <div><strong>{{ __('Phone') }}:</strong> {{ $customer->phone ?: '—' }}</div>
                <div><strong>{{ __('Status') }}:</strong> <x-ui-badge :type="$customer->status === 'active' ? 'success' : 'secondary'">{{ ucfirst($customer->status) }}</x-ui-badge></div>
                <div><strong>{{ __('Payment Terms') }}:</strong> {{ $customer->payment_terms ?: '—' }}</div>
                <div><strong>{{ __('Credit Limit') }}:</strong> {{ number_format($customer->credit_limit, 2) }}</div>
                <div><strong>{{ __('Balance') }}:</strong> {{ number_format($customer->balance, 2) }}</div>
                <div><strong>{{ __('Default Address') }}:</strong>
                    <div class="text-muted">{{ $customer->address ?: '—' }}</div>
                </div>
            </div>
        </x-ui-card>
    </div>
    <div class="col-lg-8">
        <x-ui-card>
            <x-ui-tabs id="customer-tabs">
                <x-slot name="tabs">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-contacts">{{ __('Contacts') }}</button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-addresses">{{ __('Addresses') }}</button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-quotes">{{ __('Quotes') }}</button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-orders">{{ __('Orders') }}</button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity">{{ __('Activity') }}</button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-notes">{{ __('Notes') }}</button>
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-attachments">{{ __('Attachments') }}</button>
                </x-slot>
                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="tab-contacts">
                        @include('marketing::contacts.index', ['customer' => $customer])
                    </div>
                    <div class="tab-pane fade" id="tab-addresses">
                        @include('marketing::addresses.index', ['customer' => $customer])
                    </div>
                    <div class="tab-pane fade" id="tab-quotes">
                        <ul class="list-group list-group-flush">
                            @forelse($customer->quotes as $quote)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.marketing.quotes.show', $quote) }}">{{ $quote->quote_no }}</a>
                                    <span class="text-muted">{{ $quote->date->format('d.m.Y') }} · {{ number_format($quote->grand_total, 2) }} {{ $quote->currency }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">{{ __('No quotes yet.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="tab-orders">
                        <ul class="list-group list-group-flush">
                            @forelse($customer->orders as $order)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.marketing.orders.show', $order) }}">{{ $order->order_no }}</a>
                                    <span class="text-muted">{{ $order->order_date->format('d.m.Y') }} · {{ number_format($order->total_amount, 2) }} {{ $order->currency }}</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted">{{ __('No orders yet.') }}</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="tab-pane fade" id="tab-activity">
                        @include('marketing::activities.index', ['activities' => $activities, 'embedded' => true])
                    </div>
                    <div class="tab-pane fade" id="tab-notes">
                        @include('marketing::notes._list', ['notes' => $notes, 'relatedType' => get_class($customer), 'relatedId' => $customer->id])
                    </div>
                    <div class="tab-pane fade" id="tab-attachments">
                        @include('marketing::attachments._list', ['attachments' => $attachments, 'relatedType' => get_class($customer), 'relatedId' => $customer->id])
                    </div>
                </div>
            </x-ui-tabs>
        </x-ui-card>
    </div>
</div>
@endsection
