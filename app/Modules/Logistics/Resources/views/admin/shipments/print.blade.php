<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Sevkiyat {{ $shipment->doc_no }}</title>
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
    <style>
        body { font-family: 'Inter', sans-serif; color: #111; margin: 2rem; }
        h1 { font-size: 20px; margin-bottom: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 6px; font-size: 13px; }
        th { background: #f8f9fa; text-align: left; }
    </style>
</head>
<body>
    <h1>Sevkiyat Fişi - {{ $shipment->doc_no }}</h1>
    <p><strong>Müşteri:</strong> {{ $shipment->customer?->name ?? '—' }}<br>
       <strong>Tarih:</strong> {{ optional($shipment->shipped_at)->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}<br>
       <strong>Depo:</strong> {{ $shipment->warehouse?->name ?? '—' }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Ürün</th>
                <th>Miktar</th>
                <th>Birim</th>
                <th>Paketlenen</th>
                <th>Not</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shipment->lines as $line)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                    <td>{{ number_format($line->qty, 3) }}</td>
                    <td>{{ $line->uom }}</td>
                    <td>{{ number_format($line->packed_qty, 3) }}</td>
                    <td>{{ $line->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
