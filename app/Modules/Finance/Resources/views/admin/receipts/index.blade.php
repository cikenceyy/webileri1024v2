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
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>{{ __('Receipt No') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Received At') }}</th>
                    <th class="text-end">{{ __('Amount') }}</th>
                    <th class="text-end">{{ __('Applied') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($receipts as $receipt)
                    <tr>
                        <td>{{ $receipt->doc_no }}</td>
                        <td>{{ $receipt->customer?->name }}</td>
                        <td>{{ optional($receipt->received_at)?->format('Y-m-d') }}</td>
                        <td class="text-end">{{ number_format($receipt->amount, 2) }}</td>
                        <td class="text-end">{{ number_format($receipt->appliedTotal(), 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.finance.receipts.show', $receipt) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">{{ __('No receipts recorded.') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $receipts->links() }}
        </div>
    </div>
@endsection
