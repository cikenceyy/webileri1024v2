@extends('layouts.admin')

@section('title', 'Stok Transferleri')
@section('module', 'Inventory')

@section('content')
    <section class="inv-card">
        <header class="inv-card__header d-flex justify-content-between align-items-center">
            <h1 class="inv-card__title">Stok Transferleri</h1>
            @can('create', \App\Modules\Inventory\Domain\Models\StockTransfer::class)
                <a href="{{ route('admin.inventory.transfers.create') }}" class="btn btn-primary btn-sm">Yeni Transfer</a>
            @endcan
        </header>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Belge No</th>
                    <th>Kaynak</th>
                    <th>Hedef</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transfers as $transfer)
                    <tr>
                        <td>{{ $transfer->doc_no }}</td>
                        <td>{{ $transfer->fromWarehouse?->name }}</td>
                        <td>{{ $transfer->toWarehouse?->name }}</td>
                        <td><span class="badge bg-{{ $transfer->status === 'posted' ? 'success' : 'secondary' }}">{{ ucfirst($transfer->status) }}</span></td>
                        <td>{{ optional($transfer->created_at)->format('d.m.Y H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.inventory.transfers.show', $transfer) }}" class="btn btn-sm btn-outline-secondary">Görüntüle</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Henüz transfer kaydı yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $transfers->links() }}
        </div>
    </section>
@endsection
