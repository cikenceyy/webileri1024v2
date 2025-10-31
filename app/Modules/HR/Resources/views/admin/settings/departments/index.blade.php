{{--
    Amaç: İK departman listesi tablosunu TableKit yüzeyiyle hizalamak.
    İlişkiler: Codex Prompt — Console & TableKit Tablo Görünümü Eşleştirme.
    Notlar: Görsel birlik için tablo sınıf kancaları eklendi.
--}}
@extends('layouts.admin')

@section('title', 'Departmanlar')
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">Departmanlar</h1>
        @can('create', \App\Modules\HR\Domain\Models\Department::class)
            <a href="{{ route('admin.hr.settings.departments.create') }}" class="btn btn-primary">Yeni Departman</a>
        @endcan
    </div>

    <form method="get" class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Arama</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Ad veya kod">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Durum</label>
                    <select name="status" class="form-select">
                        <option value="">Hepsi</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Pasif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100">Filtrele</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive tablekit-surface__wrapper">
            <table class="table align-middle mb-0 tablekit-surface">
                <thead>
                    <tr>
                        <th scope="col">Kod</th>
                        <th scope="col">Ad</th>
                        <th scope="col">Durum</th>
                        <th scope="col" class="tablekit-surface__actions">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td class="text-monospace">{{ $department->code }}</td>
                            <td>{{ $department->name }}</td>
                            <td>
                                <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                                    {{ $department->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="text-end tablekit-surface__actions">
                                @can('update', $department)
                                    <a href="{{ route('admin.hr.settings.departments.edit', $department) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                                @endcan
                                @can('delete', $department)
                                    <form action="{{ route('admin.hr.settings.departments.destroy', $department) }}" method="post" class="d-inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn-sm btn-outline-danger">Sil</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Kayıt bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $departments->links() }}
        </div>
    </div>
@endsection
