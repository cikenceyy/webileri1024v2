@extends('layouts.admin')

@section('title', 'Yeni İade Talebi')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.marketing.returns.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
        <h1 class="h3 mb-0">Yeni İade Talebi</h1>
    </div>

    <form method="post" action="{{ route('admin.marketing.returns.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Müşteri</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Seçiniz</option>
                        @foreach ($customers as $id => $label)
                            <option value="{{ $id }}" @selected(old('customer_id') == $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Referans Sipariş</label>
                    <input type="number" name="related_order_id" value="{{ old('related_order_id') }}" class="form-control" placeholder="Opsiyonel sipariş ID">
                    @error('related_order_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Genel Neden</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="form-control">
                </div>
            </div>

            <h2 class="h5 mb-3">Kalemler</h2>
            @php($lineDefaults = old('lines', [['product_id' => '', 'qty' => 1, 'reason_code' => '', 'notes' => '']]))
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Miktar</th>
                            <th>Neden Kodu</th>
                            <th>Not</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lineDefaults as $index => $line)
                            <tr>
                                <td>
                                    <select name="lines[{{ $index }}][product_id]" class="form-select" required>
                                        <option value="">Ürün seçiniz</option>
                                        @foreach ($products as $id => $label)
                                            <option value="{{ $id }}" @selected($line['product_id'] == $id)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error("lines.$index.product_id")<span class="text-danger small">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="number" step="0.001" name="lines[{{ $index }}][qty]" value="{{ $line['qty'] }}" class="form-control" min="0.001">
                                    @error("lines.$index.qty")<span class="text-danger small">{{ $message }}</span>@enderror
                                </td>
                                <td>
                                    <input type="text" name="lines[{{ $index }}][reason_code]" value="{{ $line['reason_code'] }}" class="form-control">
                                </td>
                                <td>
                                    <input type="text" name="lines[{{ $index }}][notes]" value="{{ $line['notes'] }}" class="form-control">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-muted small">Ek satırlar eklemek için kaydedip düzenleyebilirsiniz.</p>

            <label class="form-label">Detay Notu</label>
            <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Kaydet</button>
        </div>
    </form>
@endsection
