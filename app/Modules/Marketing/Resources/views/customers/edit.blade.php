@extends('layouts.admin')

@section('content')
<x-ui-page-header :title="__('Edit Customer')" :back-url="route('admin.marketing.customers.show', $customer)" />

<form method="post" action="{{ route('admin.marketing.customers.update', $customer) }}" class="card p-4">
    @csrf
    @method('put')
    @include('marketing::customers._form', ['customer' => $customer])

    <div class="mt-4 d-flex gap-2">
        <x-ui-button type="submit">{{ __('Save Changes') }}</x-ui-button>
        <a href="{{ route('admin.marketing.customers.show', $customer) }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
