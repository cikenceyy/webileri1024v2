@csrf
<div class="row g-3">
    <div class="col-md-4">
        <x-ui-select name="customer_id" :label="__('Customer')" :options="$customers->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()" :value="old('customer_id', $receipt->customer_id ?? null)" />
    </div>
    <div class="col-md-4">
        <x-ui-input name="receipt_no" :label="__('Receipt Number')" :value="old('receipt_no', $receipt->receipt_no ?? '')" placeholder="{{ __('Auto-generated if empty') }}" />
    </div>
    <div class="col-md-4">
        <x-ui-input type="date" name="receipt_date" :label="__('Receipt Date')" :value="old('receipt_date', optional($receipt->receipt_date ?? now())->format('Y-m-d'))" required />
    </div>
    <div class="col-md-4">
        <x-ui-select name="currency" :label="__('Currency')" :options="collect($currencies)->map(fn($c) => ['value' => $c, 'label' => $c])->toArray()" :value="old('currency', $receipt->currency ?? $currencies[0])" />
    </div>
    <div class="col-md-4">
        <x-ui-input type="number" step="0.01" min="0.01" name="amount" :label="__('Amount')" :value="old('amount', $receipt->amount ?? 0)" required />
    </div>
    <div class="col-md-4">
        <x-ui-select name="bank_account_id" :label="__('Bank Account')" :options="$bankAccounts->map(fn($a) => ['value' => $a->id, 'label' => $a->name])->toArray()" :placeholder="__('Select account')" :value="old('bank_account_id', $receipt->bank_account_id ?? null)" />
    </div>
    <div class="col-12">
        <x-ui-textarea name="notes" :label="__('Notes')" rows="3">{{ old('notes', $receipt->notes ?? '') }}</x-ui-textarea>
    </div>
</div>
