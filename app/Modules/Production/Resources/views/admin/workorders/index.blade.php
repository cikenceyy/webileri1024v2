@extends('layouts.admin')

@section('title', 'İş Emirleri')
@section('module', 'Production')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">İş Emirleri</h1>
        @can('create', \App\Modules\Production\Domain\Models\WorkOrder::class)
            <a href="{{ route('admin.production.workorders.create') }}" class="btn btn-primary">Yeni İş Emri</a>
        @endcan
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-3">
            <label for="status" class="form-label">Durum</label>
            <select name="status" id="status" class="form-select">
                <option value="">Hepsi</option>
                @foreach(['draft' => 'Taslak', 'released' => 'Serbest', 'in_progress' => 'Üretimde', 'completed' => 'Tamamlandı', 'closed' => 'Kapalı', 'cancelled' => 'İptal'] as $key => $label)
                    <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 align-self-end">
            <button class="btn btn-outline-secondary" type="submit">Filtrele</button>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Numara</th>
                    <th>Ürün</th>
                    <th>Durum</th>
                    <th>Hedef</th>
                    <th>Termin</th>
                    <th class="text-end">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                @forelse($workOrders as $workOrder)
                    <tr>
                        <td>{{ $workOrder->doc_no }}</td>
                        <td>{{ $workOrder->product?->name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</td>
                        <td>{{ number_format($workOrder->target_qty, 3) }} {{ $workOrder->uom }}</td>
                        <td>{{ optional($workOrder->due_date)->format('d.m.Y') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.production.workorders.show', $workOrder) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">İş emri bulunmuyor.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $workOrders->links() }}
    </div>
@endsection
