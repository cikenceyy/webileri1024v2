{{--
    Amaç: Sevkiyat listesini TableKit tablosu ve standart sayfa iskeletiyle sunmak.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Durum filtresi ve arama TableKit toolbar üzerinden sağlanıyor.
--}}
@extends('layouts.admin')

@section('title', 'Sevkiyatlar')
@section('module', 'Lojistik')
@section('page', 'Sevkiyatlar')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Sevkiyatlar"
            description="Hazırlanan, sevk edilen ve kapatılan sevkiyatları tek ekranda yönetin."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.logistics.shipments.create') }}" class="btn btn-primary">Yeni Sevkiyat</a>
            </x-slot>
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Sevkiyat Listesi" subtitle="Durumlara göre filtreleyin ve ilerlemeyi takip edin.">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz sevkiyat bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Belge veya müşteri ara"
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
