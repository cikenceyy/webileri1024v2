@extends('layouts.admin')

@section('title', 'MTO Konsolu')

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
                <h1 class="h3 mb-1">MTO Konsolu</h1>
                <p class="text-muted mb-0">Siparişten üretime ve sevkiyata tek bakış.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.today-board') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-grid me-1"></i>Bugün Panosu
                </a>
                <a href="{{ route('admin.consoles.p2p') }}" class="btn btn-outline-success">
                    <i class="bi bi-cash-stack me-1"></i>P2P Konsolu
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="console-card card">
                    <div class="card-header border-0">
                        <h2 class="h5 mb-0">WO Bekleyen Siparişler</h2>
                    </div>
                    <div class="card-body p-0">
                        <div class="console-scroll">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Sipariş</th>
                                        <th>Müşteri</th>
                                        <th>Planlanan</th>
                                        <th class="text-end">Aksiyon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($ordersAwaitingWorkOrders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.marketing.orders.show', $order) }}" class="fw-semibold text-decoration-none">
                                                    {{ $order->order_no }}
                                                </a>
                                            </td>
                                            <td>{{ $order->customer?->name ?? '—' }}</td>
                                            <td>{{ optional($order->due_date)?->format('d.m.Y') ?? '—' }}</td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.consoles.mto.work-orders.store', $order) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">WO Oluştur</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">Bekleyen sipariş bulunmuyor.</td>
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
                        <h2 class="h5 mb-0">Çıkışa Hazır Sevkiyatlar</h2>
                    </div>
                    <div class="card-body p-0">
                        <div class="console-scroll">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Sevkiyat</th>
                                        <th>Müşteri</th>
                                        <th>Tarih</th>
                                        <th class="text-end">Aksiyon</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($shipmentsReady as $shipment)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.logistics.shipments.show', $shipment) }}" class="fw-semibold text-decoration-none">
                                                    {{ $shipment->shipment_no }}
                                                </a>
                                            </td>
                                            <td>{{ $shipment->customer?->name ?? '—' }}</td>
                                            <td>{{ optional($shipment->ship_date)?->format('d.m.Y') ?? '—' }}</td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('admin.consoles.mto.shipments.ship', $shipment) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">Sevk Et</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">Sevk edilmeyi bekleyen kayıt yok.</td>
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
