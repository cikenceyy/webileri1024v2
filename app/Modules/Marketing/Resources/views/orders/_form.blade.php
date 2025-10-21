@php($lines = $order->lines->map(fn($line) => [
    'description' => $line->description,
    'qty' => (float) $line->qty,
    'unit_price' => (float) $line->unit_price,
    'discount_rate' => (float) $line->discount_rate,
    'tax_rate' => (float) $line->tax_rate,
    'line_total' => (float) $line->line_total,
])->toArray() ?: [[
    'description' => '',
    'qty' => 1,
    'unit_price' => 0,
    'discount_rate' => 0,
    'tax_rate' => config('marketing.module.default_tax_rate'),
    'line_total' => 0,
]])
@endphp

<div class="row g-4">
    <div class="col-md-6">
        <x-ui.select name="customer_id" :label="__('Customer')" :value="old('customer_id', $order->customer_id)">
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $order->customer_id)==$customer->id)>{{ $customer->name }}</option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-6">
        <x-ui.select name="contact_id" :label="__('Contact')" :value="old('contact_id', $order->contact_id)">
            <option value="">—</option>
            @foreach($contacts as $contact)
                <option value="{{ $contact->id }}" @selected(old('contact_id', $order->contact_id)==$contact->id)>{{ $contact->name }}</option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-4">
        <x-ui.input name="order_no" :label="__('Order No')" :value="old('order_no', $order->order_no)" />
    </div>
    <div class="col-md-4">
        <x-ui.input name="order_date" type="date" :label="__('Order Date')" :value="old('order_date', optional($order->order_date)->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
    </div>
    <div class="col-md-4">
        <x-ui.input name="due_date" type="date" :label="__('Due Date')" :value="old('due_date', optional($order->due_date)->format('Y-m-d'))" />
    </div>
    <div class="col-md-4">
        <x-ui.select name="currency" :label="__('Currency')" :value="old('currency', $order->currency ?? config('inventory.default_currency', 'TRY'))">
            @foreach(['TRY','USD','EUR'] as $currency)
                <option value="{{ $currency }}" @selected(old('currency', $order->currency ?? 'TRY')===$currency)>{{ $currency }}</option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-4">
        <x-ui.select name="status" :label="__('Status')" :value="old('status', $order->status ?? 'draft')">
            @foreach(['draft','confirmed','shipped','cancelled'] as $status)
                <option value="{{ $status }}" @selected(old('status', $order->status ?? 'draft')===$status)>{{ ucfirst($status) }}</option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-12">
        <x-ui.textarea name="notes" :label="__('Notes')" :value="old('notes', $order->notes)" rows="3" />
    </div>
    <div class="col-12">
        @include('marketing::orders._lines', ['lines' => $lines])
    </div>
</div>
