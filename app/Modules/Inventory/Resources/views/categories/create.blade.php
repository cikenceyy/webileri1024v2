@extends('layouts.admin')

@section('title', 'Yeni Kategori')

@section('content')
<x-ui-page-header title="Yeni Kategori" description="Ürün kategorisi ekleyin" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.categories.store') }}">
        @csrf
        @include('inventory::categories._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.categories.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Kaydet</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
