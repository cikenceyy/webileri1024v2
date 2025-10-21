@extends('layouts.admin')

@section('title', __('Receipts'))

@section('content')
    <x-ui-page-header :title="__('Receipts')">
        <x-slot name="actions">
            @can('create', App\Modules\Finance\Domain\Models\Receipt::class)
                <x-ui-button tag="a" :href="route('admin.finance.receipts.create')" variant="primary">{{ __('New Receipt') }}</x-ui-button>
            @endcan
        </x-slot>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-md-4">
                <x-ui-input name="q" :label="__('Search')" :value="$filters['q'] ?? ''" placeholder="{{ __('Receipt no. or customer') }}" />
            </div>
            <div class="col-md-4">
                <x-ui-select name="customer_id" :label="__('Customer')" :options="$customers->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()" :placeholder="__('All customers')" :value="$filters['customer_id'] ?? ''" />
            </div>
            <div class="col-md-2">
                <x-ui-button type="submit" class="w-100">{{ __('Filter') }}</x-ui-button>
            </div>
        </form>

        <div class="table-responsive">
            <x-ui-table class="table-compact">
                <thead>
                    <tr>
                        <th>{{ __('Receipt #') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Bank Account') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th class="text-end">{{ __('Allocated') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr>
                            <td><a href="{{ route('admin.finance.receipts.show', $receipt) }}">{{ $receipt->receipt_no }}</a></td>
                            <td>{{ $receipt->receipt_date?->format('d.m.Y') }}</td>
                            <td>{{ $receipt->customer?->name ?? '—' }}</td>
                            <td>{{ $receipt->bankAccount?->name ?? '—' }}</td>
                            <td class="text-end">{{ number_format($receipt->amount, 2) }} {{ $receipt->currency }}</td>
                            <td class="text-end">{{ number_format($receipt->allocated_total, 2) }} {{ $receipt->currency }}</td>
                            <td class="text-end">
                                <x-ui-button tag="a" size="sm" :href="route('admin.finance.receipts.edit', $receipt)" variant="secondary">{{ __('Edit') }}</x-ui-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><x-ui-empty title="{{ __('No receipts found') }}" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui-table>
        </div>

        <div class="mt-3">
            {{ $receipts->links() }}
        </div>
    </x-ui-card>
@endsection
