@extends('layouts.admin')

@section('title', 'Müşteriler')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Müşteri Listesi</h1>
        <a href="{{ route('admin.marketing.customers.create') }}" class="btn btn-primary">Yeni Müşteri</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control" placeholder="İsim, e-posta veya telefon ile ara">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Durum (Tümü)</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Pasif</option>
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
                        <th>Ad / Ünvan</th>
                        <th>E-posta</th>
                        <th>Vade (gün)</th>
                        <th>Varsayılan Fiyat Listesi</th>
                        <th>Durum</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td>{{ $customer->payment_terms_days ?? 0 }}</td>
                            <td>{{ optional($customer->priceList)->name ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                    {{ $customer->is_active ? 'Aktif' : 'Pasif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.marketing.customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">Detay</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Henüz müşteri kaydı bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $customers->links() }}
        </div>
    </div>
@endsection
