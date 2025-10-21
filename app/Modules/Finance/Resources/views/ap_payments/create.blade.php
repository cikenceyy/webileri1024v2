@extends('layouts.admin')

@section('title', 'AP Ödeme Kaydı')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Yeni AP Ödemesi</h1>
            <p class="text-muted mb-0">Tedarikçi faturası için ödeme kaydedin.</p>
        </div>
        <a href="{{ route('admin.finance.ap-payments.index') }}" class="btn btn-outline-secondary">Geri Dön</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('admin.finance.ap-payments.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="ap_invoice_id" class="form-label">Fatura</label>
                    <select name="ap_invoice_id" id="ap_invoice_id" class="form-select" required>
                        <option value="">Fatura seçin</option>
                        @foreach($openInvoices as $invoice)
                            <option value="{{ $invoice->id }}" @selected(old('ap_invoice_id') == $invoice->id)>
                                #{{ $invoice->id }} · Bakiye {{ number_format((float) $invoice->balance_due, 2, ',', '.') }} {{ $invoice->currency }}
                            </option>
                        @endforeach
                    </select>
                    @error('ap_invoice_id')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="paid_at" class="form-label">Ödeme Tarihi</label>
                        <input type="date" name="paid_at" id="paid_at" class="form-control" value="{{ old('paid_at', now()->toDateString()) }}" required>
                        @error('paid_at')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Tutar</label>
                        <input type="number" step="0.01" min="0" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
                        @error('amount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-3 mt-0">
                    <div class="col-md-6">
                        <label for="method" class="form-label">Ödeme Yöntemi</label>
                        <input type="text" name="method" id="method" class="form-control" value="{{ old('method') }}" placeholder="Havale, EFT, çek...">
                        @error('method')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="reference" class="form-label">Referans</label>
                        <input type="text" name="reference" id="reference" class="form-control" value="{{ old('reference') }}" placeholder="Dekont no">
                        @error('reference')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label for="notes" class="form-label">Notlar</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Opsiyonel açıklama">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Ödemeyi Kaydet</button>
                </div>
            </form>
        </div>
    </div>
@endsection
