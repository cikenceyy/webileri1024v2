{{--
    Amaç: Dışa aktarımların geçmişini tekil sayfa iskeleti ve TableKit ile sunmak.
    İlişkiler: PROMPT-1 — TR Dil Birliği, PROMPT-2 — Blade İskeleti.
    Notlar: TableKit yapılandırması ile toolbar/section düzeni standartlaştırıldı.
--}}
@extends('layouts.admin')

@section('title', 'Dışa Aktarım Geçmişi')

@php
    use App\Core\Exports\Models\TableExport;
    use App\Core\Support\TableKit\TableConfig;

    $statusLabels = [
        TableExport::STATUS_PENDING => 'Bekliyor',
        TableExport::STATUS_RUNNING => 'İşleniyor',
        TableExport::STATUS_DONE => 'Tamamlandı',
        TableExport::STATUS_FAILED => 'Hatalı',
    ];

    $tableKitConfig = TableConfig::make([
        ['key' => 'id', 'label' => '#', 'type' => 'number', 'sortable' => true],
        ['key' => 'table_key', 'label' => 'Tablo', 'type' => 'text', 'filterable' => true],
        ['key' => 'format', 'label' => 'Format', 'type' => 'enum', 'enum' => ['csv' => 'CSV', 'xlsx' => 'XLSX'], 'filterable' => true],
        ['key' => 'status', 'label' => 'Durum', 'type' => 'badge', 'enum' => array_merge($statusLabels, ['unknown' => 'Bilinmiyor']), 'filterable' => true],
        ['key' => 'row_count', 'label' => 'Satır', 'type' => 'number', 'sortable' => true],
        ['key' => 'progress', 'label' => 'İlerleme', 'type' => 'text'],
        ['key' => 'created_at', 'label' => 'Oluşturuldu', 'type' => 'date', 'sortable' => true, 'options' => ['format' => 'd.m.Y H:i']],
        ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
    ], [
        'data_count' => $exports->count(),
        'default_sort' => '-created_at',
        'id' => 'exports-gecmisi',
    ]);

    $tableKitRows = $exports->map(function (TableExport $export) use ($statusLabels) {
        $progress = (int) ($export->progress ?? 0);

        return [
            'id' => 'export-' . $export->id,
            'cells' => [
                'id' => [
                    'raw' => $export->id,
                    'display' => (string) $export->id,
                    'html' => '<span class="fw-semibold">#' . e($export->id) . '</span>',
                ],
                'table_key' => $export->table_key,
                'format' => strtolower((string) $export->format),
                'status' => array_key_exists($export->status, $statusLabels) ? $export->status : 'unknown',
                'row_count' => $export->row_count ?? 0,
                'progress' => [
                    'raw' => $progress,
                    'display' => $progress . '%',
                    'html' => '<div class="progress progress-sm" role="progressbar" aria-valuenow="' . e($progress)
                        . '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: '
                        . e($progress) . '%"></div></div>',
                ],
                'created_at' => $export->created_at,
                'actions' => $export->status === TableExport::STATUS_DONE && $export->file_path ? [
                    [
                        'label' => 'İndir',
                        'href' => route('admin.exports.download', $export),
                        'variant' => 'secondary',
                    ],
                ] : [],
            ],
        ];
    })->values()->all();
@endphp

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Dışa Aktarım Geçmişi"
            description="Tamamlanan ve devam eden dışa aktarmaları buradan takip edin."
        />

        <div class="d-flex flex-column gap-4 mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Son 50 dışa aktarma isteği">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    empty-text="Henüz dışa aktarım bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar :config="$tableKitConfig" search-placeholder="Export ara…">
                            <a href="{{ request()->fullUrl() }}" class="tablekit__btn tablekit__btn--ghost">
                                Yenile
                            </a>
                        </x-tablekit.toolbar>
                    </x-slot>
                </x-tablekit.table>
            </x-ui-card>
        </div>
    </x-ui-content>
@endsection
