@extends('layouts.admin')

@section('title', 'Yeni Mal Kabulü')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Yeni Mal Kabulü</h1>
        <p class="text-muted mb-0">Onaylı satınalma siparişleri için teslimat miktarlarını girin.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Form hataları mevcut.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="{{ route('admin.procurement.grns.create') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="purchase_order_id" class="form-label">Satınalma Siparişi</label>
                    <select name="purchase_order_id" id="purchase_order_id" class="form-select" required>
                        <option value="">Sipariş Seçiniz</option>
                        @foreach($availableOrders as $order)
                            <option value="{{ $order->id }}" @selected(optional($purchaseOrder)->id === $order->id)>
                                #{{ $order->id }} · {{ strtoupper($order->status) }} · {{ number_format((float) $order->total, 2, ',', '.') }} {{ $order->currency }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary">Satırları Yükle</button>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="{{ route('admin.procurement.pos.create') }}" class="btn btn-link">Yeni PO Oluştur</a>
                </div>
            </form>
        </div>
    </div>

    @if($purchaseOrder)
        <form action="{{ route('admin.procurement.grns.store') }}" method="post">
            @csrf
            <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Sipariş Satırları</h2>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Satır</th>
                                <th scope="col">Açıklama</th>
                                <th scope="col" class="text-end">Sipariş Miktarı</th>
                                <th scope="col" class="text-end">Teslim Alınan</th>
                                <th scope="col" style="width: 20%">Bu Kabul</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->lines as $index => $line)
                                @php
                                    $received = $line->grnLines->sum('qty_received');
                                    $remaining = max((float) $line->qty_ordered - (float) $received, 0);
                                @endphp
                                <tr>
                                    <td>#{{ $line->id }}</td>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ number_format((float) $line->qty_ordered, 3, ',', '.') }} {{ $line->unit }}</td>
                                    <td class="text-end">{{ number_format((float) $received, 3, ',', '.') }} {{ $line->unit }}</td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" step="0.001" min="0" max="{{ $remaining }}" name="lines[{{ $index }}][qty_received]" class="form-control" value="{{ old("lines.$index.qty_received", $remaining > 0 ? $remaining : 0) }}">
                                            <input type="hidden" name="lines[{{ $index }}][po_line_id]" value="{{ $line->id }}">
                                            <span class="input-group-text">{{ $line->unit }}</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.procurement.grns.index') }}" class="btn btn-outline-secondary me-2">Vazgeç</a>
                <button type="submit" class="btn btn-primary">Mal Kabulünü Kaydet</button>
            </div>
        </form>
    @endif
@endsection
