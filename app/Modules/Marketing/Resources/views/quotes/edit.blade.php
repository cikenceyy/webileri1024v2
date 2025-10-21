@extends('layouts.admin')

@section('content')
<x-ui.page-header :title="__('Edit Quote :no', ['no' => $quote->quote_no])" :back-url="route('admin.marketing.quotes.show', $quote)" />

<form method="post" action="{{ route('admin.marketing.quotes.update', $quote) }}" class="card p-4">
    @csrf
    @method('put')
    @include('marketing::quotes._form', ['quote' => $quote, 'customers' => $customers, 'contacts' => $contacts])
    <div class="mt-4 d-flex gap-2">
        <x-ui.button type="submit">{{ __('Update Quote') }}</x-ui.button>
        <a href="{{ route('admin.marketing.quotes.show', $quote) }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
