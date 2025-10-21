@extends('layouts.admin')

@section('title', 'Şirket Ayarları')

@section('content')
<x-ui-page-header title="Şirket Ayarları" description="Şirket bilgilerinizi ve alan adlarınızı yönetin">
    <x-slot name="actions">
        @can('update', $company)
            <x-ui-button form="companySettingsForm" type="submit" variant="primary">
                Kaydet
            </x-ui-button>
        @endcan
    </x-slot>
</x-ui-page-header>

@if(session('status'))
    <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <x-ui-card>
            <form
                id="companySettingsForm"
                method="POST"
                action="{{ route('admin.settings.company.update') }}"
                data-company-settings-form
            >
                @csrf
                @method('PUT')

                <x-ui-input
                    name="name"
                    label="Şirket Adı"
                    :value="old('name', $company->name)"
                    required
                />

                <x-ui-input
                    class="mt-3"
                    name="theme_color"
                    label="Tema Rengi"
                    placeholder="#4f46e5"
                    :value="old('theme_color', $company->theme_color)"
                />

                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-semibold mb-0" for="companyLogoId">Şirket Logosu</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-action="open-logo-picker">
                                Sürücüden Seç
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-action="clear-logo">
                                Temizle
                            </button>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        name="logo_id"
                        id="companyLogoId"
                        value="{{ old('logo_id', $logoMedia?->id) }}"
                        data-company-logo-input
                    >

                    <div class="border rounded p-3 d-flex align-items-center gap-3 bg-light" data-company-logo-preview>
                        @if($logoMedia)
                            <x-ui-file-icon :ext="$logoMedia->ext" size="36" />
                            <div class="flex-grow-1">
                                <div class="fw-semibold text-ellipsis">{{ $logoMedia->original_name }}</div>
                                <div class="text-muted small">
                                    {{ $logoMedia->mime }} · {{ number_format(($logoMedia->size ?? 0) / 1024, 1, ',', '.') }} KB
                                </div>
                            </div>
                        @else
                            <div class="text-muted">Drive üzerinden bir logo seçin. Görsel formatlar önerilir.</div>
                        @endif
                    </div>

                    @error('logo_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                @can('update', $company)
                    <div class="mt-4 d-flex justify-content-end">
                        <x-ui-button type="submit" variant="primary">Kaydet</x-ui-button>
                    </div>
                @endcan
            </form>
        </x-ui-card>
    </div>

    <div class="col-lg-6">
        <x-ui-card>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">Alan Adları</h2>
                <span class="badge bg-light text-dark">Primary: {{ $company->domain }}</span>
            </div>

            <x-ui-alert type="info" class="mb-4">
                Birincil alan adını değiştirdiğinizde, yönetim paneline yeni alan adıyla erişmeniz gerekir.
            </x-ui-alert>

            @can('create', \App\Core\Support\Models\CompanyDomain::class)
                <form method="POST" action="{{ route('admin.settings.company.domains.store') }}" class="row g-2 align-items-end mb-4">
                    @csrf
                    <div class="col-md-7">
                        <x-ui-input
                            name="domain"
                            label="Yeni Alan Adı"
                            placeholder="example.com"
                            :value="old('domain')"
                        />
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4 pt-1">
                            <input class="form-check-input" type="checkbox" value="1" id="domainPrimary" name="is_primary" @checked(old('is_primary'))>
                            <label class="form-check-label" for="domainPrimary">Primary yap</label>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex justify-content-end">
                        <x-ui-button type="submit" class="w-100">Ekle</x-ui-button>
                    </div>
                </form>
            @endcan

            @error('domain')
                <x-ui-alert type="danger" class="mb-3">{{ $message }}</x-ui-alert>
            @enderror

            @if($domains->count())
                <x-ui-table dense>
                    <thead>
                        <tr>
                            <th scope="col">Alan Adı</th>
                            <th scope="col">Durum</th>
                            <th scope="col" class="text-end">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($domains as $domain)
                            <tr>
                                <td class="align-middle">{{ $domain->domain }}</td>
                                <td class="align-middle">
                                    @if($domain->is_primary)
                                        <x-ui-badge type="success" soft>Primary</x-ui-badge>
                                    @else
                                        <x-ui-badge type="secondary" soft>Alias</x-ui-badge>
                                    @endif
                                </td>
                                <td class="align-middle text-end">
                                    <div class="d-inline-flex gap-2">
                                        @can('update', $domain)
                                            @if(! $domain->is_primary)
                                                <form method="POST" action="{{ route('admin.settings.company.domains.make_primary', $domain) }}">
                                                    @csrf
                                                    <x-ui-button type="submit" variant="outline" size="sm">Primary Yap</x-ui-button>
                                                </form>
                                            @endif
                                        @endcan
                                        @can('delete', $domain)
                                            <form
                                                method="POST"
                                                action="{{ route('admin.settings.company.domains.destroy', $domain) }}"
                                                onsubmit="return confirm('Alan adını silmek istediğinize emin misiniz?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <x-ui-button type="submit" variant="danger" size="sm" @disabled($domain->is_primary && $domains->count() <= 1)>
                                                    Sil
                                                </x-ui-button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui-table>
            @else
                <x-ui-empty title="Alan adı bulunmuyor" description="Yeni alan adları ekleyerek alternatif giriş adresleri tanımlayın." />
            @endif
        </x-ui-card>
    </div>
</div>

<x-ui-modal id="companyLogoPickerModal" size="xl">
    <x-slot name="title">Drive'dan Logo Seç</x-slot>
    <div class="ratio ratio-16x9" data-drive-picker-container>
        <iframe
            src="{{ route('admin.drive.media.index', ['tab' => 'media_products', 'picker' => 1]) }}"
            title="Drive Logo Seçici"
            allow="autoplay"
            data-drive-picker-frame
        ></iframe>
    </div>
</x-ui-modal>
@endsection
