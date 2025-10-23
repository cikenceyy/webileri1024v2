@extends('layouts.admin')

@section('title', __('Banka & Kasa Paneli'))
@section('module', 'finance')
@section('page', 'cash-panel')

@push('page-styles')
    @vite('app/Modules/Finance/Resources/scss/finance.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Finance/Resources/js/finance.js')
@endpush

@section('content')
    <div class="finance-shell" data-finance-screen="cash-panel">
        <header class="finance-shell__section-header">
            <div>
                <h1>{{ __('Banka & Kasa Paneli') }}</h1>
                <p class="text-muted">{{ __('Hesap bakiyelerini kart görünümünde takip edin, hızlı hareket girin ve CSV ile işlemleri içe aktarın.') }}</p>
            </div>
            <div class="finance-filter-presets" role="list">
                <button type="button" class="finance-filter-presets__item {{ $activeCurrency === '' ? 'is-active' : '' }}" data-currency-filter="">{{ __('Tümü') }}</button>
                @foreach($currencies as $currency)
                    <button type="button" class="finance-filter-presets__item {{ $activeCurrency === $currency ? 'is-active' : '' }}" data-currency-filter="{{ $currency }}">{{ $currency }}</button>
                @endforeach
            </div>
        </header>

        <section class="finance-account-grid" data-currency-filter="{{ $activeCurrency }}">
            @forelse($accounts as $account)
                <article class="finance-account-card" data-currency="{{ $account->currency }}">
                    <header>
                        <h2>{{ $account->name }}</h2>
                        <span class="finance-account-card__meta">{{ $account->account_no ?? '—' }}</span>
                    </header>
                    <div class="finance-account-card__body">
                        <p class="finance-account-card__balance">{{ number_format($account->balance, 2) }} {{ $account->currency }}</p>
                        <span class="finance-account-card__hint">{{ __('Son hareket: :date', ['date' => $account->last_transaction_at ? \Carbon\Carbon::parse($account->last_transaction_at)->format('d.m.Y') : __('Henüz yok')]) }}</span>
                    </div>
                    <footer class="finance-account-card__footer">
                        <span class="badge bg-{{ $account->is_default ? 'primary' : 'secondary' }}">{{ $account->is_default ? __('Varsayılan') : __('Alternatif') }}</span>
                        <span class="badge bg-light text-dark">{{ strtoupper($account->status ?? 'active') }}</span>
                    </footer>
                </article>
            @empty
                <x-ui-empty title="{{ __('Henüz banka veya kasa hesabı yok.') }}" description="{{ __('Yeni bir hesap ekleyerek başlayın.') }}" />
            @endforelse
        </section>

        <section class="finance-shell__split">
            <div class="finance-shell__panel">
                <x-ui-card>
                    <h2>{{ __('Hesap Oluştur') }}</h2>
                    <form method="POST" action="{{ route('admin.finance.cash-panel.accounts.store') }}" class="row g-2">
                        @csrf
                        <div class="col-md-6">
                            <x-ui-input name="name" :label="__('Hesap Adı')" required />
                        </div>
                        <div class="col-md-6">
                            <x-ui-input name="account_no" :label="__('Hesap No / IBAN')" />
                        </div>
                        <div class="col-md-6">
                            <x-ui-select name="currency" :label="__('Para Birimi')" :options="collect(config('finance.supported_currencies'))->map(fn($code) => ['value' => $code, 'label' => $code])->toArray()" />
                        </div>
                        <div class="col-md-6">
                            <x-ui-select name="status" :label="__('Durum')" :options="[['value' => 'active', 'label' => __('Aktif')], ['value' => 'inactive', 'label' => __('Pasif')]]" />
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="finance-default-account" name="is_default">
                                <label class="form-check-label" for="finance-default-account">{{ __('Varsayılan hesap olsun') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <x-ui-button type="submit">{{ __('Hesap Kaydet') }}</x-ui-button>
                        </div>
                    </form>
                </x-ui-card>
            </div>
            <div class="finance-shell__panel">
                <x-ui-card>
                    <h2>{{ __('Hareket Ekle') }}</h2>
                    <form method="POST" action="{{ route('admin.finance.cash-panel.transactions.store') }}" class="row g-2">
                        @csrf
                        <div class="col-md-6">
                            <x-ui-select name="bank_account_id" :label="__('Hesap')" :options="$accounts->map(fn($account) => ['value' => $account->id, 'label' => $account->name . ' · ' . $account->currency])->toArray()" :placeholder="__('Hesap seçin')" />
                        </div>
                        <div class="col-md-6">
                            <x-ui-select name="type" :label="__('Tür')" :options="[['value' => 'deposit', 'label' => __('Giriş')], ['value' => 'withdrawal', 'label' => __('Çıkış')]]" />
                        </div>
                        <div class="col-md-4">
                            <x-ui-input type="number" step="0.01" min="0" name="amount" :label="__('Tutar')" required />
                        </div>
                        <div class="col-md-4">
                            <x-ui-select name="currency" :label="__('Para Birimi')" :options="collect(config('finance.supported_currencies'))->map(fn($code) => ['value' => $code, 'label' => $code])->toArray()" />
                        </div>
                        <div class="col-md-4">
                            <x-ui-input type="date" name="transacted_at" :label="__('Tarih')" value="{{ now()->toDateString() }}" />
                        </div>
                        <div class="col-md-6">
                            <x-ui-input name="reference" :label="__('Referans')" />
                        </div>
                        <div class="col-md-6">
                            <x-ui-input name="notes" :label="__('Not')" />
                        </div>
                        <div class="col-12">
                            <x-ui-button type="submit">{{ __('Kaydet') }}</x-ui-button>
                        </div>
                    </form>
                </x-ui-card>
            </div>
            <div class="finance-shell__panel">
                <x-ui-card>
                    <h2>{{ __('CSV İçe Aktarma') }}</h2>
                    <form method="POST" action="{{ route('admin.finance.cash-panel.transactions.import') }}" enctype="multipart/form-data">
                        @csrf
                        <x-ui-input type="file" name="file" :label="__('CSV Dosyası')" accept=".csv,text/csv" />
                        <p class="text-muted small mt-2">{{ __('Kolonlar: bank_account_id,type,amount,currency,transacted_at,reference,notes') }}</p>
                        <x-ui-button type="submit">{{ __('İçe Aktar') }}</x-ui-button>
                    </form>
                </x-ui-card>
            </div>
        </section>

        <section class="finance-activity" aria-labelledby="cash-transactions">
            <header class="finance-shell__section-header">
                <div>
                    <h2 id="cash-transactions">{{ __('Son Banka & Kasa Hareketleri') }}</h2>
                    <p class="text-muted">{{ __('Liste, seçili para birimine göre filtrelenir.') }}</p>
                </div>
                <div class="finance-shell__quick-actions">
                    <span class="finance-balance-chip">{{ __('Net hareket:') }} <strong>{{ number_format($netBalance, 2) }}</strong></span>
                </div>
            </header>
            <div class="finance-table-wrapper">
                <table class="finance-table">
                    <thead>
                        <tr>
                            <th>{{ __('Tarih') }}</th>
                            <th>{{ __('Hesap') }}</th>
                            <th>{{ __('Tür') }}</th>
                            <th class="text-end">{{ __('Tutar') }}</th>
                            <th>{{ __('Referans') }}</th>
                            <th class="text-end">{{ __('İşlem') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transacted_at?->format('d.m.Y') }}</td>
                                <td>{{ $transaction->bankAccount?->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : 'danger' }}">{{ $transaction->type === 'deposit' ? __('Giriş') : __('Çıkış') }}</span>
                                </td>
                                <td class="text-end">{{ number_format($transaction->amount, 2) }} {{ $transaction->currency }}</td>
                                <td>{{ $transaction->reference ?? '—' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.finance.cash-panel.transactions.destroy', $transaction) }}" onsubmit="return confirm('{{ __('Bu hareketi silmek istediğinize emin misiniz?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui-button type="submit" size="sm" variant="ghost">{{ __('Sil') }}</x-ui-button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-ui-empty title="{{ __('Henüz hareket yok') }}" description="{{ __('Sağ üstteki formdan yeni bir hareket ekleyin.') }}" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
