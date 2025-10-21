@extends('layouts.admin')

@section('content')
    <x-ui-page-header title="Anlık Stok" description="Ambar ve varyant bazında güncel stok miktarları.">
        <x-slot:actions>
            <a href="{{ request()->fullUrlWithQuery(['download' => 'csv']) }}" class="btn btn-sm btn-outline-secondary">CSV indir</a>
        </x-slot:actions>
    </x-ui-page-header>

    <x-ui-card class="mb-4">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Ambar</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ürün</label>
                <select name="product_id" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? null) == $product->id)>{{ $product->sku }} — {{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Varyant</label>
                <select name="variant_id" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($variants as $variant)
                        <option value="{{ $variant->id }}" @selected(($filters['variant_id'] ?? null) == $variant->id)>{{ $variant->sku }} — {{ $variant->product?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtrele</button>
            </div>
        </form>
    </x-ui-card>

    <x-ui-card>
        @if($items->count())
            <div class="table-responsive">
                <x-ui-table dense>
                    <thead>
                        <tr>
                            <th>Ambar</th>
                            <th>Ürün</th>
                            <th>Varyant</th>
                            <th class="text-end">Mevcut</th>
                            <th class="text-end">Rezerve</th>
                            <th class="text-end">Reorder</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->warehouse?->name ?? '—' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->product?->name ?? 'Silinmiş' }}</div>
                                    <div class="text-muted small">{{ $item->product?->sku }}</div>
                                </td>
                                <td>{{ $item->variant?->sku ?? 'Varsayılan' }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $item->qty, 3, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $item->reserved_qty, 3, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $item->reorder_point, 3, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui-table>
            </div>
        @else
            <x-ui-empty title="Stok kaydı yok" description="Filtreleri değiştirerek tekrar deneyin." />
        @endif
    </x-ui-card>
@endsection
