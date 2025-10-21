@extends('layouts.admin')

@section('title', 'AP Fatura Detayı')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">AP Fatura #{{ $apInvoice->id }}</h1>
            <p class="text-muted mb-0">PO #{{ $apInvoice->purchaseOrder?->id ?? '—' }} · GRN #{{ $apInvoice->goodsReceipt?->id ?? '—' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.ap-invoices.index') }}" class="btn btn-outline-secondary">Listeye Dön</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($apInvoice->has_price_variance)
        <div class="alert alert-warning">
            <strong>Fiyat farkı:</strong> Fatura toplamı beklenen tutardan {{ number_format(abs((float) $apInvoice->price_variance_amount), 2, ',', '.') }} {{ $apInvoice->currency }} {{ $apInvoice->price_variance_amount >= 0 ? 'fazla' : 'düşük' }}.
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Satır Kalemleri</h2>
                    <span class="badge rounded-pill text-bg-{{ $apInvoice->status === 'paid' ? 'success' : ($apInvoice->status === 'approved' ? 'primary' : 'secondary') }}">
                        {{ strtoupper($apInvoice->status) }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Açıklama</th>
                                <th class="text-end">Miktar</th>
                                <th>Birim</th>
                                <th class="text-end">Birim Fiyat</th>
                                <th class="text-end">Tutar</th>
                                <th>Kaynak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($apInvoice->lines as $line)
                                <tr>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ number_format((float) $line->qty, 3, ',', '.') }}</td>
                                    <td>{{ $line->unit ?? '—' }}</td>
                                    <td class="text-end">{{ number_format((float) $line->unit_price, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) $line->amount, 2, ',', '.') }}</td>
                                    <td><code>{{ $line->source_type }}:{{ $line->source_uuid }}</code></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Fatura satırı bulunmuyor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-end">
                    <div class="fw-semibold">Beklenen Toplam: {{ number_format((float) $apInvoice->expected_total, 2, ',', '.') }} {{ $apInvoice->currency }}</div>
                    <div class="fs-5">Fatura Toplamı: {{ number_format((float) $apInvoice->total, 2, ',', '.') }} {{ $apInvoice->currency }}</div>
                    <div>Bakiye: {{ number_format((float) $apInvoice->balance_due, 2, ',', '.') }} {{ $apInvoice->currency }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Ödemeler</h2>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th class="text-end">Tutar</th>
                                <th>Yöntem</th>
                                <th>Referans</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($apInvoice->payments as $payment)
                                <tr>
                                    <td>{{ $payment->paid_at?->format('d.m.Y') }}</td>
                                    <td class="text-end">{{ number_format((float) $payment->amount, 2, ',', '.') }} {{ $apInvoice->currency }}</td>
                                    <td>{{ $payment->method ?? '—' }}</td>
                                    <td>{{ $payment->reference ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Henüz ödeme kaydı yok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="h5 mb-0">Durum Güncelle</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.finance.ap-invoices.update', $apInvoice) }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label for="status" class="form-label">Durum</label>
                            <select name="status" id="status" class="form-select">
                                @foreach(['draft' => 'Taslak', 'approved' => 'Onaylı', 'paid' => 'Ödendi'] as $value => $label)
                                    <option value="{{ $value }}" @selected($apInvoice->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Kaydet</button>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Notlar</h2>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $apInvoice->notes ?? 'Not bulunmuyor.' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
