@extends('layouts.admin')

@section('title', __('Bank Transactions'))

@section('content')
    <x-ui-page-header :title="__('Bank Transactions')" />

    <x-ui-card>
        <form method="POST" action="{{ route('admin.finance.bank-transactions.store') }}" class="row g-2 align-items-end mb-4">
            @csrf
            <div class="col-md-3">
                <x-ui-select name="bank_account_id" :label="__('Account')" :options="$accounts->map(fn($a) => ['value' => $a->id, 'label' => $a->name])->toArray()" :value="$filters['bank_account_id'] ?? null" required />
            </div>
            <div class="col-md-2">
                <x-ui-select name="type" :label="__('Type')" :options="[['value' => 'deposit', 'label' => __('Deposit')], ['value' => 'withdrawal', 'label' => __('Withdrawal')]]" />
            </div>
            <div class="col-md-2">
                <x-ui-input type="number" step="0.01" min="0.01" name="amount" :label="__('Amount')" required />
            </div>
            <div class="col-md-2">
                <x-ui-input name="currency" :label="__('Currency')" :value="old('currency', config('finance.default_currency'))" />
            </div>
            <div class="col-md-2">
                <x-ui-input type="date" name="transacted_at" :label="__('Date')" :value="old('transacted_at', now()->format('Y-m-d'))" required />
            </div>
            <div class="col-md-1">
                <x-ui-button type="submit" variant="primary" class="w-100">{{ __('Add') }}</x-ui-button>
            </div>
        </form>

        <div class="table-responsive">
            <x-ui-table class="table-compact">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transacted_at?->format('d.m.Y') }}</td>
                            <td>{{ $transaction->bankAccount?->name ?? '—' }}</td>
                            <td>{{ ucfirst($transaction->type) }}</td>
                            <td class="text-end">{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</td>
                            <td>{{ $transaction->reference ?? '—' }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.finance.bank-transactions.destroy', $transaction) }}" onsubmit="return confirm('{{ __('Delete transaction?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <x-ui-button type="submit" size="sm" variant="danger">{{ __('Delete') }}</x-ui-button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><x-ui-empty title="{{ __('No transactions recorded') }}" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui-table>
        </div>

        <div class="mt-3">
            {{ $transactions->links() }}
        </div>
    </x-ui-card>
@endsection
