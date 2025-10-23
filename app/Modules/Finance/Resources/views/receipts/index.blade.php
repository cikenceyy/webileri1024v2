@extends('layouts.admin')

@section('title', __('Tahsilatlar'))
@section('module', 'finance')
@section('page', 'receipts')

@push('page-styles')
    @vite('app/Modules/Finance/Resources/scss/finance.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Finance/Resources/js/finance.js')
@endpush

@section('content')
    <x-ui-page-header :title="__('Tahsilatlar')">
        <x-slot name="actions">
            @can('create', App\Modules\Finance\Domain\Models\Receipt::class)
                <x-ui-button tag="a" :href="route('admin.finance.receipts.create')" variant="primary">{{ __('Yeni Tahsilat') }}</x-ui-button>
            @endcan
        </x-slot>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" class="row g-2 align-items-end mb-3">
            <div class="col-md-4">
                <x-ui-input name="q" :label="__('Arama')" :value="$filters['q'] ?? ''" placeholder="{{ __('Receipt no. or customer') }}" />
            </div>
            <div class="col-md-4">
                <x-ui-select name="customer_id" :label="__('Müşteri')" :options="$customers->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()" :placeholder="__('Tüm müşteriler')" :value="$filters['customer_id'] ?? ''" />
            </div>
            <div class="col-md-2">
                <x-ui-button type="submit" class="w-100">{{ __('Filtrele') }}</x-ui-button>
            </div>
        </form>

        <div class="table-responsive">
            <x-ui-table class="table-compact">
                <thead>
                    <tr>
                        <th>{{ __('Tahsilat #') }}</th>
                        <th>{{ __('Tarih') }}</th>
                        <th>{{ __('Müşteri') }}</th>
                        <th>{{ __('Banka Hesabı') }}</th>
                        <th class="text-end">{{ __('Tutar') }}</th>
                        <th class="text-end">{{ __('Dağıtılan') }}</th>
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
                                <x-ui-button tag="a" size="sm" :href="route('admin.finance.receipts.edit', $receipt)" variant="secondary">{{ __('Düzenle') }}</x-ui-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"><x-ui-empty title="{{ __('Tahsilat bulunamadı') }}" /></td>
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
