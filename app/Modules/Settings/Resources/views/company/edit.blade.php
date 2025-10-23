@extends('layouts.admin')

@section('title', 'Şirket Bilgileri')

@section('content')
<x-ui-page-header title="Şirket Bilgileri" description="Şirket kimlik ve iletişim bilgilerinizi yönetin">
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

<x-ui-card>
    <form
        id="companySettingsForm"
        method="POST"
        action="{{ route('admin.settings.company.update') }}"
        data-company-settings-form
    >
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-6">
                <x-ui-input
                    name="name"
                    label="Ticari Ünvan"
                    :value="old('name', $settings->name)"
                    required
                />

                <x-ui-input
                    class="mt-3"
                    name="legal_title"
                    label="Resmi Ünvan"
                    :value="old('legal_title', $settings->legal_title)"
                />

                <div class="row mt-3 g-3">
                    <div class="col-md-6">
                        <x-ui-input
                            name="tax_office"
                            label="Vergi Dairesi"
                            :value="old('tax_office', $settings->tax_office)"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-ui-input
                            name="tax_number"
                            label="Vergi Numarası"
                            :value="old('tax_number', $settings->tax_number)"
                        />
                    </div>
                </div>

                <x-ui-input
                    class="mt-3"
                    name="website"
                    label="Web Sitesi"
                    placeholder="https://example.com"
                    :value="old('website', $settings->website)"
                />

                <div class="row mt-3 g-3">
                    <div class="col-md-6">
                        <x-ui-input
                            name="email"
                            label="E-posta"
                            type="email"
                            placeholder="info@example.com"
                            :value="old('email', $settings->email)"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-ui-input
                            name="phone"
                            label="Telefon"
                            placeholder="+90 555 000 00 00"
                            :value="old('phone', $settings->phone)"
                        />
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <label class="form-label fw-semibold" for="companyLogoId">Şirket Logosu</label>
                @php
                    $logoPickerKey = 'company-logo';
                    $logoPickerData = $logoMedia ? [
                        'id' => $logoMedia->id,
                        'name' => $logoMedia->original_name,
                        'original_name' => $logoMedia->original_name,
                        'mime' => $logoMedia->mime,
                        'ext' => $logoMedia->ext,
                        'size' => $logoMedia->size,
                        'path' => $logoMedia->path,
                        'url' => route('admin.drive.media.download', $logoMedia),
                        'category' => $logoMedia->category,
                    ] : null;
                @endphp
                <div class="d-flex gap-2 mb-3">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        data-drive-picker-open
                        data-drive-picker-key="{{ $logoPickerKey }}"
                        data-drive-picker-modal="companyLogoPickerModal"
                        data-drive-picker-folder="{{ \App\Modules\Drive\Domain\Models\Media::CATEGORY_MEDIA_PRODUCTS }}"
                    >
                        Sürücüden Seç
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-danger"
                        data-drive-picker-clear
                        data-drive-picker-key="{{ $logoPickerKey }}"
                    >
                        Temizle
                    </button>
                </div>

                <input
                    type="hidden"
                    name="logo_id"
                    id="companyLogoId"
                    value="{{ old('logo_id', $logoMedia?->id) }}"
                    data-drive-picker-input
                    data-drive-picker-key="{{ $logoPickerKey }}"
                >

                <div class="border rounded p-3 d-flex align-items-center gap-3 bg-light">
                    <div
                        class="w-100"
                        data-drive-picker-preview
                        data-drive-picker-key="{{ $logoPickerKey }}"
                        data-drive-picker-template="inventory-media"
                        data-empty-message="Drive üzerinden bir logo seçin."
                        data-drive-picker-state="{{ $logoPickerData ? 'filled' : 'empty' }}"
                        data-drive-picker-value='@json($logoPickerData)'
                    >
                        @if($logoMedia)
                            <div class="inventory-media-preview">
                                <x-ui-file-icon :ext="$logoMedia->ext" size="36" />
                                <div class="inventory-media-preview__meta">
                                    <div class="inventory-media-preview__name">{{ $logoMedia->original_name }}</div>
                                    <div class="inventory-media-preview__desc">{{ $logoMedia->mime }} · {{ number_format(($logoMedia->size ?? 0) / 1024, 1, ',', '.') }} KB</div>
                                </div>
                            </div>
                        @else
                            <div class="inventory-media-empty">Drive üzerinden bir logo seçin.</div>
                        @endif
                    </div>
                </div>

                @error('logo_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        @can('update', $company)
            <div class="mt-4 d-flex justify-content-end">
                <x-ui-button type="submit" variant="primary">Kaydet</x-ui-button>
            </div>
        @endcan
    </form>
</x-ui-card>

<x-ui-modal id="companyLogoPickerModal" size="xl">
    <x-slot name="title">Drive'dan Logo Seç</x-slot>
    <div class="ratio ratio-16x9" data-drive-picker-container>
        <iframe
            src="{{ route('admin.drive.media.index', ['tab' => 'media_products', 'picker' => 1]) }}"
            title="Drive Logo Seçici"
            allow="autoplay"
            data-drive-picker-frame
            data-drive-picker-src="{{ route('admin.drive.media.index', ['picker' => 1]) }}"
        ></iframe>
    </div>
</x-ui-modal>
@endsection
