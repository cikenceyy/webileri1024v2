{{--
    Amaç: TableKit performans metriklerini ortak Blade iskeleti ve TableKit tablo bileşeniyle sunmak.
    İlişkiler: PROMPT-1 — TR Dil Birliği, PROMPT-2 — Blade İskeleti.
    Notlar: Filtre formu, özet kartları ve tablo bölümü ortak başlık/toolbar şemasına göre düzenlendi.
--}}
@extends('layouts.admin')

@section('title', 'Tablo Metrikleri')

@php
    use App\Core\Support\TableKit\TableConfig;
    use Illuminate\Support\Str;

    $tableKitConfig = TableConfig::make([
        ['key' => 'table_key', 'label' => 'Tablo', 'type' => 'text', 'sortable' => true, 'filterable' => true],
        ['key' => 'p95_total_time_ms', 'label' => 'P95 (ms)', 'type' => 'number', 'sortable' => true, 'options' => ['precision' => 0]],
        ['key' => 'avg_total_time_ms', 'label' => 'Ortalama (ms)', 'type' => 'number', 'sortable' => true, 'options' => ['precision' => 0]],
        ['key' => 'cache_hit_ratio', 'label' => 'Önbellek %', 'type' => 'text'],
        ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
    ], [
        'data_count' => $entries->count(),
        'default_sort' => '-p95_total_time_ms',
        'id' => 'tablekit-metrics',
    ]);

    $tableKitRows = $entries->map(function ($entry) {
        $cacheRatio = (float) ($entry->cache_hit_ratio ?? 0);

        return [
            'id' => 'metric-' . Str::slug($entry->table_key ?? Str::uuid()->toString()),
            'cells' => [
                'table_key' => $entry->table_key ?? '—',
                'p95_total_time_ms' => $entry->p95_total_time_ms ?? 0,
                'avg_total_time_ms' => $entry->avg_total_time_ms ?? 0,
                'cache_hit_ratio' => [
                    'raw' => $cacheRatio,
                    'display' => number_format($cacheRatio, 2, ',', '.') . '%',
                    'html' => e(number_format($cacheRatio, 2, ',', '.') . '%'),
                ],
                'actions' => [],
            ],
        ];
    })->values()->all();
@endphp

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="TableKit Performansı"
            description="En sık kullanılan listelerin yanıt sürelerini ve önbellek etkisini izleyin."
        />

        <div class="d-flex flex-column gap-4 mt-4">
            <x-ui-card title="Filtreler" subtitle="Tarih ve tablo bazlı süzme">
                <form method="get" class="row g-3" aria-label="Metrik filtresi">
                    <div class="col-md-4">
                        <label for="metric-date" class="form-label fw-semibold">Tarih</label>
                        <input
                            id="metric-date"
                            type="date"
                            name="date"
                            value="{{ $selectedDate->toDateString() }}"
                            class="form-control"
                        >
                    </div>
                    <div class="col-md-4">
                        <label for="metric-table" class="form-label fw-semibold">Tablo Anahtarı</label>
                        <input
                            id="metric-table"
                            type="text"
                            name="table_key"
                            value="{{ $selectedTableKey }}"
                            class="form-control"
                            placeholder="örn. orders.index"
                        >
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end justify-content-md-end">
                        <div class="d-flex gap-2 w-100 justify-content-md-end">
                            <a href="{{ route('admin.metrics.tablekit') }}" class="btn btn-outline-secondary flex-grow-1 flex-md-grow-0">Temizle</a>
                            <button type="submit" class="btn btn-primary flex-grow-1 flex-md-grow-0">Uygula</button>
                        </div>
                    </div>
                </form>
            </x-ui-card>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <x-ui-card title="En Çok Kullanılanlar" subtitle="İstek adedi">
                        <ul class="list-unstyled mb-0" aria-live="polite">
                            @forelse($topTables as $entry)
                                <li class="mb-2 d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">{{ $entry->table_key }}</span>
                                    <span class="text-muted small">{{ $entry->request_count }} istek</span>
                                </li>
                            @empty
                                <li class="text-muted">Kayıt yok</li>
                            @endforelse
                        </ul>
                    </x-ui-card>
                </div>
                <div class="col-md-6 col-lg-8">
                    <x-ui-card title="Kayıtlar" subtitle="Yanıt süreleri">
                        <x-tablekit.table
                            :config="$tableKitConfig"
                            :rows="$tableKitRows"
                            empty-text="Kayıt yok"
                        >
                            <x-slot name="toolbar">
                                <x-tablekit.toolbar :config="$tableKitConfig" search-placeholder="Tablo ara…" />
                            </x-slot>
                        </x-tablekit.table>
                    </x-ui-card>
                </div>
            </div>
        </div>
    </x-ui-content>
@endsection
