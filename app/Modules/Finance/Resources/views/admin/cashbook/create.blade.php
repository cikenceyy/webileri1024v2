@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('New Cashbook Entry'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('New Cashbook Entry') }}</h1>
            <p class="text-muted mb-0">{{ __('Log an incoming or outgoing cash movement.') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.finance.cashbook.store') }}" class="card shadow-sm">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="direction" class="form-label">{{ __('Direction') }}</label>
                    <select name="direction" id="direction" class="form-select" required>
                        @foreach($directions as $direction)
                            <option value="{{ $direction }}" @selected(old('direction') === $direction)>{{ __(Str::headline($direction)) }}</option>
                        @endforeach
                    </select>
                    @error('direction')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="occurred_at" class="form-label">{{ __('Date') }}</label>
                    <input type="date" name="occurred_at" id="occurred_at" class="form-control" value="{{ old('occurred_at', now()->format('Y-m-d')) }}" required>
                    @error('occurred_at')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label for="amount" class="form-label">{{ __('Amount') }}</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
                    @error('amount')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="account" class="form-label">{{ __('Account') }}</label>
                    <input type="text" name="account" id="account" class="form-control" value="{{ old('account') }}" required>
                    @error('account')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="reference_type" class="form-label">{{ __('Reference') }}</label>
                    <div class="input-group">
                        <input type="text" name="reference_type" id="reference_type" class="form-control" placeholder="{{ __('Type (optional)') }}" value="{{ old('reference_type') }}">
                        <input type="number" name="reference_id" class="form-control" placeholder="{{ __('ID') }}" value="{{ old('reference_id') }}">
                    </div>
                    @error('reference_type')<div class="text-danger small">{{ $message }}</div>@enderror
                    @error('reference_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('admin.finance.cashbook.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Save Entry') }}</button>
        </div>
    </form>
@endsection
