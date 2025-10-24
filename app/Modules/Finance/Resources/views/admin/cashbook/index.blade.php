@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Cashbook'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ __('Cashbook (Lite)') }}</h1>
            <p class="text-muted mb-0">{{ __('Track incoming and outgoing cash movements.') }}</p>
        </div>
        @can('create', \App\Modules\Finance\Domain\Models\CashbookEntry::class)
            <a href="{{ route('admin.finance.cashbook.create') }}" class="btn btn-primary">{{ __('New Entry') }}</a>
        @endcan
    </div>

    <form method="get" class="card card-body mb-4 shadow-sm">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="direction" class="form-label">{{ __('Direction') }}</label>
                <select name="direction" id="direction" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach($directions as $direction)
                        <option value="{{ $direction }}" @selected(($filters['direction'] ?? '') === $direction)>{{ __(Str::headline($direction)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="from" class="form-label">{{ __('From') }}</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label for="to" class="form-label">{{ __('To') }}</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Direction') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th>{{ __('Reference') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>{{ optional($entry->occurred_at)?->format('Y-m-d') }}</td>
                        <td>{{ __(Str::headline($entry->direction)) }}</td>
                        <td>{{ $entry->account }}</td>
                        <td>{{ $entry->reference_type ? $entry->reference_type . ' #' . $entry->reference_id : '—' }}</td>
                        <td class="text-end">{{ number_format($entry->amount, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.finance.cashbook.show', $entry) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ __('No cashbook entries yet.') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $entries->links() }}
        </div>
    </div>
@endsection
