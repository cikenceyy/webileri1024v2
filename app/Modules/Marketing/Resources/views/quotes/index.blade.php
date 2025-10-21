@extends('layouts.admin')

@section('content')
<x-ui-page-header :title="__('Quotes')">
    <x-slot:name>actions</x-slot:name>
    <x-ui-button variant="primary" href="{{ route('admin.marketing.quotes.create') }}">{{ __('New Quote') }}</x-ui-button>
</x-ui-page-header>

<form method="get" class="card mb-4 p-3">
    <div class="row g-3 align-items-end">
        <div class="col-md-4"><x-ui-input name="q" :label="__('Search')" :value="request('q')" /></div>
        <div class="col-md-3">
            <x-ui-select name="status" :label="__('Status')" :value="request('status')">
                <option value="">{{ __('All') }}</option>
                @foreach(['draft','sent','accepted','rejected','cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status')===$status)>{{ ucfirst($status) }}</option>
                @endforeach
            </x-ui-select>
        </div>
        <div class="col-md-3"><x-ui-input name="customer_id" :label="__('Customer ID')" :value="request('customer_id')" /></div>
        <div class="col-12"><x-ui-button type="submit">{{ __('Filter') }}</x-ui-button></div>
    </div>
</form>

<x-ui-card>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>{{ __('Quote No') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Grand Total') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotes as $quote)
                    <tr>
                        <td><a href="{{ route('admin.marketing.quotes.show', $quote) }}">{{ $quote->quote_no }}</a></td>
                        <td>{{ $quote->customer?->name }}</td>
                        <td>{{ optional($quote->date)->format('d.m.Y') }}</td>
                        <td><x-ui-badge :type="$quote->status === 'accepted' ? 'success' : 'secondary'">{{ ucfirst($quote->status) }}</x-ui-badge></td>
                        <td class="text-end">{{ number_format($quote->grand_total, 2) }} {{ $quote->currency }}</td>
                        <td class="text-end">
                            <form method="post" action="{{ route('admin.marketing.quotes.destroy', $quote) }}" onsubmit="return confirm('{{ __('Delete quote?') }}');">
                                @csrf
                                @method('delete')
                                <x-ui-button type="submit" size="sm" variant="danger">{{ __('Delete') }}</x-ui-button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"><x-ui-empty :title="__('No quotes found.')" /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $quotes->links() }}</div>
</x-ui-card>
@endsection
