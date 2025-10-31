{{--
    Amaç: Satınalma siparişlerini TableKit ile ortak tablo hattında sunmak ve filtreleri sadeleştirmek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Durum ve tarih filtreleri toolbar alanına taşındı; grid yapısı kart + tablo biçiminde düzenlendi.
--}}
@extends('layouts.admin')

@section('title', 'Satınalma Siparişleri')
@section('module', 'Procurement')
@section('page', 'Satınalma Siparişleri')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Satınalma Siparişleri"
            description="Tedarik siparişlerinin durumunu, toplamlarını ve teslimat süreçlerini izleyin."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.procurement.pos.index') }}" class="btn btn-outline-secondary">Listeyi Yenile</a>
                <a href="{{ route('admin.procurement.pos.create') }}" class="btn btn-primary">Yeni Sipariş</a>
            </x-slot>
        </x-ui-page-header>

        @if (session('status'))
            <x-ui-alert variant="success" class="mt-3">{{ session('status') }}</x-ui-alert>
        @endif

        <div class="mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Durum, tarih ve tedarikçi bazlı filtreleme">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz satınalma siparişi bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Sipariş numarası ara"
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
