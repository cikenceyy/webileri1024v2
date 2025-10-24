@php
    $line = $line ?? [];
    $index = $index ?? 0;
@endphp
<tr>
    <td>
        <input type="hidden" name="lines[{{ $index }}][id]" value="{{ $line['id'] ?? '' }}">
        <textarea name="lines[{{ $index }}][description]" class="form-control" rows="2" required>{{ $line['description'] ?? '' }}</textarea>
    </td>
    <td>
        <input type="number" step="0.001" min="0.001" name="lines[{{ $index }}][qty]" class="form-control" value="{{ $line['qty'] ?? '' }}" required>
    </td>
    <td>
        <input type="text" name="lines[{{ $index }}][uom]" class="form-control" value="{{ $line['uom'] ?? 'pcs' }}">
    </td>
    <td>
        <input type="number" step="0.0001" min="0" name="lines[{{ $index }}][unit_price]" class="form-control" value="{{ $line['unit_price'] ?? '' }}" required>
    </td>
    <td>
        <input type="number" step="0.01" min="0" max="100" name="lines[{{ $index }}][discount_pct]" class="form-control" value="{{ $line['discount_pct'] ?? 0 }}">
    </td>
    <td>
        <input type="number" step="0.01" min="0" max="50" name="lines[{{ $index }}][tax_rate]" class="form-control" value="{{ $line['tax_rate'] ?? $tax_rate_default ?? 0 }}">
    </td>
    <td class="text-end">
        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-line">&times;</button>
    </td>
</tr>
