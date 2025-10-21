@extends('layouts.admin')

@section('title', __('Edit Bank Account'))

@section('content')
    <x-ui.page-header :title="__('Edit Bank Account')" :description="$account->name" />

    <x-ui.card>
        <form method="POST" action="{{ route('admin.finance.bank-accounts.update', $account) }}">
            @method('PUT')
            @include('finance::banks._form', ['account' => $account])
            <div class="mt-4 d-flex gap-2">
                <x-ui.button type="submit" variant="primary">{{ __('Update Account') }}</x-ui.button>
                <x-ui.button tag="a" :href="route('admin.finance.bank-accounts.index')" variant="secondary">{{ __('Cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
