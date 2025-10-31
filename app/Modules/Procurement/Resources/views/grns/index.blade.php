{{--
    Amaç: Mal kabul kayıtlarını TableKit hattına almak ve filtreleri sadeleştirmek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Durum ve teslim tarihi filtreleri toolbar üzerinden yönetilir.
--}}
@extends('layouts.admin')

@section('title', 'Mal Kabul Kayıtları')
@section('module', 'Procurement')
@section('page', 'Mal Kabul Kayıtları')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Mal Kabul Kayıtları"
            description="Satınalma siparişlerinin teslimat ilerlemesini izleyin."
        >
            <x-slot name="actions">
                <a href="{{ route('admin.procurement.grns.create') }}" class="btn btn-primary">Yeni Mal Kabulü</a>
            </x-slot>
        </x-ui-page-header>

        @if (session('status'))
            <x-ui-alert variant="success" class="mt-3">{{ session('status') }}</x-ui-alert>
        @endif

        <div class="mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Durum ve teslim tarihi bazlı filtreleme">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz mal kabul kaydı bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="GRN veya sipariş ara"
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
