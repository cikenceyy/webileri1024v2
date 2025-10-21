@extends('layouts.admin')

@section('content')
<x-ui-page-header :title="__('Customers')">
    <x-slot:name>actions</x-slot:name>
    <x-ui-button variant="primary" href="{{ route('admin.marketing.customers.create') }}">{{ __('New Customer') }}</x-ui-button>
</x-ui-page-header>

<form method="get" class="card mb-4 p-3">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <x-ui-input name="q" :label="__('Search')" :value="request('q')" placeholder="{{ __('Name, code or email') }}" />
        </div>
        <div class="col-md-3">
            <x-ui-select name="status" :label="__('Status')" :value="request('status')">
                <option value="">{{ __('All') }}</option>
                <option value="active" @selected(request('status')==='active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(request('status')==='inactive')>{{ __('Inactive') }}</option>
            </x-ui-select>
        </div>
        <div class="col-md-3">
            <x-ui-select name="sort" :label="__('Sort By')" :value="request('sort', 'created_at')">
                <option value="created_at">{{ __('Created At') }}</option>
                <option value="name">{{ __('Name') }}</option>
                <option value="code">{{ __('Code') }}</option>
            </x-ui-select>
        </div>
        <div class="col-md-2">
            <x-ui-select name="dir" :label="__('Direction')" :value="request('dir', 'desc')">
                <option value="desc">{{ __('Desc') }}</option>
                <option value="asc">{{ __('Asc') }}</option>
            </x-ui-select>
        </div>
        <div class="col-12">
            <x-ui-button type="submit">{{ __('Filter') }}</x-ui-button>
        </div>
    </div>
</form>

<x-ui-card>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Balance') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $customer->code }}</td>
                        <td><a href="{{ route('admin.marketing.customers.show', $customer) }}">{{ $customer->name }}</a></td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td><x-ui-badge :type="$customer->status === 'active' ? 'success' : 'secondary'">{{ ucfirst($customer->status) }}</x-ui-badge></td>
                        <td class="text-end">{{ number_format($customer->balance, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.marketing.customers.edit', $customer) }}" class="btn btn-sm btn-light">{{ __('Edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-ui-empty :title="__('No customers found')" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $customers->links() }}
    </div>
</x-ui-card>
@endsection
