@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Edit Invoice'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('Edit Invoice') }}</h1>
            <p class="text-muted mb-0">{{ __('Update draft invoice details before issuing.') }}</p>
        </div>
        <div class="text-muted">{{ __('Status: :status', ['status' => __(Str::headline($invoice->status))]) }}</div>
    </div>

    <form method="post" action="{{ route('admin.finance.invoices.update', $invoice) }}">
        @csrf
        @method('PUT')
        @include('finance::admin.invoices._form')
        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Update Draft') }}</button>
        </div>
    </form>
@endsection
