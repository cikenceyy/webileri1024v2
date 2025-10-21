@extends('layouts.admin')

@section('title', __('Bank Accounts'))

@section('content')
    <x-ui-page-header :title="__('Bank Accounts')">
        <x-slot name="actions">
            @can('create', App\Modules\Finance\Domain\Models\BankAccount::class)
                <x-ui-button tag="a" :href="route('admin.finance.bank-accounts.create')" variant="primary">{{ __('Add Account') }}</x-ui-button>
            @endcan
        </x-slot>
    </x-ui-page-header>

    <x-ui-card>
        <div class="table-responsive">
            <x-ui-table class="table-compact">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Account No') }}</th>
                        <th>{{ __('Currency') }}</th>
                        <th>{{ __('Default') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>{{ $account->name }}</td>
                            <td>{{ $account->account_no ?? '—' }}</td>
                            <td>{{ $account->currency }}</td>
                            <td>{{ $account->is_default ? __('Yes') : __('No') }}</td>
                            <td>{{ ucfirst($account->status) }}</td>
                            <td class="text-end">
                                <x-ui-button tag="a" size="sm" :href="route('admin.finance.bank-accounts.edit', $account)" variant="secondary">{{ __('Edit') }}</x-ui-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><x-ui-empty title="{{ __('No bank accounts yet') }}" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui-table>
        </div>
        <div class="mt-3">
            {{ $accounts->links() }}
        </div>
    </x-ui-card>
@endsection
