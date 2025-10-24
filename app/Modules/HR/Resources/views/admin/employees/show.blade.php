@extends('layouts.admin')

@section('title', $employee->name)
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.hr.employees.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">{{ $employee->name }}</h1>
        <span class="badge ms-3 bg-{{ $employee->is_active ? 'success' : 'secondary' }}">{{ $employee->is_active ? 'Aktif' : 'Pasif' }}</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Kod</dt>
                        <dd class="col-sm-8">{{ $employee->code }}</dd>

                        <dt class="col-sm-4 text-muted">E-posta</dt>
                        <dd class="col-sm-8">{{ $employee->email ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">Telefon</dt>
                        <dd class="col-sm-8">{{ $employee->phone ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">Departman</dt>
                        <dd class="col-sm-8">{{ $employee->department?->name ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">Ünvan</dt>
                        <dd class="col-sm-8">{{ $employee->title?->name ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">Çalışma Tipi</dt>
                        <dd class="col-sm-8">{{ $employee->employmentType?->name ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">İşe Giriş</dt>
                        <dd class="col-sm-8">{{ optional($employee->hire_date)->format('d.m.Y') ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">İşten Ayrılış</dt>
                        <dd class="col-sm-8">{{ optional($employee->termination_date)->format('d.m.Y') ?? '-' }}</dd>

                        <dt class="col-sm-4 text-muted">Bağlı Kullanıcı</dt>
                        <dd class="col-sm-8">{{ $employee->user?->name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
            @if (! empty($employee->notes))
                <div class="card mt-4">
                    <div class="card-body">
                        <h2 class="h5">Notlar</h2>
                        <p class="mb-0">{{ $employee->notes }}</p>
                    </div>
                </div>
            @endif
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6 text-muted">İşlemler</h2>
                    <div class="d-grid gap-2">
                        @can('update', $employee)
                            <a href="{{ route('admin.hr.employees.edit', $employee) }}" class="btn btn-outline-secondary">Düzenle</a>
                        @endcan
                        @can('archive', $employee)
                            <form action="{{ route('admin.hr.employees.archive', $employee) }}" method="post" onsubmit="return confirm('Personeli arşivlemek istiyor musunuz?');">
                                @csrf
                                <button class="btn btn-outline-danger w-100" @disabled(! $employee->is_active)>Arşivle</button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
