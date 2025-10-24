@extends('layouts.admin')

@section('title', 'Mal Kabul (GRN)')
@section('module', 'Logistics')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Mal Kabul (GRN)</h1>
        <a href="{{ route('admin.logistics.receipts.create') }}" class="btn btn-primary">Yeni GRN</a>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">Durum (Tümü)</option>
                @foreach (['draft' => 'Taslak', 'received' => 'Alındı', 'reconciled' => 'Uzlaşıldı', 'closed' => 'Kapandı', 'cancelled' => 'İptal'] as $key => $label)
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
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Belge No</th>
                        <th>Tedarikçi</th>
                        <th>Durum</th>
                        <th>Depo</th>
                        <th>Tarih</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receipts as $receipt)
                        <tr>
                            <td class="fw-semibold">{{ $receipt->doc_no }}</td>
                            <td>{{ $receipt->vendor_id ? ('#' . $receipt->vendor_id) : '—' }}</td>
                            <td><span class="badge bg-light text-dark text-capitalize">{{ $receipt->status }}</span></td>
                            <td>{{ $receipt->warehouse?->name ?? '—' }}</td>
                            <td>{{ optional($receipt->received_at)->format('d.m.Y H:i') ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.logistics.receipts.show', $receipt) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Henüz kayıt bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $receipts->links() }}
    </div>
@endsection
