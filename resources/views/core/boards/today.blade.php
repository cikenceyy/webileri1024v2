@extends('layouts.admin')

@section('title', 'Bugün Panosu')

@push('page-styles')
    <style>
        .today-board .board-header {
            position: sticky;
            top: 0;
            z-index: 1020;
            backdrop-filter: blur(12px);
            background-color: rgba(var(--bs-body-bg-rgb, 255, 255, 255), 0.88);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 .35rem 1.2rem rgba(15, 23, 42, 0.08);
        }

        .today-board .board-grid {
            margin-top: 1.5rem;
        }

        .today-board .card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 .25rem 1rem rgba(15, 23, 42, 0.08);
        }

        .today-board .card-header {
            position: sticky;
            top: 0;
            z-index: 5;
            backdrop-filter: blur(10px);
            background-color: rgba(var(--bs-body-bg-rgb, 255, 255, 255), 0.94);
        }

        .today-board .board-table {
            max-height: 320px;
            overflow: auto;
        }

        .today-board .board-table table {
            min-width: 100%;
        }

        .today-board .table thead th {
            position: sticky;
            top: 0;
            z-index: 6;
            background-color: rgba(var(--bs-body-bg-rgb, 248, 249, 250), 0.96);
        }

        @media (min-width: 992px) {
            .today-board .board-header {
                top: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="today-board container-fluid py-3">
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

        <div class="board-header mb-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-3">
                <div>
                    <h1 class="h3 mb-1">Bugün Panosu</h1>
                    <p class="text-muted mb-0">Operasyon konsollarınızdan kritik işlerin anlık görünümü.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-primary" href="{{ route('admin.consoles.mto') }}">
                        <i class="bi bi-speedometer2 me-1"></i>MTO Konsolu
                    </a>
                    <a class="btn btn-outline-success" href="{{ route('admin.consoles.p2p') }}">
                        <i class="bi bi-cash-stack me-1"></i>P2P Konsolu
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-4 board-grid">
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="h5 mb-0">Açık Siparişler</h2>
                                <small class="text-muted">WO önerisi bekleyen onaylı siparişler</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="board-table">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Müşteri</th>
                                        <th class="text-end">Tutar</th>
                                        <th>Durum</th>
                                        <th class="text-end">Aksiyon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($openOrders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.marketing.orders.show', $order) }}" class="fw-semibold text-decoration-none">
                                                    {{ $order->order_no }}
                                                </a>
                                            </td>
                                            <td>{{ $order->customer?->name ?? '—' }}</td>
                                            <td class="text-end">{{ number_format((float) $order->total_amount, 2, ',', '.') }} {{ $order->currency }}</td>
                                            <td>
                                                <span class="badge text-bg-info">{{ ucfirst($order->status) }}</span>
                                            </td>
                                            <td class="text-end">
                                                @if ($order->work_orders_count > 0)
                                                    <span class="badge text-bg-success">WO Hazır</span>
                                                @else
                                                    <form method="POST" action="{{ route('admin.consoles.mto.work-orders.store', $order) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-sm">WO Oluştur</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Açık sipariş bulunmuyor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="h5 mb-0">Sevkiyat Bekleyenler</h2>
                                <small class="text-muted">Paketlenmiş &amp; çıkışa hazır gönderiler</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="board-table">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Müşteri</th>
                                        <th>Tarih</th>
                                        <th>Durum</th>
                                        <th class="text-end">Aksiyon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pendingShipments as $shipment)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.logistics.shipments.show', $shipment) }}" class="text-decoration-none fw-semibold">
                                                    {{ $shipment->shipment_no }}
                                                </a>
                                            </td>
                                            <td>{{ $shipment->customer?->name ?? '—' }}</td>
                                            <td>{{ optional($shipment->ship_date)?->format('d.m.Y') }}</td>
                                            <td><span class="badge text-bg-warning text-uppercase">{{ str_replace('_', ' ', $shipment->status) }}</span></td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.consoles.mto.shipments.ship', $shipment) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">Sevk Et</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Sevk edilmeyi bekleyen kayıt yok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="h5 mb-0">Gecikmiş Tahsilatlar</h2>
                                <small class="text-muted">30g: <strong>{{ number_format($overdueBuckets['30'] ?? 0, 2, ',', '.') }}</strong> · 60g: <strong>{{ number_format($overdueBuckets['60'] ?? 0, 2, ',', '.') }}</strong> · 90g+: <strong>{{ number_format($overdueBuckets['90'] ?? 0, 2, ',', '.') }}</strong></small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="board-table">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>No</th>
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
                                                <a href="{{ route('admin.finance.invoices.show', $invoice) }}" class="text-decoration-none fw-semibold">
                                                    {{ $invoice->invoice_no }}
                                                </a>
                                            </td>
                                            <td>{{ $invoice->customer?->name ?? '—' }}</td>
                                            <td class="text-nowrap">{{ optional($invoice->due_date)?->format('d.m.Y') }}</td>
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

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="h5 mb-0">Minimum Stok</h2>
                                <small class="text-muted">Eşik altına inen kalemler</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="board-table">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Ürün</th>
                                        <th>Depo</th>
                                        <th class="text-end">Mevcut</th>
                                        <th class="text-end">Reorder</th>
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
                                            <td colspan="5" class="text-center py-4 text-muted">Minimum stok eşiği altında ürün yok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="h5 mb-0">Bekleyen QC</h2>
                                <small class="text-muted">Tamamlanmayı bekleyen kabul kontrolleri</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="board-table">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>GRN</th>
                                        <th>Sipariş</th>
                                        <th>Durum</th>
                                        <th>Tarih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pendingQc as $grn)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.procurement.grns.show', $grn) }}" class="fw-semibold text-decoration-none">#{{ $grn->id }}</a>
                                            </td>
                                            <td>
                                                @if ($grn->purchaseOrder)
                                                    <a href="{{ route('admin.procurement.pos.show', $grn->purchaseOrder) }}" class="text-decoration-none">PO #{{ $grn->purchaseOrder->id }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td><span class="badge text-bg-warning">{{ ucfirst($grn->status) }}</span></td>
                                            <td>{{ optional($grn->received_at)?->format('d.m.Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">Bekleyen kalite kontrol kaydı yok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100">
                    <div class="card-header border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="h5 mb-0">Bugünkü Bakım</h2>
                                <small class="text-muted">Planlı bakım / servis işleri</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="board-table">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>İş Emri</th>
                                        <th>Ürün</th>
                                        <th>Planlanan</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($maintenanceToday as $workOrder)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.production.work-orders.show', $workOrder) }}" class="fw-semibold text-decoration-none">
                                                    {{ $workOrder->work_order_no }}
                                                </a>
                                            </td>
                                            <td>{{ $workOrder->product?->name ?? '—' }}</td>
                                            <td>{{ optional($workOrder->planned_start_date)?->format('H:i') ?? '—' }}</td>
                                            <td><span class="badge text-bg-secondary text-uppercase">{{ str_replace('_', ' ', $workOrder->status) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">Bugün için planlı bakım kaydı yok.</td>
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
