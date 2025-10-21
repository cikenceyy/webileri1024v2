@extends('layouts.admin')

@section('title', __('New Receipt'))

@section('content')
    <x-ui.page-header :title="__('New Receipt')" />

    <x-ui.card>
        <form method="POST" action="{{ route('admin.finance.receipts.store') }}">
            @include('finance::receipts._form', ['receipt' => null])
            <div class="mt-4 d-flex gap-2">
                <x-ui.button type="submit" variant="primary">{{ __('Save Receipt') }}</x-ui.button>
                <x-ui.button tag="a" :href="route('admin.finance.receipts.index')" variant="secondary">{{ __('Cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
