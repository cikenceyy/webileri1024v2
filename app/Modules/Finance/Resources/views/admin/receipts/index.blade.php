@extends('layouts.admin')

@section('title', __('Receipts'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('Receipts') }}</h1>
            <p class="text-muted mb-0">{{ __('Record customer payments and distribute them to invoices.') }}</p>
        </div>
        @can('create', \App\Modules\Finance\Domain\Models\Receipt::class)
            <a href="{{ route('admin.finance.receipts.create') }}" class="btn btn-primary">{{ __('New Receipt') }}</a>
        @endcan
    </div>

    <form method="get" class="card card-body mb-4 shadow-sm">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="q" class="form-label">{{ __('Search') }}</label>
                <input type="text" name="q" id="q" class="form-control" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Doc no. or customer') }}">
            </div>
            <div class="col-md-4">
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

    <div class="card shadow-sm">
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" :search-placeholder="__('Tahsilat ara…')" />
            </x-slot>
        </x-table>
    </div>
@endsection
