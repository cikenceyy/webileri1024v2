@extends('layouts.admin')

@section('title', 'Ünvan Düzenle')
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.hr.settings.titles.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">Ünvan Düzenle</h1>
    </div>

    <form method="post" action="{{ route('admin.hr.settings.titles.update', $title) }}" class="card">
        @csrf
        @method('put')
        <div class="card-body">
            @include('hr::admin.settings.titles._form', ['title' => $title])
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Güncelle</button>
        </div>
    </form>
@endsection
