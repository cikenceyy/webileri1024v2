@extends('layouts.admin')

@section('title', $warehouse->name)
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="inv-card__title">{{ $warehouse->name }}</h1>
                <p class="text-muted">Kod: {{ $warehouse->code }}</p>
            </div>
            <div class="d-flex gap-2">
                @can('update', $warehouse)
                    <a href="{{ route('admin.inventory.warehouses.edit', $warehouse) }}" class="btn btn-outline-secondary btn-sm">Düzenle</a>
                @endcan
                <a href="{{ route('admin.inventory.warehouses.index') }}" class="btn btn-link btn-sm">← Depo listesi</a>
            </div>
        </header>
        <div class="row g-4">
            <div class="col-md-4">
                <h2 class="h6 mb-3">Raflar</h2>
                <ul class="list-group mb-3">
                    @forelse ($bins as $bin)
                        <li class="list-group-item">
                            @can('update', $warehouse)
                                <form method="post" action="{{ route('admin.inventory.warehouses.bins.update', [$warehouse, $bin]) }}" class="row g-2 align-items-center mb-2">
                                    @csrf
                                    @method('put')
                                    <div class="col-4">
                                        <input type="text" name="code" value="{{ old('code', $bin->code) }}" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" name="name" value="{{ old('name', $bin->name) }}" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-3 d-flex gap-1">
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">Kaydet</button>
                                    </div>
                                </form>
                                <form method="post" action="{{ route('admin.inventory.warehouses.bins.destroy', [$warehouse, $bin]) }}" onsubmit="return confirm('Raf silinsin mi?')">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                                </form>
                            @else
                                <div>
                                    <strong>{{ $bin->code }}</strong>
                                    <div class="text-muted small">{{ $bin->name }}</div>
                                </div>
                            @endcan
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Henüz raf tanımı yapılmadı.</li>
                    @endforelse
                </ul>
                @can('update', $warehouse)
                    <form method="post" action="{{ route('admin.inventory.warehouses.bins.store', $warehouse) }}" class="card card-body">
                        @csrf
                        <h3 class="h6">Yeni Raf</h3>
                        <div class="mb-2">
                            <label class="form-label">Kod</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Ad</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Ekle</button>
                    </form>
                @endcan
            </div>
            <div class="col-md-8">
                <h2 class="h6 mb-3">Stok Özeti</h2>
                <form method="get" class="mb-3">
                    <input type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Ürün ara">
                </form>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Raf</th>
                            <th class="text-end">Miktar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockItems as $row)
                            <tr>
                                <td>{{ $row['product']?->name ?? '—' }}<br><span class="text-muted">{{ $row['product']?->sku ?? '' }}</span></td>
                                <td>
                                    @if ($row['bin'])
                                        {{ $row['bin']->code }} — {{ $row['bin']->name }}
                                    @else
                                        Genel stok
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($row['qty'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Stok kaydı bulunamadı.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
