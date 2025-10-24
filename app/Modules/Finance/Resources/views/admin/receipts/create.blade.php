@extends('layouts.admin')

@section('title', __('New Receipt'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('New Receipt') }}</h1>
            <p class="text-muted mb-0">{{ __('Capture an incoming payment from a customer.') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.finance.receipts.store') }}" class="card shadow-sm">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="customer_id" class="form-label">{{ __('Customer') }}</label>
                    <select name="customer_id" id="customer_id" class="form-select" required>
                        <option value="">{{ __('Select customer') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="received_at" class="form-label">{{ __('Received At') }}</label>
                    <input type="date" name="received_at" id="received_at" class="form-control" value="{{ old('received_at', now()->format('Y-m-d')) }}" required>
                    @error('received_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="amount" class="form-label">{{ __('Amount') }}</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
                    @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="method" class="form-label">{{ __('Method') }}</label>
                    <input type="text" name="method" id="method" class="form-control" value="{{ old('method') }}">
                    @error('method')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="reference" class="form-label">{{ __('Reference') }}</label>
                    <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference') }}">
                    @error('reference')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('admin.finance.receipts.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Save Receipt') }}</button>
        </div>
    </form>
@endsection
