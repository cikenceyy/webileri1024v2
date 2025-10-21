@extends('layouts.admin')

@section('title', __('Invoices'))

@section('module', 'finance')

@section('content')
    <x-ui.page-header :title="__('Invoices')">
        <x-slot name="actions">
            @can('create', App\Modules\Finance\Domain\Models\Invoice::class)
                <x-ui.button tag="a" :href="route('admin.finance.invoices.create')" variant="primary">{{ __('New Invoice') }}</x-ui.button>
            @endcan
        </x-slot>
    </x-ui.page-header>

    <x-ui.card>
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-md-4">
                <x-ui.input name="q" :label="__('Search')" :value="$filters['q'] ?? ''" placeholder="{{ __('Invoice no. or customer') }}" />
            </div>
            <div class="col-md-3">
                <x-ui.select name="customer_id" :label="__('Customer')" :options="$customers->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()" :placeholder="__('All customers')" :value="$filters['customer_id'] ?? ''" />
            </div>
            <div class="col-md-3">
                <x-ui.input name="status" :label="__('Status')" :value="$filters['status'] ?? ''" placeholder="{{ __('e.g. draft') }}" />
            </div>
            <div class="col-md-2">
                <x-ui.button type="submit" class="w-100">{{ __('Filter') }}</x-ui.button>
            </div>
        </form>

        <div class="table-responsive">
            <x-ui.table class="table-compact">
                <thead>
                    <tr>
                        <th>{{ __('Invoice #') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Issue Date') }}</th>
                        <th>{{ __('Due Date') }}</th>
                        <th class="text-end">{{ __('Total') }}</th>
                        <th class="text-end">{{ __('Balance') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td><a href="{{ route('admin.finance.invoices.show', $invoice) }}">{{ $invoice->invoice_no }}</a></td>
                            <td>{{ $invoice->customer?->name ?? '—' }}</td>
                            <td>{{ $invoice->issue_date?->format('d.m.Y') }}</td>
                            <td>{{ $invoice->due_date?->format('d.m.Y') ?? '—' }}</td>
                            <td class="text-end">{{ number_format($invoice->grand_total, 2) }} {{ $invoice->currency }}</td>
                            <td class="text-end">{{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</td>
                            <td><x-ui.badge type="info">{{ ucfirst($invoice->status) }}</x-ui.badge></td>
                            <td class="text-end">
                                <x-ui.button tag="a" size="sm" :href="route('admin.finance.invoices.edit', $invoice)" variant="secondary">{{ __('Edit') }}</x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-ui.empty title="{{ __('No invoices found') }}" description="{{ __('Adjust your filters or create a new invoice.') }}" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-3">
            {{ $invoices->links() }}
        </div>
    </x-ui.card>
@endsection
