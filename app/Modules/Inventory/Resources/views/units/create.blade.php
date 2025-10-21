@extends('layouts.admin')

@section('title', 'Yeni Birim')

@section('content')
<x-ui-page-header title="Yeni Birim" description="Miktar birimi ekleyin" />

<x-ui-card>
    <form method="POST" action="{{ route('admin.inventory.units.store') }}">
        @csrf
        @include('inventory::units._form')

        <div class="mt-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.inventory.units.index') }}" class="btn btn-outline-secondary">Vazgeç</a>
            <x-ui-button type="submit" variant="primary">Kaydet</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
