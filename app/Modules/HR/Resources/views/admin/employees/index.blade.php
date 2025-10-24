@extends('layouts.admin')

@section('title', 'Personel Dizini')
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">Personel Dizini</h1>
        @can('create', \App\Modules\HR\Domain\Models\Employee::class)
            <a href="{{ route('admin.hr.employees.create') }}" class="btn btn-primary">Yeni Personel</a>
        @endcan
    </div>

    <form method="get" class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Arama</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Ad, kod veya e-posta">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Departman</label>
                    <select name="department_id" class="form-select">
                        <option value="">Hepsi</option>
                        @foreach ($departments as $id => $label)
                            <option value="{{ $id }}" @selected((int) ($filters['department_id'] ?? 0) === (int) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ünvan</label>
                    <select name="title_id" class="form-select">
                        <option value="">Hepsi</option>
                        @foreach ($titles as $id => $label)
                            <option value="{{ $id }}" @selected((int) ($filters['title_id'] ?? 0) === (int) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Çalışma Tipi</label>
                    <select name="employment_type_id" class="form-select">
                        <option value="">Hepsi</option>
                        @foreach ($employmentTypes as $id => $label)
                            <option value="{{ $id }}" @selected((int) ($filters['employment_type_id'] ?? 0) === (int) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="">Hepsi</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Pasif</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-primary w-100">Filtre</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>Ad</th>
                        <th>Departman</th>
                        <th>Ünvan</th>
                        <th>Çalışma Tipi</th>
                        <th>Durum</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td class="text-monospace">{{ $employee->code }}</td>
                            <td>
                                <a href="{{ route('admin.hr.employees.show', $employee) }}">{{ $employee->name }}</a>
                                <div class="text-muted small">{{ $employee->email }}</div>
                            </td>
                            <td>{{ $employee->department?->name ?? '-' }}</td>
                            <td>{{ $employee->title?->name ?? '-' }}</td>
                            <td>{{ $employee->employmentType?->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $employee->is_active ? 'success' : 'secondary' }}">
                                    {{ $employee->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('update', $employee)
                                    <a href="{{ route('admin.hr.employees.edit', $employee) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                                @endcan
                                @can('archive', $employee)
                                    <form action="{{ route('admin.hr.employees.archive', $employee) }}" method="post" class="d-inline" onsubmit="return confirm('Personeli arşivlemek istiyor musunuz?');">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger" @disabled(! $employee->is_active)>Arşivle</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Personel bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $employees->links() }}
        </div>
    </div>
@endsection
