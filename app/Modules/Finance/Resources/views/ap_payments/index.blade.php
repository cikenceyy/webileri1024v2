@extends('layouts.admin')

@section('title', 'AP Ödemeleri')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">AP Ödemeleri</h1>
            <p class="text-muted mb-0">Tedarikçi faturalarına yapılan ödemeleri takip edin.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.ap-payments.index') }}" class="btn btn-outline-secondary">Yenile</a>
            <a href="{{ route('admin.finance.ap-payments.create') }}" class="btn btn-primary">Yeni Ödeme</a>
        </div>
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
                        <th scope="col">Tarih</th>
                        <th scope="col" class="text-end">Tutar</th>
                        <th scope="col">Yöntem</th>
                        <th scope="col">Referans</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <a href="{{ route('admin.finance.ap-invoices.show', $payment->invoice) }}" class="text-decoration-none">
                                    #{{ $payment->invoice?->id ?? '—' }}
                                </a>
                            </td>
                            <td>{{ $payment->paid_at?->format('d.m.Y') }}</td>
                            <td class="text-end">{{ number_format((float) $payment->amount, 2, ',', '.') }} {{ $payment->invoice?->currency ?? 'TRY' }}</td>
                            <td>{{ $payment->method ?? '—' }}</td>
                            <td>{{ $payment->reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Henüz ödeme kaydı bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $payments->links() }}
        </div>
    </div>
@endsection
