@extends('layouts.admin')

@section('title', 'Depolar')
@section('module', 'Inventory')

@section('content')
    <section class="inv-warehouse inv-warehouse--split">
        <header class="inv-warehouse__header">
            <h1 class="inv-warehouse__title">Depo & Raf Yönetimi</h1>
            <div class="inv-warehouse__actions">
                @can('create', \App\Modules\Inventory\Domain\Models\Warehouse::class)
                    <a href="{{ route('admin.inventory.warehouses.create') }}" class="btn btn-primary btn-sm">Yeni Depo</a>
                @endcan
            </div>
        </header>
        <div class="inv-warehouse__layout">
            <aside class="inv-warehouse__sidebar">
                <form method="get" class="inv-warehouse__filters">
                    <label class="form-label" for="warehouse_id">Depo</label>
                    <select name="warehouse_id" id="warehouse_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Tümü</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(optional($selectedWarehouse)->id === $warehouse->id)>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @if ($selectedWarehouse)
                        <label class="form-label mt-3" for="bin_id">Raf / Bin</label>
                        <select name="bin_id" id="bin_id" class="form-select" onchange="this.form.submit()">
                            <option value="">Depodaki tüm raflar</option>
                            @foreach ($selectedWarehouse->bins as $bin)
                                <option value="{{ $bin->id }}" @selected(optional($selectedBin)->id === $bin->id)>
                                    {{ $bin->code }} — {{ $bin->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <label class="form-label mt-3" for="search">Ürün ara</label>
                    <input type="search" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="SKU veya ürün adı">
                    <button type="submit" class="btn btn-outline-secondary btn-sm mt-2">Filtrele</button>
                </form>
            </aside>
            <div class="inv-warehouse__content">
                <div class="inv-warehouse__summary">
                    <div>
                        <p class="inv-warehouse__summary-label">Seçili Depo</p>
                        <p class="inv-warehouse__summary-value">{{ $selectedWarehouse?->name ?? 'Tümü' }}</p>
                    </div>
                    <div>
                        <p class="inv-warehouse__summary-label">Toplam Miktar</p>
                        <p class="inv-warehouse__summary-value">
                            @php
                                $total = $selectedWarehouse ? ($stats->get($selectedWarehouse->id)->total_qty ?? 0) : $stats->sum('total_qty');
                            @endphp
                            {{ number_format($total, 2) }}
                        </p>
                    </div>
                </div>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Raf</th>
                            <th class="text-end">Miktar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockItems as $row)
                            <tr>
                                <td>{{ $row['product']?->name ?? '—' }}<br><span class="text-muted">{{ $row['product']?->sku ?? '' }}</span></td>
                                <td>
                                    @if ($row['bin'])
                                        {{ $row['bin']->code }} — {{ $row['bin']->name }}
                                    @else
                                        Genel stok
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($row['qty'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Kayıt bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
