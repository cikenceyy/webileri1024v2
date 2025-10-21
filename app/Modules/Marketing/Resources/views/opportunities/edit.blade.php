@extends('layouts.admin')

@section('content')
<x-ui.page-header :title="__('Edit Opportunity')" :back-url="route('admin.marketing.opportunities.index')" />

<form method="post" action="{{ route('admin.marketing.opportunities.update', $opportunity) }}" class="card p-4">
    @csrf
    @method('put')
    @include('marketing::opportunities._form', ['opportunity' => $opportunity, 'customers' => $customers])
    <div class="mt-4 d-flex gap-2">
        <x-ui.button type="submit">{{ __('Update Opportunity') }}</x-ui.button>
        <a href="{{ route('admin.marketing.opportunities.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
