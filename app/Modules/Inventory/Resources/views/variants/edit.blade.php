@extends('layouts.admin')

@section('title', 'Varyant Düzenle')

@section('content')
<x-ui-page-header title="Varyant Düzenle" description="{{ $product->name }}" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.products.variants.update', [$product, $variant]) }}" data-variant-form>
        @csrf
        @method('PUT')
        @include('inventory::variants._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.products.variants.index', $product) }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Güncelle</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
