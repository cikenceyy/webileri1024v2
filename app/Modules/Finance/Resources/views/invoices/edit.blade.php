@extends('layouts.admin')

@section('title', __('Edit Invoice'))

@section('module', 'finance')

@section('content')
    <x-ui-page-header :title="__('Edit Invoice')" :description="$invoice->invoice_no" />

    <x-ui-card>
        <form method="POST" action="{{ route('admin.finance.invoices.update', $invoice) }}">
            @method('PUT')
            @include('finance::invoices._form', ['invoice' => $invoice])
            <div class="mt-4 d-flex gap-2">
                <x-ui-button type="submit" variant="primary">{{ __('Update Invoice') }}</x-ui-button>
                <x-ui-button tag="a" :href="route('admin.finance.invoices.show', $invoice)" variant="secondary">{{ __('Cancel') }}</x-ui-button>
            </div>
        </form>
    </x-ui-card>
@endsection
