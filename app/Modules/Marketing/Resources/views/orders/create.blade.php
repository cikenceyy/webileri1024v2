@extends('layouts.admin')

@section('content')
<x-ui-page-header :title="__('New Order')" :back-url="route('admin.marketing.orders.index')" />

<form method="post" action="{{ route('admin.marketing.orders.store') }}" class="card p-4" data-default-price-list="{{ $settingsDefaults['price_list_id'] ?? '' }}" data-default-tax-inclusive="{{ $settingsDefaults['tax_inclusive'] ? '1' : '0' }}" data-default-payment-terms="{{ $settingsDefaults['payment_terms_days'] ?? 0 }}">
    @csrf
    @include('marketing::orders._form', ['order' => new \App\Modules\Marketing\Domain\Models\Order(), 'customers' => $customers, 'contacts' => $contacts, 'settingsDefaults' => $settingsDefaults])
    <div class="mt-4 d-flex gap-2">
        <x-ui-button type="submit">{{ __('Save Order') }}</x-ui-button>
        <a href="{{ route('admin.marketing.orders.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
