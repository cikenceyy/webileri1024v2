@extends('layouts.admin')

@section('title', 'BOM Listesi')
@section('module', 'Production')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Ürün Reçeteleri</h1>
        @can('create', \App\Modules\Production\Domain\Models\Bom::class)
            <a href="{{ route('admin.production.boms.create') }}" class="btn btn-primary">Yeni BOM</a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Kod</th>
                    <th>Ürün</th>
                    <th>Versiyon</th>
                    <th>Çıktı Miktarı</th>
                    <th class="text-end">İşlemler</th>
                </tr>
                </thead>
                <tbody>
                @forelse($boms as $bom)
                    <tr>
                        <td>{{ $bom->code }}</td>
                        <td>{{ $bom->product?->name ?? 'Ürün' }}</td>
                        <td>{{ $bom->version }}</td>
                        <td>{{ number_format($bom->output_qty, 3) }} {{ $bom->product?->unit ?? 'pcs' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.production.boms.show', $bom) }}" class="btn btn-sm btn-outline-primary">Görüntüle</a>
                            @can('update', $bom)
                                <a href="{{ route('admin.production.boms.edit', $bom) }}" class="btn btn-sm btn-outline-secondary">Düzenle</a>
                            @endcan
                            @can('create', \App\Modules\Production\Domain\Models\Bom::class)
                                <form action="{{ route('admin.production.boms.duplicate', $bom) }}" method="post" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-info">Kopyala</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Henüz tanımlı reçete yok.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $boms->links() }}
    </div>
@endsection
