@extends('layouts.admin')

@section('title', __('New Invoice'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('New Invoice') }}</h1>
            <p class="text-muted mb-0">{{ __('Prepare a draft invoice before issuing it to the customer.') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.finance.invoices.store') }}">
        @csrf
        @include('finance::admin.invoices._form')
        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.finance.invoices.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Save Draft') }}</button>
        </div>
    </form>
@endsection
