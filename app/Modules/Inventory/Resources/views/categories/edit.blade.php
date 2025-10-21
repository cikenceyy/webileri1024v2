@extends('layouts.admin')

@section('title', 'Kategori Düzenle')

@section('content')
<x-ui.page-header title="Kategori Düzenle" description="{{ $category->name }}" />

<x-ui.card>
    <form method="POST" action="{{ route('admin.inventory.categories.update', $category) }}">
        @csrf
        @method('PUT')
        @include('inventory::categories._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.categories.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui.button type="submit" variant="primary">Güncelle</x-ui.button>
        </div>
    </form>
</x-ui.card>
@endsection
