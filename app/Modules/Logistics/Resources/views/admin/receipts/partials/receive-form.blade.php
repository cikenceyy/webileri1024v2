@php
    $varianceEnabled = $varianceEnabled ?? config('features.logistics.variance_reason_codes', true);
@endphp

<form method="post" action="{{ route('admin.logistics.receipts.receive', $receipt) }}" class="card mb-4">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Mal Kabul</span>
        <div class="d-flex gap-2">
            <select name="warehouse_id" class="form-select form-select-sm">
                <option value="">Depo Seç</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id', $receipt->warehouse_id) == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary">Kaydet</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th>Beklenen</th>
                    <th>Alınan</th>
                    <th>Depo</th>
                    <th>Raf</th>
                    @if ($varianceEnabled)
                        <th>Varyans Notu</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($receipt->lines as $line)
                    <tr>
                        <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                        <td>{{ number_format($line->qty_expected ?? 0, 3) }}</td>
                        <td>
                            <input type="hidden" name="lines[{{ $loop->index }}][id]" value="{{ $line->id }}">
                            <input type="number" step="0.001" min="0" name="lines[{{ $loop->index }}][qty_received]" value="{{ old("lines.{$loop->index}.qty_received", $line->qty_received) }}" class="form-control form-control-sm">
                            <input type="hidden" name="lines[{{ $loop->index }}][qty_expected]" value="{{ old("lines.{$loop->index}.qty_expected", $line->qty_expected) }}">
                        </td>
                        <td>
                            <select name="lines[{{ $loop->index }}][warehouse_id]" class="form-select form-select-sm">
                                <option value="">Seçiniz</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old("lines.{$loop->index}.warehouse_id", $line->warehouse_id) == $warehouse->id)>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="lines[{{ $loop->index }}][bin_id]" class="form-select form-select-sm">
                                <option value="">Seçiniz</option>
                                @foreach ($bins as $bin)
                                    <option value="{{ $bin->id }}" @selected(old("lines.{$loop->index}.bin_id", $line->bin_id) == $bin->id)>{{ $bin->code }}</option>
                                @endforeach
                            </select>
                        </td>
                        @if ($varianceEnabled)
                            <td>
                                <input type="text" name="lines[{{ $loop->index }}][variance_reason]" value="{{ old("lines.{$loop->index}.variance_reason", $line->variance_reason) }}" class="form-control form-control-sm">
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
