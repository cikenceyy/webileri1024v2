<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">{{ __('Addresses') }}</h6>
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#address-form">{{ __('Add Address') }}</button>
</div>

<div class="collapse mb-3" id="address-form">
    <form method="post" action="{{ route('admin.marketing.addresses.store', $customer) }}" class="card card-body gap-3">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <x-ui.select name="type" :label="__('Type')">
                    <option value="billing">{{ __('Billing') }}</option>
                    <option value="shipping">{{ __('Shipping') }}</option>
                </x-ui.select>
            </div>
            <div class="col-md-8"><x-ui.input name="line1" :label="__('Line 1')" required /></div>
            <div class="col-md-6"><x-ui.input name="line2" :label="__('Line 2')" /></div>
            <div class="col-md-6"><x-ui.input name="line3" :label="__('Line 3')" /></div>
            <div class="col-md-6"><x-ui.input name="city" :label="__('City')" /></div>
            <div class="col-md-3"><x-ui.input name="state" :label="__('State')" /></div>
            <div class="col-md-3"><x-ui.input name="postal_code" :label="__('Postal Code')" /></div>
            <div class="col-md-4"><x-ui.input name="country" :label="__('Country')" placeholder="TR" /></div>
            <div class="col-12"><x-ui.checkbox name="is_primary" :label="__('Primary for this type')" value="1" /></div>
        </div>
        <div class="d-flex gap-2">
            <x-ui.button type="submit">{{ __('Save') }}</x-ui.button>
            <button class="btn btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#address-form">{{ __('Cancel') }}</button>
        </div>
    </form>
</div>

<ul class="list-group list-group-flush">
    @forelse($customer->addresses as $address)
        <li class="list-group-item d-flex justify-content-between align-items-start">
            <div>
                <div class="fw-semibold">{{ ucfirst($address->type) }} @if($address->is_primary)<span class="badge bg-success ms-2">{{ __('Primary') }}</span>@endif</div>
                <div class="text-muted small">
                    {{ $address->line1 }}<br>
                    {{ $address->line2 }} {{ $address->line3 }}<br>
                    {{ $address->city }} {{ $address->postal_code }} {{ $address->country }}
                </div>
            </div>
            <form method="post" action="{{ route('admin.marketing.addresses.destroy', $address) }}">
                @csrf
                @method('delete')
                <x-ui.button type="submit" variant="danger" size="sm">{{ __('Remove') }}</x-ui.button>
            </form>
        </li>
    @empty
        <li class="list-group-item text-muted">{{ __('No addresses recorded.') }}</li>
    @endforelse
</ul>
