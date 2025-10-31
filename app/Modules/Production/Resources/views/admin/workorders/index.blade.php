{{--
    Amaç: Üretim iş emirlerini TableKit tablosu ile tutarlı iskelette listelemek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Basit filtreler toolbar alanına taşındı; liste varsayılan olarak 25 kayıt gösterir.
--}}
@extends('layouts.admin')

@section('title', 'İş Emirleri')
@section('module', 'Production')
@section('page', 'İş Emirleri')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="İş Emirleri"
            description="Üretim planındaki iş emirlerini durum, termin ve ürün kırılımında takip edin."
        >
            @can('create', \App\Modules\Production\Domain\Models\WorkOrder::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.production.workorders.create') }}" class="btn btn-primary">Yeni İş Emri</a>
                </x-slot>
            @endcan
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Filtreleyin, arayın ve durumları yönetin">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    :state="$tableKitState ?? []"
                    empty-text="Henüz iş emri kaydı bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="İş emri numarası ara"
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
