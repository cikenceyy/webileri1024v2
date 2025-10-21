@extends('layouts.admin')

@section('content')
    <x-ui.page-header title="Stok Hareket Defteri" description="Tüm stok giriş/çıkış hareketlerini izleyin." />

    <x-ui.card class="mb-4">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Ambar</label>
                <select name="warehouse_id" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Ürün</label>
                <select name="product_id" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? null) == $product->id)>{{ $product->sku }} — {{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Varyant</label>
                <select name="variant_id" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($variants as $variant)
                        <option value="{{ $variant->id }}" @selected(($filters['variant_id'] ?? null) == $variant->id)>{{ $variant->sku }} — {{ $variant->product?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Neden</label>
                <select name="reason" class="form-select">
                    <option value="">Tümü</option>
                    @foreach($reasons as $reason)
                        <option value="{{ $reason }}" @selected(($filters['reason'] ?? null) === $reason)>{{ ucfirst(str_replace('_', ' ', $reason)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <x-ui.input type="date" name="date_from" label="Başlangıç Tarihi" value="{{ $filters['date_from'] ?? '' }}" />
            </div>
            <div class="col-md-3">
                <x-ui.input type="date" name="date_to" label="Bitiş Tarihi" value="{{ $filters['date_to'] ?? '' }}" />
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filtrele</button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card>
        @if($movements->count())
            <div class="table-responsive">
                <x-ui.table dense>
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Ambar</th>
                            <th>Ürün</th>
                            <th>Varyant</th>
                            <th>Yön</th>
                            <th class="text-end">Miktar</th>
                            <th class="text-end">Birim Maliyet</th>
                            <th>Neden</th>
                            <th>Not</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                            <tr>
                                <td>{{ $movement->moved_at?->format('d.m.Y H:i') }}</td>
                                <td>{{ $movement->warehouse?->name }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $movement->product?->name ?? '—' }}</div>
                                    <div class="text-muted small">{{ $movement->product?->sku }}</div>
                                </td>
                                <td>{{ $movement->variant?->sku ?? 'Varsayılan' }}</td>
                                <td>
                                    <span class="badge bg-{{ $movement->direction === 'in' ? 'success' : 'danger' }}">{{ strtoupper($movement->direction) }}</span>
                                </td>
                                <td class="text-end">{{ number_format((float) $movement->qty, 3, ',', '.') }}</td>
                                <td class="text-end">{{ number_format((float) ($movement->unit_cost ?? 0), 4, ',', '.') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $movement->reason)) }}</td>
                                <td class="text-muted small">{{ \Illuminate\Support\Str::limit($movement->note, 60) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            </div>
            <div class="mt-3">
                {{ $movements->links() }}
            </div>
        @else
            <x-ui.empty title="Hareket bulunamadı" description="Seçilen filtreler için kayıt yok." />
        @endif
    </x-ui.card>
@endsection
