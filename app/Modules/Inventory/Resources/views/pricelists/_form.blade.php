@php
    $priceList = $priceList ?? null;
    $currencies = ['TRY', 'USD', 'EUR'];
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <x-ui.input name="name" label="Liste Adı" :value="old('name', $priceList?->name)" required />
    </div>
    <div class="col-md-3">
        <x-ui.select name="currency" label="Para Birimi" required>
            @foreach($currencies as $currency)
                <option value="{{ $currency }}" @selected(old('currency', $priceList?->currency ?? config('inventory.default_currency')) === $currency)>{{ $currency }}</option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-3">
        <x-ui.select name="type" label="Tür" required>
            @php($typeValue = old('type', $priceList?->type ?? 'sale'))
            <option value="sale" @selected($typeValue === 'sale')>Satış</option>
            <option value="purchase" @selected($typeValue === 'purchase')>Satın Alma</option>
        </x-ui.select>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="priceListActive" name="active" value="1" @checked(old('active', $priceList?->active ?? true))>
            <label class="form-check-label" for="priceListActive">Aktif</label>
        </div>
    </div>
</div>
