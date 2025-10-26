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
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" />
            </x-slot>
        </x-table>
    </section>
@endsection
