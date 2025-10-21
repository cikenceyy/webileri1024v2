@extends('layouts.admin')

@section('title', __('Edit Receipt'))

@section('content')
    <x-ui.page-header :title="__('Edit Receipt')" :description="$receipt->receipt_no" />

    <x-ui.card>
        <form method="POST" action="{{ route('admin.finance.receipts.update', $receipt) }}">
            @method('PUT')
            @include('finance::receipts._form', ['receipt' => $receipt])
            <div class="mt-4 d-flex gap-2">
                <x-ui.button type="submit" variant="primary">{{ __('Update Receipt') }}</x-ui.button>
                <x-ui.button tag="a" :href="route('admin.finance.receipts.show', $receipt)" variant="secondary">{{ __('Cancel') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
@endsection
