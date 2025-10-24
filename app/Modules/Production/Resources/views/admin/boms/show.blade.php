@extends('layouts.admin')

@section('title', $bom->code)
@section('module', 'Production')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $bom->code }} • v{{ $bom->version }}</h1>
            <p class="text-muted mb-0">{{ $bom->product?->name }}</p>
        </div>
        <div class="d-flex gap-2">
            @can('update', $bom)
                <a href="{{ route('admin.production.boms.edit', $bom) }}" class="btn btn-outline-secondary">Düzenle</a>
            @endcan
            @can('create', \App\Modules\Production\Domain\Models\Bom::class)
                <form action="{{ route('admin.production.boms.duplicate', $bom) }}" method="post">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">Kopyala</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Çıktı Miktarı</dt>
                <dd class="col-sm-9">{{ number_format($bom->output_qty, 3) }}</dd>
                <dt class="col-sm-3">Durum</dt>
                <dd class="col-sm-9">{{ $bom->is_active ? 'Aktif' : 'Pasif' }}</dd>
                <dt class="col-sm-3">Notlar</dt>
                <dd class="col-sm-9">{{ $bom->notes ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Bileşenler</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead>
                <tr>
                    <th>Malzeme</th>
                    <th>Miktar</th>
                    <th>Fire %</th>
                    <th>Varsayılan Depo</th>
                    <th>Varsayılan Raf</th>
                </tr>
                </thead>
                <tbody>
                @forelse($bom->items as $item)
                    <tr>
                        <td>{{ $item->component?->name ?? '—' }}</td>
                        <td>{{ number_format($item->qty_per, 3) }}</td>
                        <td>{{ number_format($item->wastage_pct, 2) }}</td>
                        <td>{{ $item->defaultWarehouse?->name ?? '—' }}</td>
                        <td>{{ $item->defaultBin?->code ?? $item->default_bin_id ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Bileşen bulunmuyor.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
