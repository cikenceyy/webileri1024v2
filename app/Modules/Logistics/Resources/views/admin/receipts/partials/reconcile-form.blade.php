@php($varianceEnabled = config('features.logistics.variance_reason_codes', true))
@php use Illuminate\Support\Str; @endphp

<form method="post" action="{{ route('admin.logistics.receipts.reconcile', $receipt) }}" class="card mb-4">
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ (string) Str::uuid() }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Varyans Uzlaşması</span>
        <button class="btn btn-sm btn-primary">Kaydet</button>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Ürün</th>
                    <th>Beklenen</th>
                    <th>Alınan</th>
                    <th>Varyans</th>
                    @if ($varianceEnabled)
                        <th>Gerekçe</th>
                    @endif
                    <th>Not</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($receipt->lines as $line)
                    <tr>
                        <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                        <td>{{ number_format($line->qty_expected ?? 0, 3) }}</td>
                        <td>{{ number_format($line->qty_received ?? 0, 3) }}</td>
                        <td>{{ number_format(($line->qty_received ?? 0) - ($line->qty_expected ?? 0), 3) }}</td>
                        <input type="hidden" name="lines[{{ $loop->index }}][id]" value="{{ $line->id }}">
                        @if ($varianceEnabled)
                            <td>
                                <input type="text" name="lines[{{ $loop->index }}][variance_reason]" value="{{ old("lines.{$loop->index}.variance_reason", $line->variance_reason) }}" class="form-control form-control-sm">
                            </td>
                        @endif
                        <td>
                            <input type="text" name="lines[{{ $loop->index }}][notes]" value="{{ old("lines.{$loop->index}.notes", $line->notes) }}" class="form-control form-control-sm">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
