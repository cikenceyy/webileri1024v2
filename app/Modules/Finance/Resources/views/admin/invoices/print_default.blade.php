@php
    use Illuminate\Support\Str;

    $company = tenant();
    $customer = $invoice->customer;
    $issueDate = optional($invoice->issued_at)->format('d.m.Y');
    $dueDate = optional($invoice->due_date)->format('d.m.Y');
    $currency = $invoice->currency ?? config('app.currency', 'TRY');
@endphp

<header class="print-header">
    <div>
        <h2>{{ $company->name ?? config('app.name') }}</h2>
        @if($company?->tax_no)
            <p class="text-muted">{{ __('Vergi No: :tax', ['tax' => $company->tax_no]) }}</p>
        @endif
        @if($company?->email)
            <p class="text-muted">{{ $company->email }}</p>
        @endif
    </div>
    <div class="text-end">
        <h1>{{ __('Fatura') }}</h1>
        <p class="text-muted">{{ __('Belge No: :no', ['no' => $invoice->doc_no ?? __('Taslak')]) }}</p>
        <p class="text-muted">{{ __('Tarih: :date', ['date' => $issueDate ?: '—']) }}</p>
        <p class="text-muted">{{ __('Vade: :date', ['date' => $dueDate ?: '—']) }}</p>
    </div>
</header>

<section class="print-meta" style="display:flex; justify-content: space-between; gap:2rem; margin-bottom:2rem;">
    <div style="flex:1;">
        <h3 class="h6" style="margin-bottom:0.5rem;">{{ __('Fatura Bilgileri') }}</h3>
        <p class="text-muted" style="margin:0;">{{ __('Durum: :status', ['status' => Str::headline($invoice->status)]) }}</p>
        <p class="text-muted" style="margin:0;">{{ __('Ödeme Şartı: :days gün', ['days' => $invoice->payment_terms_days ?? '—']) }}</p>
    </div>
    <div style="flex:1;">
        <h3 class="h6" style="margin-bottom:0.5rem;">{{ __('Müşteri') }}</h3>
        <p style="margin:0;">{{ $customer?->name ?? '—' }}</p>
        @if(!empty($customer?->billing_address))
            <p class="text-muted" style="margin:0;">{{ implode(' ', array_filter($customer->billing_address)) }}</p>
        @endif
        @if($customer?->tax_no)
            <p class="text-muted" style="margin:0;">{{ __('Vergi No: :tax', ['tax' => $customer->tax_no]) }}</p>
        @endif
        @if($customer?->email)
            <p class="text-muted" style="margin:0;">{{ $customer->email }}</p>
        @endif
        @if($customer?->phone)
            <p class="text-muted" style="margin:0;">{{ $customer->phone }}</p>
        @endif
    </div>
</section>

<table class="print-table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">{{ __('Açıklama') }}</th>
            <th scope="col" class="text-end">{{ __('Miktar') }}</th>
            <th scope="col" class="text-end">{{ __('Birim Fiyat') }}</th>
            <th scope="col" class="text-end">{{ __('Vergi %') }}</th>
            <th scope="col" class="text-end">{{ __('Tutar') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->lines as $index => $line)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $line->description ?? $line->product?->name ?? __('Satır :number', ['number' => $index + 1]) }}</td>
                <td class="text-end">{{ number_format((float) $line->qty, 3) }} {{ $line->uom }}</td>
                <td class="text-end">{{ number_format((float) $line->unit_price, 2) }} {{ $currency }}</td>
                <td class="text-end">{{ number_format((float) $line->tax_rate, 2) }}</td>
                <td class="text-end">{{ number_format((float) $line->line_total, 2) }} {{ $currency }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-muted">{{ __('Fatura satırı bulunmuyor.') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>

<section style="display:flex; justify-content: flex-end;">
    <table style="min-width:320px; border-collapse:collapse;">
        <tbody>
        <tr>
            <td style="padding:0.5rem; border:1px solid #d1d5db;">{{ __('Ara Toplam') }}</td>
            <td style="padding:0.5rem; border:1px solid #d1d5db;" class="text-end">{{ number_format((float) $invoice->subtotal, 2) }} {{ $currency }}</td>
        </tr>
        <tr>
            <td style="padding:0.5rem; border:1px solid #d1d5db;">{{ __('Vergi') }}</td>
            <td style="padding:0.5rem; border:1px solid #d1d5db;" class="text-end">{{ number_format((float) $invoice->tax_total, 2) }} {{ $currency }}</td>
        </tr>
        <tr>
            <td style="padding:0.5rem; border:1px solid #d1d5db; font-weight:600;">{{ __('Genel Toplam') }}</td>
            <td style="padding:0.5rem; border:1px solid #d1d5db; font-weight:600;" class="text-end">{{ number_format((float) $invoice->grand_total, 2) }} {{ $currency }}</td>
        </tr>
        <tr>
            <td style="padding:0.5rem; border:1px solid #d1d5db;">{{ __('Tahsil Edilen') }}</td>
            <td style="padding:0.5rem; border:1px solid #d1d5db;" class="text-end">{{ number_format((float) $invoice->paid_amount, 2) }} {{ $currency }}</td>
        </tr>
        <tr>
            <td style="padding:0.5rem; border:1px solid #d1d5db;">{{ __('Kalan Bakiye') }}</td>
            <td style="padding:0.5rem; border:1px solid #d1d5db;" class="text-end">{{ number_format((float) $invoice->balance_due, 2) }} {{ $currency }}</td>
        </tr>
        </tbody>
    </table>
</section>

@if($invoice->notes)
    <section style="margin-top:2rem;">
        <h3 class="h6">{{ __('Notlar') }}</h3>
        <p class="text-muted">{!! nl2br(e($invoice->notes)) !!}</p>
    </section>
@endif

<footer class="print-footer">
    <span>{{ __('Yazdırma tarihi: :date', ['date' => now()->format('d.m.Y H:i')]) }}</span>
    <span>{{ __('Powered by :app', ['app' => config('app.name')]) }}</span>
</footer>
