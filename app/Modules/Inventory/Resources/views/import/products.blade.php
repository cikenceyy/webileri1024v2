@extends('layouts.admin')

@section('title', 'Ürün İçe Aktar')

@section('content')
<x-ui-page-header title="Ürün İçe Aktar" description="CSV şablonu ile toplu ekleme" />

@if(session('status'))
    <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
@endif

@if(session('import_errors'))
    <x-ui-alert type="warning" dismissible>
        Bazı satırlar içe aktarılırken hata oluştu:
        <ul class="mb-0 small mt-2">
            @foreach(session('import_errors') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui-alert>
@endif

<x-ui-card>
    <h2 class="h6">CSV Şablonu</h2>
    <p class="text-muted">Aşağıdaki başlıkları içeren UTF-8 CSV dosyasını yükleyin:</p>
    <ul class="small text-muted">
        <li><code>sku</code>, <code>name</code> (zorunlu)</li>
        <li><code>category_code</code>, <code>price</code>, <code>unit</code>, <code>barcode</code>, <code>reorder_point</code>, <code>status</code> (opsiyonel)</li>
    </ul>
    <div class="mt-3">
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.inventory.import.products.sample') }}">Örnek CSV</a>
    </div>
</x-ui-card>

<x-ui-card class="mt-4">
    <form method="POST" action="{{ route('admin.inventory.import.products.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <x-ui-file name="file" label="CSV Dosyası" accept=".csv,text/csv" required />
            <div class="form-text">En fazla 5 MB.</div>
        </div>
        <div class="d-flex justify-content-end">
            <x-ui-button type="submit" variant="primary">İçe Aktar</x-ui-button>
        </div>
    </form>
</x-ui-card>
@endsection
