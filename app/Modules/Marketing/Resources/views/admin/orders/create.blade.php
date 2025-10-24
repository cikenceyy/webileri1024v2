@extends('layouts.admin')

@section('title', 'Yeni Satış Siparişi')
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
        <a href="{{ route('admin.marketing.orders.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">Yeni Satış Siparişi</h1>
    </div>

    <form method="post" action="{{ route('admin.marketing.orders.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Müşteri</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Seçiniz</option>
                        @foreach ($customers as $id => $label)
                            <option value="{{ $id }}" @selected(old('customer_id', $defaults['customer_id'] ?? null) == $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Belge No</label>
                    <input type="text" class="form-control" value="{{ $defaults['doc_no'] }}" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fiyat Listesi</label>
                    <select name="price_list_id" class="form-select">
                        <option value="">Varsayılan</option>
                        @foreach ($priceLists as $id => $label)
                            <option value="{{ $id }}" @selected(old('price_list_id', $defaults['price_list_id']) == $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('price_list_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Para Birimi</label>
                    <input type="text" name="currency" value="{{ old('currency', $defaults['currency'] ?? 'TRY') }}" class="form-control" maxlength="3">
                    @error('currency')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vergi Dahil mi?</label>
                    <select name="tax_inclusive" class="form-select">
                        <option value="0" @selected(! old('tax_inclusive', $defaults['tax_inclusive']))>Hariç</option>
                        <option value="1" @selected(old('tax_inclusive', $defaults['tax_inclusive']))>Dahil</option>
                    </select>
                    @error('tax_inclusive')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vade (gün)</label>
                    <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', $defaults['payment_terms_days']) }}" class="form-control" min="0" max="180">
                    @error('payment_terms_days')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vade Tarihi</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $defaults['due_date']) }}" class="form-control">
                    @error('due_date')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>

            <h2 class="h5 mb-3">Satır Kalemleri</h2>
            @php($lineDefaults = old('lines', [['product_id' => '', 'qty' => 1, 'uom' => 'pcs', 'unit_price' => 0, 'discount_pct' => 0, 'tax_rate' => null]]))
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
                                    @error("lines.$index.qty")<span class="text-danger small">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="text" name="lines[{{ $index }}][uom]" value="{{ $line['uom'] ?? 'pcs' }}" class="form-control">
                                </td>
                                <td>
                                    <input type="number" step="0.0001" name="lines[{{ $index }}][unit_price]" value="{{ $line['unit_price'] }}" class="form-control" min="0">
                                    @error("lines.$index.unit_price")<span class="text-danger small">{{ $message }}</span>@enderror
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
            <p class="text-muted small">Ek satırlar eklemek için kaydedip düzenleyebilirsiniz.</p>

            <label class="form-label">Not</label>
            <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Taslağı Kaydet</button>
        </div>
    </form>
@endsection
