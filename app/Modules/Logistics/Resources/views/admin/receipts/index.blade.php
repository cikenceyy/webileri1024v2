{{--
    Amaç: Mal kabul kayıtlarını TableKit tablosu ve standart iskelet ile sunmak.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Durum, depo ve tedarikçi filtreleri TableKit toolbar üzerinden sağlanır.
--}}
@extends('layouts.admin')

@section('title', 'Mal Kabulleri')
@section('module', 'Lojistik')
@section('page', 'Mal Kabulleri')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Mal Kabul İşlemleri"
            description="Taslak, teslim alınan ve mutabık mal kabullerini tek tablo üzerinden takip edin."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.logistics.receipts.create') }}" class="btn btn-primary">Yeni Mal Kabul</a>
            </x-slot>
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Mal Kabul Listesi" subtitle="Belge, tedarikçi ve depoya göre filtreleyin.">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Kayıtlı mal kabul bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Belge veya tedarikçi ara"
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
