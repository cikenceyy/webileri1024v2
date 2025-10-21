@extends('layouts.admin')

@section('title', __('Allocations'))

@section('content')
    <x-ui.page-header :title="__('Allocations')" />

    <x-ui.card class="mb-3">
        <form method="POST" action="{{ route('admin.finance.allocations.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <x-ui.select name="invoice_id" :label="__('Invoice')" :options="\App\Modules\Finance\Domain\Models\Invoice::orderByDesc('issue_date')->take(25)->get()->map(fn($inv) => ['value' => $inv->id, 'label' => $inv->invoice_no])->toArray()" required />
            </div>
            <div class="col-md-4">
                <x-ui.select name="receipt_id" :label="__('Receipt')" :options="\App\Modules\Finance\Domain\Models\Receipt::orderByDesc('receipt_date')->take(25)->get()->map(fn($rec) => ['value' => $rec->id, 'label' => $rec->receipt_no])->toArray()" required />
            </div>
            <div class="col-md-2">
                <x-ui.input type="number" step="0.01" min="0.01" name="amount" :label="__('Amount')" required />
            </div>
            <div class="col-md-2">
                <x-ui.button type="submit" class="w-100" variant="primary">{{ __('Allocate') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card>
        <div class="table-responsive">
            <x-ui.table class="table-compact">
                <thead>
                    <tr>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Receipt') }}</th>
                        <th>{{ __('Allocated At') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allocations as $allocation)
                        <tr>
                            <td>{{ $allocation->invoice->invoice_no ?? '—' }}</td>
                            <td>{{ $allocation->receipt->receipt_no ?? '—' }}</td>
                            <td>{{ $allocation->allocated_at?->format('d.m.Y H:i') }}</td>
                            <td class="text-end">{{ number_format($allocation->amount, 2) }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.finance.allocations.destroy', $allocation) }}" onsubmit="return confirm('{{ __('Remove allocation?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" size="sm" variant="danger">{{ __('Remove') }}</x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><x-ui.empty title="{{ __('No allocations found') }}" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui.table>
        </div>

        <div class="mt-3">
            {{ $allocations->links() }}
        </div>
    </x-ui.card>
@endsection
