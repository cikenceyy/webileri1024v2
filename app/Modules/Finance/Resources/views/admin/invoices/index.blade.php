{{--
    Amaç: Satış faturalarını TableKit bileşeniyle tekil tablo hattında listelemek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Eski filtre formu kaldırıldı; arama ve durum filtreleri TableKit toolbar üzerinden yönetiliyor.
--}}
@extends('layouts.admin')

@section('title', 'Satış Faturaları')
@section('module', 'Finans')
@section('page', 'Satış Faturaları')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Satış Faturaları"
            description="Taslak, düzenlenmiş ve tahsil edilmiş tüm faturaları tek tablo üzerinden yönetin."
        >
            @can('create', \App\Modules\Finance\Domain\Models\Invoice::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.finance.invoices.create') }}" class="btn btn-primary">Yeni Fatura</a>
                </x-slot>
            @endcan
        </x-ui-page-header>

        <div class="row g-3 mt-1">
            <div class="col-sm-6 col-lg-3">
                <x-ui-card class="h-100" title="Taslak">
                    <p class="display-6 fw-semibold mb-0">{{ $metrics['draft'] }}</p>
                </x-ui-card>
            </div>
            <div class="col-sm-6 col-lg-3">
                <x-ui-card class="h-100" title="Düzenlendi">
                    <p class="display-6 fw-semibold mb-0">{{ $metrics['issued'] }}</p>
                </x-ui-card>
            </div>
            <div class="col-sm-6 col-lg-3">
                <x-ui-card class="h-100" title="Kısmi Tahsil">
                    <p class="display-6 fw-semibold mb-0">{{ $metrics['partially_paid'] }}</p>
                </x-ui-card>
            </div>
            <div class="col-sm-6 col-lg-3">
                <x-ui-card class="h-100" title="Tahsil Edildi">
                    <p class="display-6 fw-semibold mb-0">{{ $metrics['paid'] }}</p>
                </x-ui-card>
            </div>
        </div>

        <div class="mt-4">
            <x-ui-card title="Fatura Listesi" subtitle="Arama, filtreleme ve dışa aktarma">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Kayıtlı fatura bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Fatura veya müşteri ara"
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
