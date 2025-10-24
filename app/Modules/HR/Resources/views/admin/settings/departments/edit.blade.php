@extends('layouts.admin')

@section('title', 'Departman Düzenle')
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.hr.settings.departments.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">Departman Düzenle</h1>
    </div>

    <form method="post" action="{{ route('admin.hr.settings.departments.update', $department) }}" class="card">
        @csrf
        @method('put')
        <div class="card-body">
            @include('hr::admin.settings.departments._form', ['department' => $department])
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Güncelle</button>
        </div>
    </form>
@endsection
