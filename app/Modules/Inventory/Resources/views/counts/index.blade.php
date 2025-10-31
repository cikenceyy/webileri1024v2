{{--
    Amaç: Stok sayımlarını TableKit tablosu ve standart sayfa iskeletiyle listelemek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Liste TableKit’e taşındı, sayfa başlığı ve aksiyon korunarak güncellendi.
--}}
@extends('layouts.admin')

@section('title', 'Stok Sayımları')
@section('module', 'Envanter')
@section('page', 'Stok Sayımları')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Stok Sayımları"
            description="Depo bazlı sayımları ve mutabakat durumlarını tek tablo üzerinden takip edin."
        >
            @can('create', \App\Modules\Inventory\Domain\Models\StockCount::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.inventory.counts.create') }}" class="btn btn-primary btn-sm">Yeni Sayım</a>
                </x-slot>
            @endcan
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Sayım Listesi" subtitle="Belge, depo ve duruma göre filtreleyin.">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz sayım kaydı yok."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Belge veya depo ara"
                            :search-value="request('q')"
                        >
                            <button type="submit" class="tablekit__btn tablekit__btn--secondary">
                                Listeyi Güncelle
                            </button>
                        </x-tablekit.toolbar>
                    </x-slot>
                </x-tablekit.table>
            </x-ui-card>
        </div>
    </x-ui-content>
@endsection
