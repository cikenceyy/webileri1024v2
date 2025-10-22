@extends('layouts.admin')

@section('title', $product->name . ' • BOM')
@section('module', 'Inventory')

@push('page-styles')
    @vite('app/Modules/Inventory/Resources/scss/bom.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Inventory/Resources/js/bom.js')
@endpush

@section('content')
    <section class="inv-bom" data-product="{{ $product->id }}">
        <header class="inv-bom__header">
            <div>
                <h1 class="inv-bom__title">{{ $product->name }}</h1>
                <p class="inv-bom__subtitle">SKU: {{ $product->sku }}</p>
            </div>
            <form method="get" class="inv-bom__lot">
                <label for="bom-lot" class="form-label">Lot</label>
                <select id="bom-lot" name="lot" class="form-select" data-action="change-lot">
                    @foreach ([1, 10, 100] as $lotOption)
                        <option value="{{ $lotOption }}" {{ $lot === $lotOption ? 'selected' : '' }}>{{ $lotOption }}</option>
                    @endforeach
                </select>
            </form>
        </header>

        <section class="inv-bom__items">
            <h2 class="inv-bom__section-title">Reçete Kalemleri</h2>
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Malzeme</th>
                        <th>Birim</th>
                        <th>Gereken</th>
                        <th>Mevcut</th>
                        <th>Durum</th>
                        <th class="text-end">Aksiyon</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($components as $component)
                        <tr>
                            <td>{{ $component['material'] }}</td>
                            <td>{{ $component['unit'] }}</td>
                            <td>{{ number_format($component['required'], 2) }}</td>
                            <td>{{ number_format($component['available'], 2) }}</td>
                            <td>
                                <span class="inv-badge {{ $component['status'] === 'missing' ? 'inv-badge--danger' : 'inv-badge--success' }}">
                                    {{ $component['status'] === 'missing' ? 'Eksik' : 'Hazır' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-action="bom-procure">Tedarik</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-action="bom-transfer">Transfer</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">Reçete kalemi bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <aside class="inv-bom__summary">
            <h2 class="inv-bom__section-title">Yeterlilik Özeti</h2>
            <p>Stokta bulunan miktar: {{ number_format($onHand, 2) }}</p>
            <p>Lot başına ihtiyaç: {{ number_format($components->sum('required'), 2) }}</p>
            <button type="button" class="btn btn-outline-primary" data-action="bom-balance">Eksikleri çöz</button>
        </aside>
    </section>
@endsection
