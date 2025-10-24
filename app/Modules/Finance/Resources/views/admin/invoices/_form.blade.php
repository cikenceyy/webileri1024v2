@php
    use Illuminate\Support\Arr;
    $linePayload = old('lines', isset($invoice) ? $invoice->lines->map(function ($line) {
        return [
            'id' => $line->id,
            'product_id' => $line->product_id,
            'variant_id' => $line->variant_id,
            'description' => $line->description,
            'qty' => $line->qty,
            'uom' => $line->uom,
            'unit_price' => $line->unit_price,
            'discount_pct' => $line->discount_pct,
            'tax_rate' => $line->tax_rate,
        ];
    })->toArray() : []);
@endphp

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="mb-3">
                    <label for="customer_id" class="form-label">{{ __('Customer') }}</label>
                    <select name="customer_id" id="customer_id" class="form-select" required>
                        <option value="">{{ __('Select customer') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $invoice->customer_id ?? null) == $customer->id)>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="order_id" class="form-label">{{ __('Sales Order (optional)') }}</label>
                    <select name="order_id" id="order_id" class="form-select">
                        <option value="">{{ __('No reference') }}</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" @selected(old('order_id', $invoice->order_id ?? null) == $order->id)>{{ $order->doc_no }}</option>
                        @endforeach
                    </select>
                    @error('order_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="currency" class="form-label">{{ __('Currency') }}</label>
                    <input type="text" name="currency" id="currency" class="form-control" maxlength="3" value="{{ old('currency', $invoice->currency ?? $defaults['currency']) }}" required>
                    @error('currency')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 form-check">
                    <input type="hidden" name="tax_inclusive" value="0">
                    <input type="checkbox" class="form-check-input" id="tax_inclusive" name="tax_inclusive" value="1" @checked(old('tax_inclusive', $invoice->tax_inclusive ?? $defaults['tax_inclusive']))>
                    <label class="form-check-label" for="tax_inclusive">{{ __('Prices include tax') }}</label>
                    @error('tax_inclusive')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="payment_terms_days" class="form-label">{{ __('Payment Terms (days)') }}</label>
                    <input type="number" min="0" max="180" class="form-control" id="payment_terms_days" name="payment_terms_days" value="{{ old('payment_terms_days', $invoice->payment_terms_days ?? $defaults['payment_terms_days']) }}" required>
                    @error('payment_terms_days')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                    @error('notes')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 mb-0">{{ __('Lines') }}</h2>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-line">{{ __('Add line') }}</button>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle" id="invoice-lines">
                        <thead>
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th style="width:100px">{{ __('Qty') }}</th>
                            <th style="width:90px">{{ __('UOM') }}</th>
                            <th style="width:140px">{{ __('Unit Price') }}</th>
                            <th style="width:110px">{{ __('Discount %') }}</th>
                            <th style="width:110px">{{ __('Tax %') }}</th>
                            <th style="width:40px"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($linePayload as $index => $line)
                            @include('finance::admin.invoices.partials.line-row', ['index' => $index, 'line' => $line, 'tax_rate_default' => $tax_rate_default])
                        @empty
                            @include('finance::admin.invoices.partials.line-row', ['index' => 0, 'line' => [], 'tax_rate_default' => $tax_rate_default])
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @error('lines')<div class="text-danger small">{{ $message }}</div>@enderror
                @foreach($errors->get('lines.*.*') as $fieldErrors)
                    @foreach($fieldErrors as $fieldError)
                        <div class="text-danger small">{{ $fieldError }}</div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="invoice-line-template">
@include('finance::admin.invoices.partials.line-row', ['index' => '__INDEX__', 'line' => [], 'tax_rate_default' => $tax_rate_default])
</script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.querySelector('#invoice-lines tbody');
            const addButton = document.querySelector('#add-line');
            const templateHtml = document.querySelector('#invoice-line-template')?.innerHTML || '';
            let lineIndex = tableBody.querySelectorAll('tr').length;

            addButton?.addEventListener('click', () => {
                if (!templateHtml) {
                    return;
                }
                const html = templateHtml.replace(/__INDEX__/g, lineIndex);
                const wrapper = document.createElement('tbody');
                wrapper.innerHTML = html;
                const row = wrapper.firstElementChild;
                tableBody.appendChild(row);
                lineIndex++;
            });

            tableBody.addEventListener('click', (event) => {
                if (event.target.matches('.btn-remove-line')) {
                    const row = event.target.closest('tr');
                    if (row && tableBody.children.length > 1) {
                        row.remove();
                    }
                }
            });
        });
    </script>
@endpush
