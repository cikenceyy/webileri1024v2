@extends('layouts.admin')

@section('content')
<x-ui-page-header :title="__('Edit Order :no', ['no' => $order->order_no])" :back-url="route('admin.marketing.orders.show', $order)" />

<form method="post" action="{{ route('admin.marketing.orders.update', $order) }}" class="card p-4">
    @csrf
    @method('put')
    @include('marketing::orders._form', ['order' => $order, 'customers' => $customers, 'contacts' => $contacts])
    <div class="mt-4 d-flex gap-2">
        <x-ui-button type="submit">{{ __('Update Order') }}</x-ui-button>
        <a href="{{ route('admin.marketing.orders.show', $order) }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
