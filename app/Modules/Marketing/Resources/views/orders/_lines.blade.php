@php
    $pricingService = app(\App\Modules\Marketing\Application\Services\PricingService::class);
    $calculation = $pricingService->calculate($lines);
    $displayLines = $calculation['lines'];
@endphp

<div
    class="crm-line-editor marketing-line-editor"
    data-crm-line-editor data-marketing-line-editor
    data-line-prefix="lines"
    data-default-tax="{{ (float) config('marketing.module.default_tax_rate', 20) }}"
>
    <div class="table-responsive">
        <table class="table align-middle" id="order-lines" data-crm-line-table data-marketing-line-table>
            <thead>
                <tr>
                    <th>{{ __('Description') }}</th>
                    <th class="w-15">{{ __('Qty') }}</th>
                    <th class="w-15">{{ __('Unit Price') }}</th>
                    <th class="w-10">{{ __('Discount %') }}</th>
                    <th class="w-10">{{ __('Tax %') }}</th>
                    <th class="text-end">{{ __('Line Total') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody data-crm-line-body data-marketing-line-body>
                @forelse($displayLines as $index => $line)
                    <tr data-crm-line-row data-marketing-line-row>
                        <td>
                            <x-ui-input
                                class="mb-0"
                                name="lines[{{ $index }}][description]"
                                data-field="description"
                                :value="$line['description']"
                                required
                            />
                        </td>
                        <td>
                            <x-ui-input
                                class="mb-0"
                                name="lines[{{ $index }}][qty]"
                                data-field="qty"
                                type="number"
                                step="0.001"
                                :value="$line['qty']"
                                required
                            />
                        </td>
                        <td>
                            <x-ui-input
                                class="mb-0"
                                name="lines[{{ $index }}][unit_price]"
                                data-field="unit_price"
                                type="number"
                                step="0.01"
                                :value="$line['unit_price']"
                                required
                            />
                        </td>
                        <td>
                            <x-ui-input
                                class="mb-0"
                                name="lines[{{ $index }}][discount_rate]"
                                data-field="discount_rate"
                                type="number"
                                step="0.01"
                                :value="$line['discount_rate']"
                            />
                        </td>
                        <td>
                            <x-ui-input
                                class="mb-0"
                                name="lines[{{ $index }}][tax_rate]"
                                data-field="tax_rate"
                                type="number"
                                step="0.01"
                                :value="$line['tax_rate']"
                            />
                        </td>
                        <td class="text-end fw-semibold" data-field="line_total">{{ number_format($line['line_total'], 2) }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-crm-line-remove data-marketing-line-remove>&times;</button>
                        </td>
                    </tr>
                @empty
                    <tr data-crm-line-row data-marketing-line-row>
                        <td><x-ui-input class="mb-0" name="lines[0][description]" data-field="description" required /></td>
                        <td><x-ui-input class="mb-0" name="lines[0][qty]" data-field="qty" type="number" step="0.001" value="1" required /></td>
                        <td><x-ui-input class="mb-0" name="lines[0][unit_price]" data-field="unit_price" type="number" step="0.01" value="0" required /></td>
                        <td><x-ui-input class="mb-0" name="lines[0][discount_rate]" data-field="discount_rate" type="number" step="0.01" value="0" /></td>
                        <td><x-ui-input class="mb-0" name="lines[0][tax_rate]" data-field="tax_rate" type="number" step="0.01" value="{{ (float) config('marketing.module.default_tax_rate', 20) }}" /></td>
                        <td class="text-end fw-semibold" data-field="line_total">0.00</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger" data-crm-line-remove data-marketing-line-remove>&times;</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex gap-2">
        <x-ui-button type="button" variant="outline" data-crm-line-add data-marketing-line-add>{{ __('Add Line') }}</x-ui-button>
    </div>

    <div class="mt-3 ms-auto" style="max-width: 320px;" data-crm-line-totals data-marketing-line-totals>
        <div class="d-flex justify-content-between"><span>{{ __('Subtotal') }}</span><span data-total="subtotal">{{ number_format($calculation['subtotal'], 2) }}</span></div>
        <div class="d-flex justify-content-between"><span>{{ __('Discount') }}</span><span data-total="discount">{{ number_format($calculation['discount_total'], 2) }}</span></div>
        <div class="d-flex justify-content-between"><span>{{ __('Tax') }}</span><span data-total="tax">{{ number_format($calculation['tax_total'], 2) }}</span></div>
        <div class="d-flex justify-content-between fw-semibold"><span>{{ __('Grand Total') }}</span><span data-total="grand">{{ number_format($calculation['grand_total'], 2) }}</span></div>
    </div>

    <template data-crm-line-template data-marketing-line-template>
        <tr data-crm-line-row data-marketing-line-row>
            <td><x-ui-input class="mb-0" name="lines[__INDEX__][description]" data-field="description" required /></td>
            <td><x-ui-input class="mb-0" name="lines[__INDEX__][qty]" data-field="qty" type="number" step="0.001" value="1" required /></td>
            <td><x-ui-input class="mb-0" name="lines[__INDEX__][unit_price]" data-field="unit_price" type="number" step="0.01" value="0" required /></td>
            <td><x-ui-input class="mb-0" name="lines[__INDEX__][discount_rate]" data-field="discount_rate" type="number" step="0.01" value="0" /></td>
            <td><x-ui-input class="mb-0" name="lines[__INDEX__][tax_rate]" data-field="tax_rate" type="number" step="0.01" value="{{ (float) config('marketing.module.default_tax_rate', 20) }}" /></td>
            <td class="text-end fw-semibold" data-field="line_total">0.00</td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" data-crm-line-remove data-marketing-line-remove>&times;</button></td>
        </tr>
    </template>
</div>
