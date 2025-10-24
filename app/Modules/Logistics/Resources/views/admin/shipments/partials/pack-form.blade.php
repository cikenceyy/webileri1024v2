<form method="post" action="{{ route('admin.logistics.shipments.pack', $shipment) }}" class="card mb-4">
    @csrf
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Paketleme</span>
        <div class="d-flex gap-2">
            <input type="number" name="packages_count" value="{{ old('packages_count', $shipment->packages_count) }}" class="form-control form-control-sm" placeholder="Paket" min="0">
            <input type="number" step="0.001" name="gross_weight" value="{{ old('gross_weight', $shipment->gross_weight) }}" class="form-control form-control-sm" placeholder="Brüt">
            <input type="number" step="0.001" name="net_weight" value="{{ old('net_weight', $shipment->net_weight) }}" class="form-control form-control-sm" placeholder="Net">
            <button class="btn btn-sm btn-primary">Kaydet</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th>Toplanan</th>
                    <th>Paketlenen</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shipment->lines as $line)
                    <tr>
                        <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                        <td>{{ number_format($line->picked_qty, 3) }}</td>
                        <td>
                            <input type="hidden" name="lines[{{ $loop->index }}][id]" value="{{ $line->id }}">
                            <input type="number" step="0.001" min="0" name="lines[{{ $loop->index }}][packed_qty]" value="{{ old("lines.{$loop->index}.packed_qty", $line->packed_qty) }}" class="form-control form-control-sm">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
