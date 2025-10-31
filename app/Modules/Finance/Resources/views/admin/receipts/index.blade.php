{{--
    Amaç: Tahsilat kayıtlarını TableKit tablosu ve standart sayfa iskeleti ile sunmak.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Arama ve filtreler TableKit toolbar üzerinden sağlanır.
--}}
@extends('layouts.admin')

@section('title', 'Tahsilatlar')
@section('module', 'Finans')
@section('page', 'Tahsilatlar')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Tahsilatlar"
            description="Müşteri ödemelerini kaydedin, faturalarla eşleştirin ve hareketleri takip edin."
        >
            @can('create', \App\Modules\Finance\Domain\Models\Receipt::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.finance.receipts.create') }}" class="btn btn-primary">Yeni Tahsilat</a>
                </x-slot>
            @endcan
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Tahsilat Listesi" subtitle="Arama, filtreleme ve dışa aktarma">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Kayıtlı tahsilat bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Makbuz veya müşteri ara"
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
