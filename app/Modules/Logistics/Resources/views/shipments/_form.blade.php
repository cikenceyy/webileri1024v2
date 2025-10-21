@php($shipment = $shipment ?? null)

<div class="row g-4">
    <div class="col-md-4">
        <x-ui.input
            name="shipment_no"
            label="Sevkiyat No"
            :value="old('shipment_no', $shipment?->shipment_no)"
            placeholder="Boş bırakılırsa otomatik atanır"
        />
    </div>
    <div class="col-md-4">
        <x-ui.input
            type="date"
            name="ship_date"
            label="Sevk Tarihi"
            :value="old('ship_date', optional($shipment?->ship_date)->format('Y-m-d') ?? now()->format('Y-m-d'))"
            required
        />
    </div>
    <div class="col-md-4">
        <x-ui.select name="status" label="Durum" required>
            @php($status = old('status', $shipment?->status ?? 'draft'))
            <option value="draft" @selected($status === 'draft')>Taslak</option>
            <option value="preparing" @selected($status === 'preparing')>Hazırlanıyor</option>
            <option value="in_transit" @selected($status === 'in_transit')>Yolda</option>
            <option value="delivered" @selected($status === 'delivered')>Teslim Edildi</option>
            <option value="cancelled" @selected($status === 'cancelled')>İptal</option>
        </x-ui.select>
    </div>
    <div class="col-md-6">
        <x-ui.select name="customer_id" label="Müşteri">
            <option value="">Seçiniz</option>
            @foreach($customerOptions as $option)
                <option value="{{ $option['value'] }}" @selected((int) old('customer_id', $shipment?->customer_id ?? 0) === (int) $option['value'])>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-6">
        <x-ui.select name="order_id" label="Sipariş">
            <option value="">Seçiniz</option>
            @foreach($orderOptions as $option)
                <option value="{{ $option['value'] }}" @selected((int) old('order_id', $shipment?->order_id ?? 0) === (int) $option['value'])>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </x-ui.select>
    </div>
    <div class="col-md-4">
        <x-ui.input
            name="carrier"
            label="Kargo Firması"
            :value="old('carrier', $shipment?->carrier)"
            placeholder="Aras, Yurtiçi..."
        />
    </div>
    <div class="col-md-4">
        <x-ui.input
            name="tracking_no"
            label="Takip Numarası"
            :value="old('tracking_no', $shipment?->tracking_no)"
        />
    </div>
    <div class="col-md-4">
        <x-ui.input
            type="number"
            name="package_count"
            label="Koli Adedi"
            min="0"
            step="1"
            :value="old('package_count', $shipment?->package_count)"
        />
    </div>
    <div class="col-md-4">
        <x-ui.input
            type="number"
            name="weight_kg"
            label="Ağırlık (kg)"
            min="0"
            step="0.001"
            :value="old('weight_kg', $shipment?->weight_kg)"
        />
    </div>
    <div class="col-md-4">
        <x-ui.input
            type="number"
            name="volume_dm3"
            label="Hacim (dm³)"
            min="0"
            step="0.001"
            :value="old('volume_dm3', $shipment?->volume_dm3)"
        />
    </div>
    <div class="col-12">
        <x-ui.textarea name="notes" label="Notlar" rows="4">{{ old('notes', $shipment?->notes) }}</x-ui.textarea>
    </div>
</div>
