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
        <x-table :config="$tableKitConfig" :rows="$tableKitRows" :paginator="$tableKitPaginator">
            <x-slot name="toolbar">
                <x-table:toolbar :config="$tableKitConfig" />
            </x-slot>
        </x-table>
    </section>
@endsection
