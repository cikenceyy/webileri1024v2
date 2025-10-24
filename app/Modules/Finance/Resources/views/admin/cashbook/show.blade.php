@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Cashbook Entry'))

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __(Str::headline($entry->direction)) }} {{ __('Entry') }}</h1>
            <div class="text-muted">{{ __('Occurred at :date', ['date' => optional($entry->occurred_at)?->format('Y-m-d')]) }}</div>
        </div>
        <a href="{{ route('admin.finance.cashbook.index') }}" class="btn btn-outline-secondary">{{ __('Back to list') }}</a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('Summary') }}</h2>
                    <dl class="row mb-0">
                        <dt class="col-5 text-muted">{{ __('Direction') }}</dt>
                        <dd class="col-7 text-end">{{ __(Str::headline($entry->direction)) }}</dd>
                        <dt class="col-5 text-muted">{{ __('Amount') }}</dt>
                        <dd class="col-7 text-end fw-semibold">{{ number_format($entry->amount, 2) }}</dd>
                        <dt class="col-5 text-muted">{{ __('Account') }}</dt>
                        <dd class="col-7 text-end">{{ $entry->account }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">{{ __('Reference & Notes') }}</h2>
                    <p class="mb-1"><strong>{{ __('Reference') }}:</strong> {{ $entry->reference_type ? $entry->reference_type . ' #' . $entry->reference_id : __('None') }}</p>
                    <p class="mb-0"><strong>{{ __('Notes') }}:</strong> {{ $entry->notes ?: __('No notes provided.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
