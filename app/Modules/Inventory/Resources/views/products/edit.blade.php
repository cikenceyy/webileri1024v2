@extends('layouts.admin')

@section('title', 'Ürünü Düzenle')

@section('content')
<x-ui-page-header title="Ürünü Düzenle" description="{{ $product->name }} kaydını güncelleyin" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.products.update', $product) }}" data-inventory-product-form data-drive-modal-id="drivePickerModal">
        @csrf
        @method('PUT')
        @include('inventory::products._form', [
            'drivePickerModalId' => 'drivePickerModal',
            'drivePickerFolder' => \App\Modules\Drive\Domain\Models\Media::CATEGORY_MEDIA_PRODUCTS,
        ])

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.products.index') }}" class="btn btn-outline-secondary">Geri</a>
            <x-ui-button type="submit" variant="primary">Güncelle</x-ui-button>
        </div>
    </form>
</x-ui-card>

<x-ui-modal id="drivePickerModal" size="xl">
    <x-slot name="title">Drive'dan Görsel Seç</x-slot>
    <div class="ratio ratio-16x9" data-drive-picker-container>
        <iframe
            src="{{ route('admin.drive.media.index', ['tab' => 'media_products', 'picker' => 1]) }}"
            title="Drive Görsel Seçici"
            allow="autoplay"
            data-drive-picker-frame
            data-drive-picker-src="{{ route('admin.drive.media.index', ['picker' => 1]) }}"
        ></iframe>
    </div>
</x-ui-modal>
@endsection
