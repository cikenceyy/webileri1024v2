@extends('layouts.admin')

@section('title', 'P2P Konsolu')

@push('page-styles')
    <style>
        .console-header {
            position: sticky;
            top: 0;
            z-index: 1020;
            backdrop-filter: blur(12px);
            background-color: rgba(var(--bs-body-bg-rgb, 255, 255, 255), 0.9);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 .35rem 1.2rem rgba(15, 23, 42, 0.08);
            margin-bottom: 2rem;
        }

        .console-card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 .35rem 1rem rgba(15, 23, 42, 0.08);
        }

        .console-card .table thead th {
            position: sticky;
            top: 0;
            background-color: rgba(var(--bs-body-bg-rgb, 248, 249, 250), 0.96);
            z-index: 5;
        }

        .console-scroll {
            max-height: 420px;
            overflow: auto;
        }

        @media (min-width: 992px) {
            .console-header {
                top: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-3">
        @if (session('status'))
            <div class="alert alert-success shadow-sm">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger shadow-sm">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="console-header d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-lg-between">
            <div>
                <h1 class="h3 mb-1">P2P Konsolu</h1>
                <p class="text-muted mb-0">Satınalma, kabul ve ödeme döngüsüne hızlı aksiyon.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.today-board') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-grid me-1"></i>Bugün Panosu
                </a>
                <a href="{{ route('admin.consoles.mto') }}" class="btn btn-outline-primary">
                    <i class="bi bi-speedometer2 me-1"></i>MTO Konsolu
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="console-card card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h2 class="h5 mb-0">Gecikmiş Tahsilatlar</h2>
                            <span class="badge text-bg-warning">{{ $overdueInvoices->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="console-scroll">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Fatura</th>
                                        <th>Müşteri</th>
                                        <th>Vade</th>
                                        <th class="text-end">Bakiye</th>
                                        <th class="text-end">Aksiyon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($overdueInvoices as $invoice)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">
                                                    {{ $invoice->invoice_no }}
                                                </a>
                                            </td>
                                            <td>{{ $invoice->customer?->name ?? '—' }}</td>
                                            <td>{{ optional($invoice->due_date)?->format('d.m.Y') ?? '—' }}</td>
                                            <td class="text-end">{{ number_format((float) $invoice->balance_due, 2, ',', '.') }} {{ $invoice->currency }}</td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.consoles.p2p.invoices.collect', $invoice) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">Tahsil Et</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Gecikmiş tahsilat bulunmuyor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-6">
                <div class="console-card card">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h2 class="h5 mb-0">Minimum Stok Uyarıları</h2>
                            <span class="badge text-bg-danger">{{ $lowStockItems->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="console-scroll">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Ürün</th>
                                        <th>Depo</th>
                                        <th class="text-end">Mevcut</th>
                                        <th class="text-end">Eşik</th>
                                        <th class="text-end">Aksiyon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($lowStockItems as $item)
                                        @php
                                            $availableQty = (float) $item->qty - (float) $item->reserved_qty;
                                            $suggestedQty = max((float) $item->reorder_point - $availableQty, 1);
                                        @endphp
                                        <tr>
                                            <td>{{ $item->product?->name ?? '—' }}</td>
                                            <td>{{ $item->warehouse?->name ?? '—' }}</td>
                                            <td class="text-end">{{ number_format($availableQty, 3, ',', '.') }}</td>
                                            <td class="text-end">{{ number_format((float) $item->reorder_point, 3, ',', '.') }}</td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.consoles.p2p.stock-items.purchase-orders', $item) }}" class="d-flex gap-2 justify-content-end">
                                                    @csrf
                                                    <input type="number" name="supplier_id" class="form-control form-control-sm" min="1" placeholder="Tedarikçi" required>
                                                    <input type="number" name="qty" step="0.001" min="0.001" value="{{ number_format($suggestedQty, 3, '.', '') }}" class="form-control form-control-sm" style="max-width: 6rem;">
                                                    <button type="submit" class="btn btn-outline-success btn-sm">PO Oluştur</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Minimum stok altında ürün yok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
