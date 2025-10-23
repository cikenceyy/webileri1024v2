<div class="finance-summary">
    <header class="finance-summary__header">
        <h3>{{ $invoice->customer?->name ?? __('Müşteri Bilgisi Yok') }}</h3>
        <span class="finance-summary__badge">{{ $invoice->invoice_no }}</span>
    </header>

    <dl class="finance-summary__grid">
        <div>
            <dt>{{ __('Vade Tarihi') }}</dt>
            <dd>{{ $invoice->due_date?->format('d.m.Y') ?? __('Belirlenmemiş') }}</dd>
        </div>
        <div>
            <dt>{{ __('Kalan Bakiye') }}</dt>
            <dd>{{ number_format($invoice->balance_due, 2) }} {{ $invoice->currency }}</dd>
        </div>
        <div>
            <dt>{{ __('Durum') }}</dt>
            <dd>{{ ucfirst($invoice->status) }}</dd>
        </div>
        <div>
            <dt>{{ __('Son Güncelleme') }}</dt>
            <dd>{{ $invoice->updated_at?->diffForHumans() }}</dd>
        </div>
    </dl>

    <section>
        <h4>{{ __('Hızlı Tahsilat') }}</h4>
        <form method="POST" action="{{ route('admin.finance.allocations.store') }}" class="finance-summary__quick-form">
            @csrf
            <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <x-ui-select name="receipt_id" :label="__('Tahsilat Kaydı')" :options="$receipts->map(fn($receipt) => ['value' => $receipt->id, 'label' => $receipt->receipt_no . ' · ' . number_format($receipt->amount - $receipt->allocated_total, 2) . ' ' . $receipt->currency])->toArray()" :placeholder="__('Tahsilat seçin')" />
                </div>
                <div class="col-12 col-md-6">
                    <x-ui-input type="number" step="0.01" min="0" name="amount" :label="__('Tahsil Edilecek Tutar')" value="{{ number_format($invoice->balance_due, 2) }}" />
                </div>
            </div>
            @if($receipts->isEmpty())
                <p class="text-warning small mt-2">{{ __('Bu müşteriye ait kayıtlı tahsilat bulunmuyor. Önce Tahsilatlar sayfasından bir kayıt oluşturun.') }}</p>
            @else
                <div class="mt-3 text-end">
                    <x-ui-button type="submit">{{ __('Tahsilatı Kaydet') }}</x-ui-button>
                </div>
            @endif
            <p class="text-muted small mt-2">{{ __('Kısmi tahsilat için tutarı düzenleyin, kayıt sonrası bakiye otomatik güncellenir.') }}</p>
        </form>
    </section>

    <section class="finance-summary__allocations">
        <h4>{{ __('Tahsilat Hareketleri') }}</h4>
        <ul>
            @forelse($invoice->allocations as $allocation)
                <li>
                    <span>{{ $allocation->receipt?->receipt_no ?? __('Tahsilat #:id', ['id' => $allocation->id]) }}</span>
                    <span>{{ number_format($allocation->amount, 2) }} {{ $invoice->currency }}</span>
                </li>
            @empty
                <li class="text-muted">{{ __('Bu faturaya ait tahsilat bulunmuyor.') }}</li>
            @endforelse
        </ul>
    </section>
</div>
