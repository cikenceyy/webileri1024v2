{{--
    Amaç: Ürün reçetelerini tek TableKit hattında yönetmek ve filtreleri sadeleştirmek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Aktif/pasif filtreleri toolbar alanına taşındı; tablo varsayılan 25 satır yükler.
--}}
@extends('layouts.admin')

@section('title', 'Ürün Reçeteleri')
@section('module', 'Production')
@section('page', 'Ürün Reçeteleri')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Ürün Reçeteleri"
            description="Aktif üretim reçetelerini kod, ürün ve versiyon bazında inceleyin."
        >
            @can('create', \App\Modules\Production\Domain\Models\Bom::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.production.boms.create') }}" class="btn btn-primary">Yeni Reçete</a>
                </x-slot>
            @endcan
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Kod, ürün ve durum bazlı filtreleme">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz reçete kaydı bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Reçete veya ürün ara"
                            :search-value="$filters['q'] ?? request('q')"
                        >
                            <button type="submit" class="tablekit__btn tablekit__btn--secondary">Listeyi Güncelle</button>
                        </x-tablekit.toolbar>
                    </x-slot>
                </x-tablekit.table>
            </x-ui-card>
        </div>
    </x-ui-content>
@endsection
