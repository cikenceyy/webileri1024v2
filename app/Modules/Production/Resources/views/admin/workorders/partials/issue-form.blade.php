@if(($workOrder->status === 'released' || $workOrder->status === 'in_progress') && auth()->user()?->can('issue', $workOrder))
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Malzeme Çıkışı</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.production.workorders.issue', $workOrder) }}" method="post">
                @csrf
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>Bileşen</th>
                            <th>Gereken</th>
                            <th>Çıkılacak</th>
                            <th>Depo</th>
                            <th>Raf</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($requirements as $index => $requirement)
                            <tr>
                                <td>{{ $requirement['item']->component?->name ?? 'Malzeme' }}</td>
                                <td>{{ number_format($requirement['required_qty'], 3) }}</td>
                                <td>
                                    <input type="number" step="0.001" name="lines[{{ $index }}][qty]" class="form-control" value="{{ number_format($requirement['required_qty'], 3, '.', '') }}">
                                    <input type="hidden" name="lines[{{ $index }}][component_product_id]" value="{{ $requirement['item']->component_product_id }}">
                                    <input type="hidden" name="lines[{{ $index }}][component_variant_id]" value="{{ $requirement['item']->component_variant_id }}">
                                </td>
                                <td>
                                    <select name="lines[{{ $index }}][warehouse_id]" class="form-select" required>
                                        <option value="">Seçiniz</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" @selected(($settingsDefaults['production_issue_warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="lines[{{ $index }}][bin_id]" class="form-select">
                                        <option value="">Seçiniz</option>
                                        @foreach($bins as $bin)
                                            <option value="{{ $bin->id }}">{{ $bin->code }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-outline-primary">Çıkışı Kaydet</button>
                </div>
            </form>
        </div>
    </div>
@endif
