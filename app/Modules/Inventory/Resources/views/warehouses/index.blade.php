{{--
    Amaç: Depo stoklarını TableKit tablosu ve mevcut filtre paneli ile sunmak.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Sol taraftaki filtre paneli korunarak tablo TableKit bileşenine geçirildi.
--}}
@extends('layouts.admin')

@section('title', 'Depolar')
@section('module', 'Envanter')
@section('page', 'Depo Yönetimi')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Depo & Raf Yönetimi"
            description="Depolardaki stok dağılımını, raf bazlı adetleri ve hızlı filtreleri görüntüleyin."
        />

        <section class="inv-warehouse inv-warehouse--split mt-4">
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

                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Bu filtrelerle stok bulunamadı."
                    dense="true"
                >
                    <x-slot name="toolbar">
                        <div class="d-flex align-items-center justify-content-between small text-muted px-3 py-2">
                            <span>Toplam {{ $tableKitConfig->dataCount() ?? count($tableKitRows ?? []) }} kayıt</span>
                        </div>
                    </x-slot>
                </x-tablekit.table>
            </div>
        </section>
    </x-ui-content>
@endsection
