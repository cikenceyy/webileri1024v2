@extends('layouts.admin')

@section('title', 'Yeni Ürün')

@section('content')
<x-ui-page-header title="Yeni Ürün" description="Envantere yeni bir ürün ekleyin" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.products.store') }}" data-inventory-product-form data-drive-modal-id="drivePickerModal">
        @csrf
        @include('inventory::products._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.products.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Kaydet</x-ui-button>
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
        ></iframe>
    </div>
</x-ui-modal>
@endsection
