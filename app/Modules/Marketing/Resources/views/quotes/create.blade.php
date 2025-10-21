@extends('layouts.admin')

@section('content')
<x-ui.page-header :title="__('New Quote')" :back-url="route('admin.marketing.quotes.index')" />

<form method="post" action="{{ route('admin.marketing.quotes.store') }}" class="card p-4">
    @csrf
    @include('marketing::quotes._form', ['quote' => new \App\Modules\Marketing\Domain\Models\Quote(), 'customers' => $customers, 'contacts' => $contacts])
    <div class="mt-4 d-flex gap-2">
        <x-ui.button type="submit">{{ __('Save Quote') }}</x-ui.button>
        <a href="{{ route('admin.marketing.quotes.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
