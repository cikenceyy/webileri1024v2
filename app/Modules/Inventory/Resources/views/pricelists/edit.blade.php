@extends('layouts.admin')

@section('title', 'Fiyat Listesi Düzenle')

@section('content')
<x-ui-page-header title="Fiyat Listesi Düzenle" description="{{ $priceList->name }}" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.pricelists.update', $priceList) }}">
        @csrf
        @method('PUT')
        @include('inventory::pricelists._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.pricelists.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Güncelle</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
