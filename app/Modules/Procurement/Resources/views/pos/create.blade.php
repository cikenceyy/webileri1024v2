@extends('layouts.admin')

@section('title', 'Yeni Satınalma Siparişi')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1">Yeni Satınalma Siparişi</h1>
        <p class="text-muted mb-0">Tedarikçiden ürün taleplerinizi planlayın.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Form hataları mevcut.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.procurement.pos.store') }}" method="post" id="po-create-form">
        @csrf
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="supplier_id" class="form-label">Tedarikçi ID</label>
                        <input type="number" name="supplier_id" id="supplier_id" class="form-control" value="{{ old('supplier_id') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label for="currency" class="form-label">Para Birimi</label>
                        <input type="text" name="currency" id="currency" class="form-control" value="{{ old('currency', 'TRY') }}" maxlength="3" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Sipariş Satırları</h2>
                <button type="button" class="btn btn-sm btn-outline-primary" id="add-line">Satır Ekle</button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" id="line-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 15%">Ürün ID</th>
                                <th scope="col">Açıklama</th>
                                <th scope="col" style="width: 15%">Miktar</th>
                                <th scope="col" style="width: 10%">Birim</th>
                                <th scope="col" style="width: 15%">Birim Fiyat</th>
                                <th scope="col" style="width: 5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $oldLines = old('lines', [["product_id" => null, "description" => '', "qty_ordered" => 1, "unit" => 'adet', "unit_price" => 0]]);
                            @endphp
                            @foreach ($oldLines as $index => $line)
                                <tr>
                                    <td>
                                        <input type="number" name="lines[{{ $index }}][product_id]" class="form-control" value="{{ $line['product_id'] ?? '' }}" placeholder="Opsiyonel">
                                    </td>
                                    <td>
                                        <input type="text" name="lines[{{ $index }}][description]" class="form-control" value="{{ $line['description'] ?? '' }}" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.001" min="0.001" name="lines[{{ $index }}][qty_ordered]" class="form-control" value="{{ $line['qty_ordered'] ?? '' }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="lines[{{ $index }}][unit]" class="form-control" value="{{ $line['unit'] ?? 'adet' }}" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="lines[{{ $index }}][unit_price]" class="form-control" value="{{ $line['unit_price'] ?? '' }}" required>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-link text-danger remove-line" aria-label="Satırı Sil">&times;</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.procurement.pos.index') }}" class="btn btn-outline-secondary me-2">Vazgeç</a>
            <button type="submit" class="btn btn-primary">Siparişi Kaydet</button>
        </div>
    </form>

    <template id="line-template">
        <tr>
            <td>
                <input type="number" name="__NAME__[product_id]" class="form-control" placeholder="Opsiyonel">
            </td>
            <td>
                <input type="text" name="__NAME__[description]" class="form-control" required>
            </td>
            <td>
                <input type="number" step="0.001" min="0.001" name="__NAME__[qty_ordered]" class="form-control" required>
            </td>
            <td>
                <input type="text" name="__NAME__[unit]" class="form-control" value="adet" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="__NAME__[unit_price]" class="form-control" required>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-link text-danger remove-line" aria-label="Satırı Sil">&times;</button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        const tableBody = document.querySelector('#line-table tbody');
        const template = document.querySelector('#line-template').innerHTML;
        let lineIndex = tableBody.children.length;

        document.querySelector('#add-line').addEventListener('click', () => {
            const html = template.replaceAll('__NAME__', `lines[${lineIndex}]`);
            tableBody.insertAdjacentHTML('beforeend', html);
            lineIndex += 1;
        });

        tableBody.addEventListener('click', (event) => {
            if (event.target.classList.contains('remove-line')) {
                const row = event.target.closest('tr');
                if (tableBody.children.length > 1) {
                    row.remove();
                }
            }
        });
    </script>
@endpush
