@extends('layouts.admin')

@section('title', 'Sevkiyatı Düzenle')

@section('module', 'logistics')

@section('content')
<x-ui.page-header title="Sevkiyatı Düzenle" description="Sevkiyat bilgilerini güncelleyin" />

<x-ui.card>
    <form method="POST" action="{{ route('admin.logistics.shipments.update', $shipment) }}">
        @csrf
        @method('PUT')
        @include('logistics::shipments._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.logistics.shipments.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui.button type="submit" variant="primary">Kaydet</x-ui.button>
        </div>
    </form>
</x-ui.card>
@endsection
