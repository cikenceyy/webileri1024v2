@extends('layouts.admin')

@section('title', 'Müşteri Güncelle')
@section('module', 'Marketing')

@section('content')
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.marketing.customers.show', $customer) }}" class="btn btn-link p-0 me-3">&larr; Detaya dön</a>
        <h1 class="h3 mb-0">{{ $customer->name }}</h1>
    </div>

    <form method="post" action="{{ route('admin.marketing.customers.update', $customer) }}" class="card">
        @csrf
        @method('put')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Ad / Ünvan</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-control" required>
                    @error('name')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vergi No</label>
                    <input type="text" name="tax_no" value="{{ old('tax_no', $customer->tax_no) }}" class="form-control">
                    @error('tax_no')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kredi Limiti</label>
                    <input type="number" step="0.01" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" class="form-control">
                    @error('credit_limit')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-control">
                    @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-control">
                    @error('phone')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Varsayılan Fiyat Listesi</label>
                    <select name="default_price_list_id" class="form-select">
                        <option value="">Seçiniz</option>
                        @foreach ($priceLists as $id => $label)
                            <option value="{{ $id }}" @selected(old('default_price_list_id', $customer->default_price_list_id) == $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('default_price_list_id')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Vade (gün)</label>
                    <input type="number" name="payment_terms_days" value="{{ old('payment_terms_days', $customer->payment_terms_days) }}" class="form-control" min="0" max="180">
                    @error('payment_terms_days')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Durum</label>
                    <select name="is_active" class="form-select">
                        <option value="1" @selected(old('is_active', $customer->is_active) == true)>Aktif</option>
                        <option value="0" @selected(old('is_active', $customer->is_active) == false)>Pasif</option>
                    </select>
                    @error('is_active')<span class="text-danger small">{{ $message }}</span>@enderror
                </div>
            </div>
            <hr class="my-4">
            <h2 class="h5">Fatura Adresi</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Adres Satırı</label>
                    <input type="text" name="billing_address[line1]" value="{{ old('billing_address.line1', $customer->billing_address['line1'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Şehir</label>
                    <input type="text" name="billing_address[city]" value="{{ old('billing_address.city', $customer->billing_address['city'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ülke</label>
                    <input type="text" name="billing_address[country]" value="{{ old('billing_address.country', $customer->billing_address['country'] ?? '') }}" class="form-control" maxlength="2">
                </div>
            </div>
            <hr class="my-4">
            <h2 class="h5">Sevkiyat Adresi</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Adres Satırı</label>
                    <input type="text" name="shipping_address[line1]" value="{{ old('shipping_address.line1', $customer->shipping_address['line1'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Şehir</label>
                    <input type="text" name="shipping_address[city]" value="{{ old('shipping_address.city', $customer->shipping_address['city'] ?? '') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ülke</label>
                    <input type="text" name="shipping_address[country]" value="{{ old('shipping_address.country', $customer->shipping_address['country'] ?? '') }}" class="form-control" maxlength="2">
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Güncelle</button>
        </div>
    </form>
@endsection
