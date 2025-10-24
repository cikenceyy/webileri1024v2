@php use Illuminate\Support\Str; @endphp
@if(in_array($workOrder->status, ['in_progress', 'released']) && auth()->user()?->can('complete', $workOrder))
    <div class="card mb-3">
        <div class="card-header">Üretim Girişi</div>
        <div class="card-body">
            <form action="{{ route('admin.production.workorders.complete', $workOrder) }}" method="post" class="row g-3 align-items-end">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">
                <div class="col-md-3">
                    <label class="form-label" for="receipt_qty">Miktar</label>
                    <input type="number" step="0.001" name="qty" id="receipt_qty" class="form-control" value="{{ number_format($workOrder->target_qty, 3, '.', '') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="receipt_wh">Depo</label>
                    <select name="warehouse_id" id="receipt_wh" class="form-select" required>
                        <option value="">Seçiniz</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(($settingsDefaults['production_receipt_warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="receipt_bin">Raf</label>
                    <select name="bin_id" id="receipt_bin" class="form-select">
                        <option value="">Seçiniz</option>
                        @foreach($bins as $bin)
                            <option value="{{ $bin->id }}">{{ $bin->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button type="submit" class="btn btn-outline-success w-100">Üretimi Kaydet</button>
                </div>
            </form>
        </div>
    </div>
@endif
