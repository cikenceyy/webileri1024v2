@extends('layouts.admin')

@section('title', 'Ünvanlar')
@section('module', 'HR')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">Ünvanlar</h1>
        @can('create', \App\Modules\HR\Domain\Models\Title::class)
            <a href="{{ route('admin.hr.settings.titles.create') }}" class="btn btn-primary">Yeni Ünvan</a>
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
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Kod</th>
                        <th>Ad</th>
                        <th>Durum</th>
                        <th class="text-end">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($titles as $title)
                        <tr>
                            <td class="text-monospace">{{ $title->code }}</td>
                            <td>{{ $title->name }}</td>
                            <td>
                                <span class="badge bg-{{ $title->is_active ? 'success' : 'secondary' }}">
                                    {{ $title->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('update', $title)
                                    <a href="{{ route('admin.hr.settings.titles.edit', $title) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                                @endcan
                                @can('delete', $title)
                                    <form action="{{ route('admin.hr.settings.titles.destroy', $title) }}" method="post" class="d-inline" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
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
            {{ $titles->links() }}
        </div>
    </div>
@endsection
