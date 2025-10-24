@extends('layouts.admin')

@section('title', 'Sipariş Düzenle')
@section('module', 'Marketing')

@push('page-scripts')
    <script>
        window.marketingStockSignals = @json($stockSignals);
        function refreshSignals() {
            document.querySelectorAll('[data-stock-row]').forEach(function (row) {
                const select = row.querySelector('[data-product-select]');
                const badge = row.querySelector('[data-stock-badge]');
                const productId = select.value;
                if (!productId || !window.marketingStockSignals[productId]) {
                    badge.textContent = '—';
                    badge.className = 'badge bg-secondary';
                    return;
                }
                const signal = window.marketingStockSignals[productId];
                const labelMap = { 'in': 'Stokta', 'low': 'Düşük', 'out': 'Tükendi' };
                const colorMap = { 'in': 'success', 'low': 'warning', 'out': 'danger' };
                badge.textContent = labelMap[signal.status] + ' • ' + signal.formatted;
                badge.className = 'badge bg-' + (colorMap[signal.status] || 'secondary');
            });
        }
        document.addEventListener('change', function (event) {
            if (event.target.matches('[data-product-select]')) {
                refreshSignals();
            }
        });
        document.addEventListener('DOMContentLoaded', refreshSignals);
    </script>
@endpush

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.marketing.orders.show', $order) }}" class="btn btn-link p-0 me-3">&larr; Detaya dön</a>
        <h1 class="h3 mb-0">{{ $order->doc_no }}</h1>
    </div>

    <form method="post" action="{{ route('admin.marketing.orders.update', $order) }}" class="card">
        @csrf
        @method('put')
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Müşteri</label>
                    <input type="text" class="form-control" value="{{ optional($order->customer)->name }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Belge No</label>
                    <input type="text" class="form-control" value="{{ $order->doc_no }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fiyat Listesi</label>
                    <select name="price_list_id" class="form-select">
                        <option value="">Varsayılan</option>
                        @foreach ($priceLists as $id => $label)
                            <option value="{{ $id }}" @selected(old('price_list_id', $order->price_list_id) == $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('price_list_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Para Birimi</label>
                    <input type="text" name="currency" value="{{ old('currency', $order->currency) }}" class="form-control" maxlength="3">
                    @error('currency')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vergi Dahil mi?</label>
                    <select name="tax_inclusive" class="form-select">
                        <option value="0" @selected(! old('tax_inclusive', $order->tax_inclusive))>Hariç</option>
                        <option value="1" @selected(old('tax_inclusive', $order->tax_inclusive))>Dahil</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vade (gün)</label>
                    <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', $order->payment_terms_days) }}" class="form-control" min="0" max="180">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vade Tarihi</label>
                    <input type="date" name="due_date" value="{{ old('due_date', optional($order->due_date)->toDateString()) }}" class="form-control">
                </div>
            </div>

            <h2 class="h5 mb-3">Satır Kalemleri</h2>
            @php($lineDefaults = old('lines', $order->lines->map(fn ($line) => [
                'id' => $line->id,
                'product_id' => $line->product_id,
                'qty' => $line->qty,
                'uom' => $line->uom,
                'unit_price' => $line->unit_price,
                'discount_pct' => $line->discount_pct,
                'tax_rate' => $line->tax_rate,
            ])->toArray()))
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 28%">Ürün</th>
                            <th>Miktar</th>
                            <th>Birim</th>
                            <th>Birim Fiyat</th>
                            <th>İskonto %</th>
                            <th>Vergi %</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lineDefaults as $index => $line)
                            <tr data-stock-row>
                                <td>
                                    <input type="hidden" name="lines[{{ $index }}][id]" value="{{ $line['id'] ?? '' }}">
                                    <select name="lines[{{ $index }}][product_id]" class="form-select" data-product-select required>
                                        <option value="">Ürün seçiniz</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected($line['product_id'] == $product->id)>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("lines.$index.product_id")<span class="text-danger small">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="number" step="0.001" name="lines[{{ $index }}][qty]" value="{{ $line['qty'] }}" class="form-control" min="0.001">
                                </td>
                                <td>
                                    <input type="text" name="lines[{{ $index }}][uom]" value="{{ $line['uom'] ?? 'pcs' }}" class="form-control">
                                </td>
                                <td>
                                    <input type="number" step="0.0001" name="lines[{{ $index }}][unit_price]" value="{{ $line['unit_price'] }}" class="form-control" min="0">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="lines[{{ $index }}][discount_pct]" value="{{ $line['discount_pct'] ?? 0 }}" class="form-control" min="0" max="100">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="lines[{{ $index }}][tax_rate]" value="{{ $line['tax_rate'] ?? '' }}" class="form-control" min="0" max="50">
                                </td>
                                <td>
                                    <span class="badge bg-secondary" data-stock-badge>—</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <label class="form-label">Not</label>
            <textarea name="notes" rows="3" class="form-control">{{ old('notes', $order->notes) }}</textarea>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Güncelle</button>
        </div>
    </form>
@endsection
