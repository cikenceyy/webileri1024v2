<form method="post" action="{{ route('admin.logistics.shipments.pick', $shipment) }}" class="card mb-4">
    @csrf
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Toplama</span>
        <button class="btn btn-sm btn-primary">Kaydet</button>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th>İstenen</th>
                    <th>Toplanan</th>
                    <th>Depo</th>
                    <th>Raf</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shipment->lines as $line)
                    <tr>
                        <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                        <td>{{ number_format($line->qty, 3) }}</td>
                        <td>
                            <input type="hidden" name="lines[{{ $loop->index }}][id]" value="{{ $line->id }}">
                            <input type="number" step="0.001" min="0" name="lines[{{ $loop->index }}][picked_qty]" value="{{ old("lines.{$loop->index}.picked_qty", $line->picked_qty) }}" class="form-control form-control-sm">
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
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
