@extends('layouts.admin')

@section('title', 'Yeni BOM')
@section('module', 'Production')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Yeni Ürün Reçetesi</h1>
        <a href="{{ route('admin.production.boms.index') }}" class="btn btn-outline-secondary">Listeye Dön</a>
    </div>

    <form action="{{ route('admin.production.boms.store') }}" method="post">
        @csrf
        @include('production::admin.boms._form')
        <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </div>
    </form>
@endsection
