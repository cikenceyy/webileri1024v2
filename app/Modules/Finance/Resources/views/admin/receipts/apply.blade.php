@php use Illuminate\Support\Str; @endphp
@extends('layouts.admin')

@section('title', __('Apply Receipt'))

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-1">{{ __('Apply Receipt') }} {{ $receipt->doc_no }}</h1>
            <p class="text-muted mb-0">{{ __('Distribute :amount to open invoices.', ['amount' => number_format($receipt->amount, 2)]) }}</p>
        </div>
        <a href="{{ route('admin.finance.receipts.show', $receipt) }}" class="btn btn-outline-secondary">{{ __('Back to receipt') }}</a>
    </div>

    <form method="post" action="{{ route('admin.finance.receipts.apply', $receipt) }}" class="card shadow-sm">
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Balance') }}</th>
                        <th style="width:160px" class="text-end">{{ __('Apply Amount') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($openInvoices as $index => $invoice)
                        @php $balance = max(0, $invoice->grand_total - $invoice->paid_amount); @endphp
                        <tr>
                            <td><a href="{{ route('admin.finance.invoices.show', $invoice) }}">{{ $invoice->doc_no }}</a></td>
                            <td>{{ __(Str::headline($invoice->status)) }}</td>
                            <td class="text-end">{{ number_format($balance, 2) }}</td>
                            <td class="text-end">
                                <input type="hidden" name="applications[{{ $index }}][invoice_id]" value="{{ $invoice->id }}">
                                <input type="number" step="0.01" min="0" max="{{ $balance }}" name="applications[{{ $index }}][amount]" class="form-control form-control-sm text-end" value="{{ old('applications.' . $index . '.amount', 0) }}">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">{{ __('No open invoices for this customer.') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @error('applications')<div class="text-danger small">{{ $message }}</div>@enderror
            @foreach($errors->get('applications.*.*') as $fieldErrors)
                @foreach($fieldErrors as $error)
                    <div class="text-danger small">{{ $error }}</div>
                @endforeach
            @endforeach
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">{{ __('Apply Receipt') }}</button>
        </div>
    </form>
@endsection
