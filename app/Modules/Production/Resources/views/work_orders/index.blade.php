@extends('layouts.admin')

@section('title', 'İş Emirleri')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">İş Emirleri</h1>
            <p class="text-muted mb-0">Siparişlerden türetilen üretim iş emirlerini yönetin.</p>
        </div>
        <a href="{{ route('admin.production.work-orders.index') }}" class="btn btn-outline-secondary">Yenile</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">İş Emri</th>
                            <th scope="col">Sipariş</th>
                            <th scope="col">Ürün</th>
                            <th scope="col" class="text-end">Miktar</th>
                            <th scope="col">Durum</th>
                            <th scope="col">Termin</th>
                            <th scope="col" class="text-end">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workOrders as $workOrder)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $workOrder->work_order_no }}</div>
                                    <div class="text-muted small">{{ $workOrder->created_at?->format('d.m.Y H:i') }}</div>
                                </td>
                                <td>
                                    @if($workOrder->order)
                                        <span class="badge text-bg-light">{{ $workOrder->order->order_no }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($workOrder->product)
                                        <div class="fw-semibold">{{ $workOrder->product->name }}</div>
                                        @if($workOrder->variant)
                                            <div class="text-muted small">Varyant: {{ $workOrder->variant->option_summary ?: $workOrder->variant->sku }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format((float) $workOrder->qty, 3, ',', '.') }} {{ $workOrder->unit }}</td>
                                <td>
                                    <span class="badge rounded-pill text-bg-{{ $workOrder->status === 'done' ? 'success' : ($workOrder->status === 'in_progress' ? 'primary' : 'secondary') }}">
                                        {{ strtoupper($workOrder->status) }}
                                    </span>
                                </td>
                                <td>{{ $workOrder->due_date?->format('d.m.Y') ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.production.work-orders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Henüz iş emri bulunmuyor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $workOrders->links() }}
        </div>
    </div>
@endsection
