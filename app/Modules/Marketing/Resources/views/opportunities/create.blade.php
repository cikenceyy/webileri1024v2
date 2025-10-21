@extends('layouts.admin')

@section('content')
<x-ui.page-header :title="__('New Opportunity')" :back-url="route('admin.marketing.opportunities.index')" />

<form method="post" action="{{ route('admin.marketing.opportunities.store') }}" class="card p-4">
    @csrf
    @include('marketing::opportunities._form', ['opportunity' => new \App\Modules\Marketing\Domain\Models\Opportunity(), 'customers' => $customers])
    <div class="mt-4 d-flex gap-2">
        <x-ui.button type="submit">{{ __('Save Opportunity') }}</x-ui.button>
        <a href="{{ route('admin.marketing.opportunities.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
    </div>
</form>
@endsection
