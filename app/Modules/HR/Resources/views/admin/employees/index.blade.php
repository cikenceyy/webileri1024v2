{{--
    Amaç: Personel dizinini TableKit ile tek tablo hattına almak ve filtreleri sadeleştirmek.
    İlişkiler: PROMPT-1, PROMPT-2, PROMPT-3 — TR Dil Birliği, Blade İskeleti, TableKit’e Geçiş.
    Notlar: Departman, ünvan ve durum filtreleri toolbar alanından yönetilir.
--}}
@extends('layouts.admin')

@section('title', 'Personel Dizini')
@section('module', 'HR')
@section('page', 'Personel Dizini')

@section('content')
    <x-ui-content class="py-4">
        <x-ui-page-header
            title="Personel Dizini"
            description="Tüm çalışan kayıtlarını departman, ünvan ve durum bazında görüntüleyin."
        >
            @can('create', \App\Modules\HR\Domain\Models\Employee::class)
                <x-slot name="actions">
                    <a href="{{ route('admin.hr.employees.create') }}" class="btn btn-primary">Yeni Personel</a>
                </x-slot>
            @endcan
        </x-ui-page-header>

        <div class="mt-4">
            <x-ui-card title="Kayıtlar" subtitle="Arama, filtreleme ve hızlı aksiyonlar">
                <x-tablekit.table
                    :config="$tableKitConfig"
                    :rows="$tableKitRows"
                    :paginator="$tableKitPaginator"
                    empty-text="Henüz personel kaydı bulunmuyor."
                >
                    <x-slot name="toolbar">
                        <x-tablekit.toolbar
                            :config="$tableKitConfig"
                            search-placeholder="Ad, kod veya e-posta ara"
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
