@csrf
<div class="row g-3">
    <div class="col-md-6">
        <x-ui-input name="name" :label="__('Account Name')" :value="old('name', $account->name ?? '')" required />
    </div>
    <div class="col-md-6">
        <x-ui-input name="account_no" :label="__('Account Number')" :value="old('account_no', $account->account_no ?? '')" />
    </div>
    <div class="col-md-4">
        <x-ui-select name="currency" :label="__('Currency')" :options="collect($currencies)->map(fn($c) => ['value' => $c, 'label' => $c])->toArray()" :value="old('currency', $account->currency ?? $currencies[0])" />
    </div>
    <div class="col-md-4">
        <x-ui-select name="status" :label="__('Status')" :options="[['value' => 'active', 'label' => __('Active')], ['value' => 'inactive', 'label' => __('Inactive')]]" :value="old('status', $account->status ?? 'active')" />
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_default" value="1" @checked(old('is_default', $account->is_default ?? false))>
            <label class="form-check-label">{{ __('Default account') }}</label>
        </div>
    </div>
</div>
