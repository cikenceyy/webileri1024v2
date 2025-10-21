@extends('layouts.admin')

@section('content')
<x-ui-page-header :title="__('Import Customers')" :back-url="route('admin.marketing.customers.index')" />

<x-ui-card>
    <form method="post" action="{{ route('admin.marketing.customers.import') }}" enctype="multipart/form-data" class="d-flex flex-column gap-3">
        @csrf
        <x-ui-file name="file" :label="__('CSV File')" accept=".csv,.txt" required />
        <p class="text-muted small mb-0">{{ __('Expected headers: code,name,email,phone,status') }}</p>
        <x-ui-button type="submit">{{ __('Import') }}</x-ui-button>
    </form>
</x-ui-card>
@endsection
