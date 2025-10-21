@extends('layouts.admin')

@section('title', 'Tedarikçi Faturaları')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Tedarikçi Faturaları</h1>
            <p class="text-muted mb-0">Mal kabullerinden oluşan AP faturalarını yönetin.</p>
        </div>
        <a href="{{ route('admin.finance.ap-invoices.index') }}" class="btn btn-outline-secondary">Yenile</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Fatura</th>
                        <th scope="col">PO</th>
                        <th scope="col">GRN</th>
                        <th scope="col">Durum</th>
                        <th scope="col" class="text-end">Toplam</th>
                        <th scope="col" class="text-end">Bakiye</th>
                        <th scope="col">Uyarı</th>
                        <th scope="col" class="text-end">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>#{{ $invoice->id }}</td>
                            <td>{{ $invoice->purchaseOrder?->id ?? '—' }}</td>
                            <td>{{ $invoice->goodsReceipt?->id ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill text-bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'approved' ? 'primary' : 'secondary') }}">
                                    {{ strtoupper($invoice->status) }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format((float) $invoice->total, 2, ',', '.') }} {{ $invoice->currency }}</td>
                            <td class="text-end">{{ number_format((float) $invoice->balance_due, 2, ',', '.') }} {{ $invoice->currency }}</td>
                            <td>
                                @if($invoice->has_price_variance)
                                    <span class="badge text-bg-warning">Fiyat farkı</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.finance.ap-invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Henüz AP faturası bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $invoices->links() }}
        </div>
    </div>
@endsection
