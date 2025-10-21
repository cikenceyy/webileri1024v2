@extends('layouts.admin')

@section('content')
    <x-ui-page-header title="Envanter Değerlemesi" description="Ağırlıklı ortalama maliyet ile stok değeri." />

    <x-ui-card>
        @if($rows->count())
            <div class="table-responsive">
                <x-ui-table dense>
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Varyant</th>
                            <th class="text-end">Miktar</th>
                            <th class="text-end">Ağırlıklı Ortalama</th>
                            <th class="text-end">Toplam Değer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row['product']->name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $row['product']->sku ?? '' }}</div>
                                </td>
                                <td>{{ $row['variant']->sku ?? 'Varsayılan' }}</td>
                                <td class="text-end">{{ number_format($row['qty'], 3, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($row['avg_cost'], 4, ',', '.') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($row['value'], 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Toplam</th>
                            <th class="text-end fw-semibold">{{ number_format($totalValue, 2, ',', '.') }} {{ config('inventory.default_currency', 'TRY') }}</th>
                        </tr>
                    </tfoot>
                </x-ui-table>
            </div>
        @else
            <x-ui-empty title="Değerleme bulunamadı" description="Stok hareketleri kayıtlı değil." />
        @endif
    </x-ui-card>
@endsection
