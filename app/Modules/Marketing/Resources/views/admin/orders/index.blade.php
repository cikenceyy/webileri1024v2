{{--
    Amaç: Satış siparişlerini TableKit tablosu ve standart sayfa iskeleti ile listelemek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Filtre ve arama kontrolleri TableKit toolbar alanına taşındı.
--}}
@extends('layouts.admin')

@section('title', 'Satış Siparişleri')
@section('module', 'Marketing')
@section('page', 'Satış Siparişleri')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Satış Siparişleri"
            description="Siparişlerin durumunu, terminlerini ve müşteri ilişkilerini yönetin."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.marketing.orders.create') }}" class="btn btn-primary">
                    Yeni Sipariş
                </a>
            </x-slot>
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Son sipariş hareketleri">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz sipariş kaydı bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Sipariş no veya müşteri ara"
                            :search-value="$filters['q'] ?? request('q')"
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
