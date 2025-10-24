@extends('layouts.admin')

@section('title', 'İade Talepleri')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">İade Talepleri</h1>
        <a href="{{ route('admin.marketing.returns.create') }}" class="btn btn-primary">Yeni İade Talebi</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="Müşteri veya neden">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Durum (Tümü)</option>
                @foreach (['open' => 'Açık', 'approved' => 'Onaylı', 'closed' => 'Kapalı'] as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Filtrele</button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Müşteri</th>
                        <th>Durum</th>
                        <th>Neden</th>
                        <th>Oluşturulma</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($returns as $return)
                        <tr>
                            <td>{{ optional($return->customer)->name ?? '—' }}</td>
                            <td>{{ strtoupper($return->status) }}</td>
                            <td>{{ $return->reason ?? '—' }}</td>
                            <td>{{ $return->created_at?->format('d.m.Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.marketing.returns.show', $return) }}" class="btn btn-sm btn-outline-primary">Detay</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Henüz iade talebi bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $returns->links() }}
        </div>
    </div>
@endsection
