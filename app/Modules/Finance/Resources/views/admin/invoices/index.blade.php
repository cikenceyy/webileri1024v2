@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Invoices'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('Sales Invoices') }}</h1>
            <p class="text-muted mb-0">{{ __('Draft, issued and settled invoices for the current company.') }}</p>
        </div>
        @can('create', \App\Modules\Finance\Domain\Models\Invoice::class)
            <a href="{{ route('admin.finance.invoices.create') }}" class="btn btn-primary">{{ __('New Invoice') }}</a>
        @endcan
    </div>

    <form method="get" class="card card-body mb-4 shadow-sm">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label">{{ __('Search') }}</label>
                <input type="text" name="q" id="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Doc no. or customer') }}">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">{{ __('Status') }}</label>
                <select name="status" id="status" class="form-select">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach(\App\Modules\Finance\Domain\Models\Invoice::statuses() as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __(Str::headline($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="customer_id" class="form-label">{{ __('Customer') }}</label>
                <select name="customer_id" id="customer_id" class="form-select">
                    <option value="">{{ __('All customers') }}</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? null) == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Draft') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['draft'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Issued') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['issued'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Partially Paid') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['partially_paid'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <span class="text-muted text-uppercase small">{{ __('Paid') }}</span>
                    <div class="fs-4 fw-semibold">{{ $metrics['paid'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" :search-placeholder="__('Fatura ara…')" />
            </x-slot>
        </x-table>
    </div>
@endsection
