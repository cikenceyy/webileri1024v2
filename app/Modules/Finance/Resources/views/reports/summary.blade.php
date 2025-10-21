@extends('layouts.admin')

@section('title', __('A/R Summary'))

@section('content')
    <x-ui-page-header :title="__('Accounts Receivable Summary')">
        <x-slot name="actions">
            <a class="btn btn-icon btn-outline-secondary" href="{{ route('admin.finance.reports.summary', ['print' => 1]) }}" target="_blank" rel="noopener">{{ __('Yazdır') }}</a>
            <a class="btn btn-icon btn-outline-primary" href="{{ route('admin.finance.reports.summary', ['format' => 'csv']) }}">{{ __('CSV Dışa Aktar') }}</a>
        </x-slot>
    </x-ui-page-header>

    <div class="row g-3 mb-4">
        @forelse($totals as $summary)
            <div class="col-md-4">
                <x-ui-card>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">{{ __('Para Birimi') }}</span>
                        <span class="fw-semibold">{{ $summary['currency'] }}</span>
                    </div>
                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span>{{ __('Toplam Faturalama') }}</span>
                            <span>{{ number_format($summary['total'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>{{ __('Toplam Tahsil Edilen') }}</span>
                            <span>{{ number_format($summary['paid'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-danger">
                            <span>{{ __('Toplam Alacak') }}</span>
                            <span>{{ number_format($summary['outstanding'], 2) }}</span>
                        </div>
                    </div>
                </x-ui-card>
            </div>
        @empty
            <div class="col-12">
                <x-ui-card>
                    <x-ui-empty title="{{ __('Kayıt bulunamadı') }}" />
                </x-ui-card>
            </div>
        @endforelse
    </div>

    <x-ui-card>
        <div class="table-responsive">
            <x-ui-table class="table-compact">
                <thead>
                    <tr>
                        <th>{{ __('Müşteri') }}</th>
                        <th>{{ __('Para Birimi') }}</th>
                        <th class="text-end">{{ __('Toplam') }}</th>
                        <th class="text-end">{{ __('Tahsil Edilen') }}</th>
                        <th class="text-end">{{ __('Kalan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['customer'] }}</td>
                            <td>{{ $row['currency'] }}</td>
                            <td class="text-end">{{ number_format($row['total'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['paid'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['balance_due'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><x-ui-empty title="{{ __('Kayıt bulunamadı') }}" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </x-ui-table>
        </div>
    </x-ui-card>
@endsection
