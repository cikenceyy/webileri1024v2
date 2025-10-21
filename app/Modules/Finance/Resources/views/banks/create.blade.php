@extends('layouts.admin')

@section('title', __('Add Bank Account'))

@section('content')
    <x-ui.page-header :title="__('Add Bank Account')" />

    <x-ui.card>
        <form method="POST" action="{{ route('admin.finance.bank-accounts.store') }}">
            @include('finance::banks._form', ['account' => null])
            <div class="mt-4 d-flex gap-2">
                <x-ui.button type="submit" variant="primary">{{ __('Save Account') }}</x-ui.button>
                <x-ui.button tag="a" :href="route('admin.finance.bank-accounts.index')" variant="secondary">{{ __('Cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
