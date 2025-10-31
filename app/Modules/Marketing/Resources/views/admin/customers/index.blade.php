{{--
    Amaç: Müşteri listesinde TableKit tablosu ve ortak sayfa iskeletini kullanmak.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Arama ve durum filtreleri TableKit toolbar bileşenine taşındı.
--}}
@extends('layouts.admin')

@section('title', 'Müşteriler')
@section('module', 'Marketing')
@section('page', 'Müşteri Listesi')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Müşteri Listesi"
            description="Tüm müşterilerin durumunu, vadelerini ve fiyat listelerini yönetin."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.marketing.customers.create') }}" class="btn btn-primary">
                    Yeni Müşteri
                </a>
            </x-slot>
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Filtreleyin, sıralayın ve müşteri kayıtlarını dışa aktarın.">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz müşteri kaydı bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="İsim, e-posta veya telefon ile ara"
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
