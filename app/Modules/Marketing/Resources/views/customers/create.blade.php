@extends('layouts.admin')

@section('content')
<x-ui.page-header :title="__('New Customer')" :back-url="route('admin.marketing.customers.index')" />

<form method="post" action="{{ route('admin.marketing.customers.store') }}" class="card p-4">
    @csrf
    @include('marketing::customers._form', ['customer' => new \App\Modules\Marketing\Domain\Models\Customer(['status' => 'active'])])

    <div class="mt-4 d-flex gap-2">
        <x-ui.button type="submit">{{ __('Save Customer') }}</x-ui.button>
        <a href="{{ route('admin.marketing.customers.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
