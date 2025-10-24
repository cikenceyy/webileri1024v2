@extends('layouts.admin')

@section('title', 'Çalışma Tipi Düzenle')
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.hr.settings.employment-types.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">Çalışma Tipi Düzenle</h1>
    </div>

    <form method="post" action="{{ route('admin.hr.settings.employment-types.update', $employmentType) }}" class="card">
        @csrf
        @method('put')
        <div class="card-body">
            @include('hr::admin.settings.employment-types._form', ['employmentType' => $employmentType])
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Güncelle</button>
        </div>
    </form>
@endsection
