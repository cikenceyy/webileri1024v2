@csrf
<div class="row g-3">
    <div class="col-md-4">
        <x-ui-select name="customer_id" :label="__('Customer')" :options="$customers->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()" :value="old('customer_id', $invoice->customer_id ?? null)" />
    </div>
    <div class="col-md-4">
        <x-ui-input name="invoice_no" :label="__('Invoice Number')" :value="old('invoice_no', $invoice->invoice_no ?? '')" placeholder="{{ __('Auto-generated if empty') }}" />
    </div>
    <div class="col-md-2">
        <x-ui-input type="date" name="issue_date" :label="__('Issue Date')" :value="old('issue_date', optional($invoice->issue_date ?? now())->format('Y-m-d'))" required />
    </div>
    <div class="col-md-2">
        <x-ui-input type="date" name="due_date" :label="__('Due Date')" :value="old('due_date', optional($invoice->due_date ?? null)?->format('Y-m-d'))" />
    </div>
    <div class="col-md-3">
        <x-ui-select name="currency" :label="__('Currency')" :options="collect($currencies)->map(fn($c) => ['value' => $c, 'label' => $c])->toArray()" :value="old('currency', $invoice->currency ?? $currencies[0])" />
    </div>
    <div class="col-md-3">
        <x-ui-input type="number" step="0.01" min="0" name="shipping_total" :label="__('Shipping Total')" :value="old('shipping_total', $invoice->shipping_total ?? 0)" />
    </div>
    <div class="col-md-6">
        <x-ui-textarea name="notes" :label="__('Notes')" rows="2">{{ old('notes', $invoice->notes ?? '') }}</x-ui-textarea>
    </div>
</div>

<hr class="my-4">

<h5 class="mb-3">{{ __('Line Items') }}</h5>
@php($oldLines = collect(old('lines', isset($invoice) ? $invoice->lines->toArray() : [])))

<div
    class="finance-invoice-lines"
    data-finance-invoice
    data-default-tax="{{ $default_tax }}"
>
    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="invoice-lines" data-invoice-table>
            <thead class="table-light">
                <tr>
                    <th style="width: 28%">{{ __('Description') }}</th>
                    <th style="width: 10%">{{ __('Qty') }}</th>
                    <th style="width: 10%">{{ __('Unit') }}</th>
                    <th style="width: 12%">{{ __('Unit Price') }}</th>
                    <th style="width: 10%">{{ __('Discount %') }}</th>
                    <th style="width: 10%">{{ __('Tax %') }}</th>
                    <th style="width: 12%">{{ __('Line Total') }}</th>
                    <th style="width: 8%"></th>
                </tr>
            </thead>
            <tbody data-invoice-body>
                @forelse($oldLines as $index => $line)
                    <tr data-invoice-row>
                        <td>
                            <input type="hidden" name="lines[{{ $index }}][id]" value="{{ $line['id'] ?? '' }}">
                            <x-ui-input :name="'lines['.$index.'][description]'" :value="$line['description'] ?? ''" required />
                        </td>
                        <td><x-ui-input type="number" step="0.001" min="0" :name="'lines['.$index.'][qty]'" :value="$line['qty'] ?? 1" data-field="qty" required /></td>
                        <td><x-ui-input :name="'lines['.$index.'][unit]'" :value="$line['unit'] ?? 'pcs'" /></td>
                        <td><x-ui-input type="number" step="0.01" min="0" :name="'lines['.$index.'][unit_price]'" :value="$line['unit_price'] ?? 0" data-field="unit_price" required /></td>
                        <td><x-ui-input type="number" step="0.01" min="0" max="100" :name="'lines['.$index.'][discount_rate]'" :value="$line['discount_rate'] ?? 0" data-field="discount_rate" /></td>
                        <td><x-ui-input type="number" step="0.01" min="0" max="100" :name="'lines['.$index.'][tax_rate]'" :value="$line['tax_rate'] ?? $default_tax" data-field="tax_rate" /></td>
                        <td class="line-total text-end" data-field="line_total">{{ number_format($line['line_total'] ?? 0, 2) }}</td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-invoice-remove>&times;</button></td>
                    </tr>
                @empty
                    <tr data-invoice-row>
                        <td>
                            <input type="hidden" name="lines[0][id]">
                            <x-ui-input name="lines[0][description]" value="" required />
                        </td>
                        <td><x-ui-input type="number" step="0.001" min="0" name="lines[0][qty]" value="1" data-field="qty" required /></td>
                        <td><x-ui-input name="lines[0][unit]" value="pcs" /></td>
                        <td><x-ui-input type="number" step="0.01" min="0" name="lines[0][unit_price]" value="0" data-field="unit_price" required /></td>
                        <td><x-ui-input type="number" step="0.01" min="0" max="100" name="lines[0][discount_rate]" value="0" data-field="discount_rate" /></td>
                        <td><x-ui-input type="number" step="0.01" min="0" max="100" name="lines[0][tax_rate]" value="{{ $default_tax }}" data-field="tax_rate" /></td>
                        <td class="line-total text-end" data-field="line_total">0.00</td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-invoice-remove>&times;</button></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2" data-invoice-controls>
        <x-ui-button type="button" variant="outline" data-invoice-add>{{ __('Add line') }}</x-ui-button>
        <div class="text-muted small" data-invoice-summary>{{ __('Totals update automatically while editing.') }}</div>
    </div>

    <template data-invoice-template>
        <tr data-invoice-row>
            <td>
                <input type="hidden" name="lines[__INDEX__][id]">
                <x-ui-input name="lines[__INDEX__][description]" value="" required />
            </td>
            <td><x-ui-input type="number" step="0.001" min="0" name="lines[__INDEX__][qty]" value="1" data-field="qty" required /></td>
            <td><x-ui-input name="lines[__INDEX__][unit]" value="pcs" /></td>
            <td><x-ui-input type="number" step="0.01" min="0" name="lines[__INDEX__][unit_price]" value="0" data-field="unit_price" required /></td>
            <td><x-ui-input type="number" step="0.01" min="0" max="100" name="lines[__INDEX__][discount_rate]" value="0" data-field="discount_rate" /></td>
            <td><x-ui-input type="number" step="0.01" min="0" max="100" name="lines[__INDEX__][tax_rate]" value="{{ $default_tax }}" data-field="tax_rate" /></td>
            <td class="line-total text-end" data-field="line_total">0.00</td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-invoice-remove>&times;</button></td>
        </tr>
    </template>
</div>
