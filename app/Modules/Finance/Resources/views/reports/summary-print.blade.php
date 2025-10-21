@extends('layouts.print')

@section('title', __('A/R Summary'))

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Accounts Receivable Summary') }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
    </div>

    <table class="print-summary">
        <thead>
            <tr>
                <th>{{ __('Para Birimi') }}</th>
                <th>{{ __('Toplam Faturalama') }}</th>
                <th>{{ __('Toplam Tahsil Edilen') }}</th>
                <th>{{ __('Toplam Alacak') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse($totals as $summary)
            <tr>
                <td>{{ $summary['currency'] }}</td>
                <td>{{ number_format($summary['total'], 2) }}</td>
                <td>{{ number_format($summary['paid'], 2) }}</td>
                <td>{{ number_format($summary['outstanding'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">{{ __('Kayıt bulunamadı') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <h2 class="h6 mt-4">{{ __('Müşteri Bazında Özet') }}</h2>
    <table class="print-table">
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
                <td colspan="5">{{ __('Kayıt bulunamadı') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
