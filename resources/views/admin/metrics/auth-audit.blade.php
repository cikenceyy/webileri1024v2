{{--
    Amaç: Yetki reddi denemelerini standart Blade iskeleti ve TableKit ile raporlamak.
    İlişkiler: PROMPT-1 — TR Dil Birliği, PROMPT-2 — Blade İskeleti.
    Notlar: Sayfa başlığı, uyarı bölümü ve tablo toolbar'ı tek şemaya göre düzenlendi.
--}}
@extends('layouts.admin')

@section('title', 'Yetki Denetim Kayıtları')
@section('module', 'Admin')
@section('page', 'Yetki Denetim Kayıtları')

@php
    use App\Core\Support\TableKit\TableConfig;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Support\Str;

    $paginator = $audits;
    $auditItems = $paginator instanceof LengthAwarePaginator ? $paginator->getCollection() : collect($audits);

    $tableKitConfig = TableConfig::make([
        ['key' => 'created_at', 'label' => 'Tarih', 'type' => 'date', 'sortable' => true, 'filterable' => true, 'options' => ['format' => 'd.m.Y H:i']],
        ['key' => 'user', 'label' => 'Kullanıcı', 'type' => 'text', 'filterable' => true],
        ['key' => 'action', 'label' => 'İşlem', 'type' => 'text', 'filterable' => true],
        ['key' => 'resource', 'label' => 'Kaynak', 'type' => 'text'],
        ['key' => 'ip_address', 'label' => 'IP', 'type' => 'text'],
        ['key' => 'result', 'label' => 'Sonuç', 'type' => 'badge'],
        ['key' => 'actions', 'label' => 'İşlemler', 'type' => 'actions'],
    ], [
        'data_count' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : $auditItems->count(),
        'default_sort' => '-created_at',
        'id' => 'auth-denetim',
    ]);

    $tableKitRows = $auditItems->map(function ($audit) {
        $userLabel = $audit->user_id ? ('#' . $audit->user_id) : 'Anonim';

        return [
            'id' => 'audit-' . ($audit->id ?? Str::uuid()->toString()),
            'cells' => [
                'created_at' => $audit->created_at,
                'user' => $userLabel,
                'action' => $audit->action ?? '—',
                'resource' => $audit->resource ?? '—',
                'ip_address' => $audit->ip_address ?? '—',
                'result' => Str::lower((string) ($audit->result ?? 'bilinmiyor')),
                'actions' => [],
            ],
        ];
    })->values()->all();

    $tableKitPaginator = $paginator instanceof LengthAwarePaginator ? $paginator : null;
@endphp

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Yetki Denetim Kayıtları"
            description="İzinsiz eylem denemelerini şirket bazında izleyin."
        >
            <x-slot name="actions">
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge text-bg-light">Tenant filtreli</span>
                </div>
            </x-slot>
        </x-ui-page-header>

        <div class="d-flex flex-column gap-4 mt-4">
            <x-ui-card title="Bilgilendirme" subtitle="Kritik uyarılar">
                <x-ui-alert variant="warning" tone="soft" icon="bi bi-shield-lock" class="mb-0">
                    Domain veya kullanıcı değişiklikleri yalnızca CLI/Superadmin tarafından yapılabilir; bu kayıtlar sadece bilgilendirme amaçlıdır.
                </x-ui-alert>
            </x-ui-card>

            <x-ui-card title="Kayıtlar" subtitle="Denetim günlüğü">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz kayıt yok."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar :config="$tableKitConfig" search-placeholder="İşlem ara…" />
                    </x-slot>
                </x-tablekit.table>
                <p class="small text-muted mt-3 mb-0">Sonuçlar tenant bazlı filtrelenmiştir.</p>
            </x-ui-card>
        </div>
    </x-ui-content>
@endsection
