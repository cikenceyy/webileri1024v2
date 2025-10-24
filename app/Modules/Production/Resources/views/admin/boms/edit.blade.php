@extends('layouts.admin')

@section('title', 'BOM Düzenle')
@section('module', 'Production')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">BOM Düzenle</h1>
        <a href="{{ route('admin.production.boms.show', $bom) }}" class="btn btn-outline-secondary">Geri</a>
    </div>

    <form action="{{ route('admin.production.boms.update', $bom) }}" method="post">
        @csrf
        @method('put')
        @include('production::admin.boms._form')
        <div class="d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">Güncelle</button>
        </div>
    </form>
@endsection
