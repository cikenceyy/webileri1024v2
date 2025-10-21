@extends('layouts.admin')

@section('content')
    <x-ui.page-header title="Stok Durumu" description="Ambar bazında mevcut stokları görüntüleyin.">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.inventory.stock.in.form') }}" class="btn btn-sm btn-primary">Stok Girişi</a>
                <a href="{{ route('admin.inventory.stock.out.form') }}" class="btn btn-sm btn-outline-primary">Stok Çıkışı</a>
                <a href="{{ route('admin.inventory.stock.transfer.form') }}" class="btn btn-sm btn-outline-secondary">Transfer</a>
                <a href="{{ route('admin.inventory.stock.adjust.form') }}" class="btn btn-sm btn-outline-secondary">Düzeltme</a>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('status'))
        <x-ui.alert type="success" class="mb-3">{{ session('status') }}</x-ui.alert>
    @endif

    <x-ui.card class="mb-4" data-inventory-filters>
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <x-ui.input name="q" label="Ürün Ara" value="{{ $filters['q'] ?? '' }}" placeholder="SKU veya ürün adı" />
            </div>
            <div class="col-md-4">
                <label class="form-label">Ambar</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected($filters['warehouse_id'] == $warehouse->id)>
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Durum</label>
                <select name="status" class="form-select">
                    <option value="">Tümü</option>
                    <option value="low" @selected(($filters['status'] ?? null) === 'low')>Kritik (Reorder altı)</option>
                </select>
            </div>
            <div class="col-md-1 text-md-end">
                <button type="submit" class="btn btn-primary w-100">Filtrele</button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card>
        @if($items->count())
            <div class="table-responsive">
                <x-ui.table dense>
                    <thead>
                        <tr>
                            <th>Ambar</th>
                            <th>Ürün</th>
                            <th>Varyant</th>
                            <th class="text-end">Mevcut</th>
                            <th class="text-end">Rezerve</th>
                            <th class="text-end">Kritik Seviye</th>
                            <th class="text-end">Güncellendi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr @class(['table-warning' => $item->reorder_point > 0 && $item->qty < $item->reorder_point])>
                                <td>{{ $item->warehouse?->name ?? '—' }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $item->product?->name ?? 'Silinmiş Ürün' }}</div>
                                    <div class="text-muted small">{{ $item->product?->sku }}</div>
                                </td>
                                <td>
                                    @if($item->variant)
                                        <div class="fw-semibold">{{ $item->variant->sku }}</div>
                                        <div class="text-muted small">{{ $item->variant->option_summary ?? '' }}</div>
                                    @else
                                        <span class="text-muted">Varsayılan</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ number_format((float) $item->qty, 3, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $item->reserved_qty, 3, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) $item->reorder_point, 3, ',', '.') }}</td>
                                <td class="text-end text-muted small">{{ optional($item->updated_at)->format('d.m.Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            </div>
            <div class="mt-3">
                {{ $items->links() }}
            </div>
        @else
            <x-ui.empty title="Stok kaydı bulunamadı" description="Filtreleri değiştirerek tekrar deneyin." />
        @endif
    </x-ui.card>
@endsection
