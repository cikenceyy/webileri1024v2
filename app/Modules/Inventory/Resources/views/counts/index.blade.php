@extends('layouts.admin')

@section('title', 'Stok Sayımları')
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header d-flex justify-content-between align-items-center">
            <h1 class="inv-card__title">Stok Sayımları</h1>
            @can('create', \App\Modules\Inventory\Domain\Models\StockCount::class)
                <a href="{{ route('admin.inventory.counts.create') }}" class="btn btn-primary btn-sm">Yeni Sayım</a>
            @endcan
        </header>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Belge No</th>
                    <th>Depo</th>
                    <th>Durum</th>
                    <th>Sayım Tarihi</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($counts as $count)
                    <tr>
                        <td>{{ $count->doc_no }}</td>
                        <td>{{ $count->warehouse?->name }}</td>
                        <td><span class="badge bg-{{ $count->status === 'reconciled' ? 'success' : ($count->status === 'counted' ? 'warning' : 'secondary') }}">{{ ucfirst($count->status) }}</span></td>
                        <td>{{ optional($count->counted_at ?? $count->created_at)->format('d.m.Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.inventory.counts.show', $count) }}" class="btn btn-sm btn-outline-secondary">Görüntüle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Henüz sayım kaydı yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $counts->links() }}
        </div>
    </section>
@endsection
