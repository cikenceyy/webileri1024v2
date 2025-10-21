@extends('layouts.admin')

@section('title', 'Ambar Düzenle')

@section('content')
<x-ui-page-header title="Ambar Düzenle" description="{{ $warehouse->name }}" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.warehouses.update', $warehouse) }}">
        @csrf
        @method('PUT')
        @include('inventory::warehouses._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.warehouses.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Güncelle</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
