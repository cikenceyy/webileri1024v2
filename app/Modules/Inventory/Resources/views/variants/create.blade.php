@extends('layouts.admin')

@section('title', 'Yeni Varyant')

@section('content')
<x-ui-page-header title="Yeni Varyant" description="{{ $product->name }} için varyant" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.products.variants.store', $product) }}" data-variant-form>
        @csrf
        @include('inventory::variants._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.products.variants.index', $product) }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Kaydet</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
