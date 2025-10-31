{{--
    Amaç: Nakit giriş/çıkışlarını TableKit üzerinde tek tabloda sunmak ve filtrelerini sadeleştirmek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Yön ve tarih filtreleri toolbar alanına taşındı; metinler tamamen Türkçeleştirildi.
--}}
@extends('layouts.admin')

@section('title', 'Nakit Defteri')
@section('module', 'Finans')
@section('page', 'Nakit Defteri')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Nakit Defteri"
            description="Nakit giriş ve çıkış hareketlerini tek bakışta takip edin."
        >
            @can('create', \App\Modules\Finance\Domain\Models\CashbookEntry::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.finance.cashbook.create') }}" class="btn btn-primary">Yeni Kayıt</a>
                </x-slot>
            @endcan
        </x-ui-page-header>

        @if (session('status'))
            <x-ui-alert variant="success" class="mt-3">{{ session('status') }}</x-ui-alert>
        @endif

        <div class="mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Arama, yön ve tarih filtreleri">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz nakit hareketi kaydedilmedi."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Hesap veya referans ara"
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
