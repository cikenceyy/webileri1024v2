@extends('layouts.admin')

@section('content')
    <x-ui-page-header title="{{ __('Satış Raporu') }}" description="{{ __('Müşteri ve ürün kırılımında satış toplamları') }}">
        <x-slot name="actions">
            <a class="btn btn-icon btn-outline-secondary" href="{{ route('admin.marketing.reports.sales.print', request()->query()) }}" target="_blank" rel="noopener">{{ __('Yazdır') }}</a>
            <a class="btn btn-icon btn-outline-primary" href="{{ route('admin.marketing.reports.sales.export', array_merge(request()->query(), ['type' => 'customer'])) }}">{{ __('Müşteri CSV') }}</a>
            <a class="btn btn-icon btn-outline-primary" href="{{ route('admin.marketing.reports.sales.export', array_merge(request()->query(), ['type' => 'product'])) }}">{{ __('Ürün CSV') }}</a>
        </x-slot>
    </x-ui-page-header>

    <x-ui-card>
        <form method="GET" action="{{ route('admin.marketing.reports.sales') }}" class="row g-3 align-items-end" data-prevent-double-submit>
            <div class="col-md-3">
                <x-ui-input type="date" name="date_from" label="{{ __('Başlangıç Tarihi') }}" :value="$filters['date_from']" />
            </div>
            <div class="col-md-3">
                <x-ui-input type="date" name="date_to" label="{{ __('Bitiş Tarihi') }}" :value="$filters['date_to']" />
            </div>
            <div class="col-md-3">
                <x-ui-select name="status" label="{{ __('Sipariş Durumu') }}" :value="$filters['status']">
                    <option value="">{{ __('Tümü') }}</option>
                    <option value="draft" @selected($filters['status'] === 'draft')>{{ __('Taslak') }}</option>
                    <option value="confirmed" @selected($filters['status'] === 'confirmed')>{{ __('Onaylandı') }}</option>
                    <option value="shipped" @selected($filters['status'] === 'shipped')>{{ __('Gönderildi') }}</option>
                    <option value="cancelled" @selected($filters['status'] === 'cancelled')>{{ __('İptal') }}</option>
                </x-ui-select>
            </div>
            <div class="col-md-3 text-md-end">
                <button type="submit" class="btn btn-primary">{{ __('Filtrele') }}</button>
            </div>
        </form>
    </x-ui-card>

    <div class="row g-4 mt-2">
        <div class="col-lg-6">
            <x-ui-card>
                <h6 class="fw-semibold mb-3">{{ __('Müşteriye Göre Satış') }}</h6>
                <div class="table-responsive">
                    <x-ui-table class="table-compact">
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
                                    <td colspan="4"><x-ui-empty title="{{ __('Kayıt bulunamadı') }}" /></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui-table>
                </div>
            </x-ui-card>
        </div>
        <div class="col-lg-6">
            <x-ui-card>
                <h6 class="fw-semibold mb-3">{{ __('Ürüne Göre Satış (ilk 50)') }}</h6>
                <div class="table-responsive">
                    <x-ui-table class="table-compact">
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
                                    <td colspan="4"><x-ui-empty title="{{ __('Kayıt bulunamadı') }}" /></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </x-ui-table>
                </div>
            </x-ui-card>
        </div>
    </div>
@endsection
