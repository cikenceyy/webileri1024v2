@extends('layouts.admin')

@section('title', 'Yeni Çalışma Tipi')
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.hr.settings.employment-types.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">Yeni Çalışma Tipi</h1>
    </div>

    <form method="post" action="{{ route('admin.hr.settings.employment-types.store') }}" class="card">
        @csrf
        <div class="card-body">
            @include('hr::admin.settings.employment-types._form', ['employmentType' => null])
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Kaydet</button>
        </div>
    </form>
@endsection
