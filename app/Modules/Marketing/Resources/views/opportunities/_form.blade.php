<div class="row g-4">
    <div class="col-md-6">
        <x-ui.select name="customer_id" :label="__('Customer')" :value="old('customer_id', $opportunity->customer_id)">
            <option value="">—</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $opportunity->customer_id)==$customer->id)>{{ $customer->name }}</option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-6">
        <x-ui.input name="title" :label="__('Title')" :value="old('title', $opportunity->title)" required />
    </div>
    <div class="col-md-4">
        <x-ui.input name="amount" type="number" step="0.01" :label="__('Amount')" :value="old('amount', $opportunity->amount)" />
    </div>
    <div class="col-md-4">
        <x-ui.select name="currency" :label="__('Currency')" :value="old('currency', $opportunity->currency ?? 'TRY')">
            @foreach(['TRY','USD','EUR'] as $currency)
                <option value="{{ $currency }}" @selected(old('currency', $opportunity->currency ?? 'TRY')===$currency)>{{ $currency }}</option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-4">
        <x-ui.select name="stage" :label="__('Stage')" :value="old('stage', $opportunity->stage ?? 'new')">
            @foreach(['new','qualified','proposal','won','lost'] as $stage)
                <option value="{{ $stage }}" @selected(old('stage', $opportunity->stage ?? 'new')===$stage)>{{ ucfirst($stage) }}</option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-4">
        <x-ui.input name="probability" type="number" step="1" min="0" max="100" :label="__('Probability %')" :value="old('probability', $opportunity->probability)" />
    </div>
    <div class="col-md-4">
        <x-ui.input name="close_date" type="date" :label="__('Expected Close')" :value="old('close_date', optional($opportunity->close_date)->format('Y-m-d'))" />
    </div>
</div>
