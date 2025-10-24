@extends('layouts.admin')

@section('title', $customer->name)
@section('module', 'Marketing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.marketing.customers.index') }}" class="btn btn-link p-0 me-3">&larr; Listeye dön</a>
            <h1 class="h3 mb-0">{{ $customer->name }}</h1>
            <p class="text-muted mb-0">Varsayılan fiyat listesi: {{ optional($customer->priceList)->name ?? 'Tanımlı değil' }}</p>
        </div>
        <a href="{{ route('admin.marketing.customers.edit', $customer) }}" class="btn btn-outline-primary">Düzenle</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Temel Bilgiler</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">E-posta</dt>
                        <dd class="col-sm-8">{{ $customer->email ?? '—' }}</dd>
                        <dt class="col-sm-4">Telefon</dt>
                        <dd class="col-sm-8">{{ $customer->phone ?? '—' }}</dd>
                        <dt class="col-sm-4">Vergi No</dt>
                        <dd class="col-sm-8">{{ $customer->tax_no ?? '—' }}</dd>
                        <dt class="col-sm-4">Vade (gün)</dt>
                        <dd class="col-sm-8">{{ $customer->payment_terms_days ?? 0 }}</dd>
                        <dt class="col-sm-4">Kredi Limiti</dt>
                        <dd class="col-sm-8">{{ $customer->credit_limit ? number_format($customer->credit_limit, 2, ',', '.') . ' ₺' : '—' }}</dd>
                        <dt class="col-sm-4">Durum</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                {{ $customer->is_active ? 'Aktif' : 'Pasif' }}
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Adresler</div>
                <div class="card-body">
                    <h6>Fatura Adresi</h6>
                    <p class="text-muted">
                        {{ $customer->billing_address['line1'] ?? 'Tanımlı değil' }}<br>
                        {{ $customer->billing_address['city'] ?? '' }} {{ $customer->billing_address['country'] ?? '' }}
                    </p>
                    <h6>Sevkiyat Adresi</h6>
                    <p class="text-muted mb-0">
                        {{ $customer->shipping_address['line1'] ?? 'Tanımlı değil' }}<br>
                        {{ $customer->shipping_address['city'] ?? '' }} {{ $customer->shipping_address['country'] ?? '' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
