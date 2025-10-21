@extends('layouts.admin')

@section('title', 'Yeni Ambar')

@section('content')
<x-ui-page-header title="Yeni Ambar" description="Depo ekleyin" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.warehouses.store') }}">
        @csrf
        @include('inventory::warehouses._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.warehouses.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Kaydet</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
