<div class="row g-4">
    <div class="col-md-6">
        <x-ui.input name="code" :label="__('Customer Code')" :value="old('code', $customer->code ?? '')" required />
    </div>
    <div class="col-md-6">
        <x-ui.input name="name" :label="__('Customer Name')" :value="old('name', $customer->name ?? '')" required />
    </div>
    <div class="col-md-6">
        <x-ui.input name="email" type="email" :label="__('Email')" :value="old('email', $customer->email ?? '')" />
    </div>
    <div class="col-md-6">
        <x-ui.input name="phone" :label="__('Phone')" :value="old('phone', $customer->phone ?? '')" />
    </div>
    <div class="col-md-6">
        <x-ui.input name="tax_no" :label="__('Tax Number')" :value="old('tax_no', $customer->tax_no ?? '')" />
    </div>
    <div class="col-md-6">
        <x-ui.select name="status" :label="__('Status')" :value="old('status', $customer->status ?? 'active')">
            <option value="active">{{ __('Active') }}</option>
            <option value="inactive">{{ __('Inactive') }}</option>
        </x-ui.select>
    </div>
    <div class="col-md-6">
        <x-ui.input name="payment_terms" :label="__('Payment Terms')" :value="old('payment_terms', $customer->payment_terms ?? '')" />
    </div>
    <div class="col-md-3">
        <x-ui.input name="credit_limit" type="number" step="0.01" :label="__('Credit Limit')" :value="old('credit_limit', $customer->credit_limit ?? '')" />
    </div>
    <div class="col-md-3">
        <x-ui.input name="balance" type="number" step="0.01" :label="__('Balance')" :value="old('balance', $customer->balance ?? '')" />
    </div>
    <div class="col-12">
        <x-ui.textarea name="address" :label="__('Default Address')" :value="old('address', $customer->address ?? '')" rows="3" />
    </div>
</div>
