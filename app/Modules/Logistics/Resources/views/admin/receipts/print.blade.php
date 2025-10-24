<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>GRN {{ $receipt->doc_no }}</title>
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
    <h1>Mal Kabul Fişi - {{ $receipt->doc_no }}</h1>
    <p><strong>Tedarikçi:</strong> {{ $receipt->vendor_id ? ('#' . $receipt->vendor_id) : '—' }}<br>
       <strong>Tarih:</strong> {{ optional($receipt->received_at)->format('d.m.Y H:i') ?? now()->format('d.m.Y H:i') }}<br>
       <strong>Depo:</strong> {{ $receipt->warehouse?->name ?? '—' }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Ürün</th>
                <th>Beklenen</th>
                <th>Alınan</th>
                <th>Varyans</th>
                <th>Gerekçe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receipt->lines as $line)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $line->product?->name ?? ('#' . $line->product_id) }}</td>
                    <td>{{ number_format($line->qty_expected ?? 0, 3) }}</td>
                    <td>{{ number_format($line->qty_received ?? 0, 3) }}</td>
                    <td>{{ number_format(($line->qty_received ?? 0) - ($line->qty_expected ?? 0), 3) }}</td>
                    <td>{{ $line->variance_reason ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
