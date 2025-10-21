@extends('layouts.admin')

@section('title', 'Yeni Fiyat Listesi')

@section('content')
<x-ui-page-header title="Yeni Fiyat Listesi" description="Fiyat listesi tanımlayın" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.pricelists.store') }}">
        @csrf
        @include('inventory::pricelists._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.pricelists.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Kaydet</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
