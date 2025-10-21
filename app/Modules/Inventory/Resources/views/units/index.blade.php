@extends('layouts.admin')

@section('title', 'Birimler')

@section('content')
<x-ui-page-header title="Birimler" description="Miktar birimlerinizi yönetin">
    <x-slot name="actions">
        @can('create', \App\Modules\Inventory\Domain\Models\Unit::class)
            <x-ui-button variant="primary" href="{{ route('admin.inventory.units.create') }}">Yeni Birim</x-ui-button>
        @endcan
    </x-slot>
</x-ui-page-header>

@if(session('status'))
    <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
@endif

<x-ui-card class="mb-4" data-inventory-filters>
    <form method="GET" action="{{ route('admin.inventory.units.index') }}" class="row g-3">
        <div class="col-md-8">
            <x-ui-input name="q" label="Ara" :value="$filters['q'] ?? ''" placeholder="Kod veya ad" />
        </div>
        <div class="col-md-4 d-flex gap-2 align-items-end">
            <x-ui-button type="submit" class="flex-grow-1">Filtrele</x-ui-button>
            <a href="{{ route('admin.inventory.units.index') }}" class="btn btn-outline-secondary">Sıfırla</a>
        </div>
    </form>
</x-ui-card>

@if($units->count())
    <x-ui-card>
        <x-ui-table dense>
            <thead>
                <tr>
                    <th>Kod</th>
                    <th>Ad</th>
                    <th>Dönüşüm</th>
                    <th>Temel?</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $unit)
                    <tr>
                        <td class="fw-semibold">{{ $unit->code }}</td>
                        <td>{{ $unit->name }}</td>
                        <td>{{ number_format((float) $unit->to_base, 6, ',', '.') }}</td>
                        <td>
                            @if($unit->is_base)
                                <x-ui-badge type="primary" soft>Temel</x-ui-badge>
                            @else
                                <span class="text-muted">Hayır</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui-table>
    </x-ui-card>

    <div class="mt-4">
        {{ $units->links() }}
    </div>
@else
    <x-ui-empty title="Birim bulunamadı" description="Yeni birim ekleyin." />
@endif
@endsection
