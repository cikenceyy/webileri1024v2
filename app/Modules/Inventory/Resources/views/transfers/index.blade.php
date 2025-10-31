{{--
    Amaç: Stok transferlerini TableKit tablosu ile standart iskelette göstermek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Yeni transfer butonu korunarak tablo TableKit bileşenine geçirildi.
--}}
@extends('layouts.admin')

@section('title', 'Stok Transferleri')
@section('module', 'Envanter')
@section('page', 'Stok Transferleri')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Stok Transferleri"
            description="Depolar arası stok hareketlerini listeleyin, durumlarına göre filtreleyin."
        >
            @can('create', \App\Modules\Inventory\Domain\Models\StockTransfer::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.inventory.transfers.create') }}" class="btn btn-primary btn-sm">Yeni Transfer</a>
                </x-slot>
            @endcan
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Transfer Listesi" subtitle="Belge, depo ve duruma göre arayın.">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz transfer kaydı yok."
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
