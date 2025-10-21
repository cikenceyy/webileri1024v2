@extends('layouts.admin')

@section('title', __('New Invoice'))

@section('module', 'finance')

@section('content')
    <x-ui.page-header :title="__('New Invoice')" />

    <x-ui.card>
        <form method="POST" action="{{ route('admin.finance.invoices.store') }}">
            @include('finance::invoices._form', ['invoice' => null])
            <div class="mt-4 d-flex gap-2">
                <x-ui.button type="submit" variant="primary">{{ __('Save Invoice') }}</x-ui.button>
                <x-ui.button tag="a" :href="route('admin.finance.invoices.index')" variant="secondary">{{ __('Cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
