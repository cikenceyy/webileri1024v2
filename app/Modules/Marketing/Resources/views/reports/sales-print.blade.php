@extends('layouts.print')

@section('title', __('Satış Raporu'))

@section('content')
    <div class="print-header">
        <div>
            <h1 class="h4 mb-1">{{ __('Satış Raporu') }}</h1>
            <p class="mb-0">{{ optional(app('company'))->name }}</p>
        </div>
        <div class="print-meta">
            @if($filters['date_from'])
                <div><strong>{{ __('Başlangıç') }}:</strong> {{ $filters['date_from'] }}</div>
            @endif
            @if($filters['date_to'])
                <div><strong>{{ __('Bitiş') }}:</strong> {{ $filters['date_to'] }}</div>
            @endif
            @if($filters['status'])
                <div><strong>{{ __('Durum') }}:</strong> {{ ucfirst($filters['status']) }}</div>
            @endif
        </div>
    </div>

    <h2 class="h6 mt-4">{{ __('Müşteriye Göre Satış') }}</h2>
    <table class="print-table">
        <thead>
        <tr>
            <th>{{ __('Müşteri') }}</th>
            <th>{{ __('Para Birimi') }}</th>
            <th class="text-end">{{ __('Sipariş Sayısı') }}</th>
            <th class="text-end">{{ __('Tutar') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($customerRows as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td>{{ $row['currency'] }}</td>
                <td class="text-end">{{ $row['orders'] }}</td>
                <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">{{ __('Kayıt bulunamadı') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <h2 class="h6 mt-4">{{ __('Ürüne Göre Satış (ilk 50)') }}</h2>
    <table class="print-table">
        <thead>
        <tr>
            <th>{{ __('Ürün') }}</th>
            <th>{{ __('Para Birimi') }}</th>
            <th class="text-end">{{ __('Satış Adedi') }}</th>
            <th class="text-end">{{ __('Satış Tutarı') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($productRows as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td>{{ $row['currency'] }}</td>
                <td class="text-end">{{ number_format($row['quantity'], 3) }}</td>
                <td class="text-end">{{ number_format($row['amount'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">{{ __('Kayıt bulunamadı') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
@endsection
