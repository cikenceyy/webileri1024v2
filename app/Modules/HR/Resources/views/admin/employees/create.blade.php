@extends('layouts.admin')

@section('title', 'Yeni Personel')
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.hr.employees.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">Yeni Personel</h1>
    </div>

    <form method="post" action="{{ route('admin.hr.employees.store') }}" class="card">
        @csrf
        <div class="card-body">
            @include('hr::admin.employees._form', ['employee' => null])
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Kaydet</button>
        </div>
    </form>
@endsection
