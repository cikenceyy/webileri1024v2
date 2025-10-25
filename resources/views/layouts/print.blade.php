<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name') . ' · Print')</title>
    <style>
        :root {
            --print-font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --print-color: #1f2937;
            --print-muted: #6b7280;
            --print-border: #d1d5db;
            --print-accent: #2563eb;
            --print-padding: 2.5rem;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: var(--print-font);
            color: var(--print-color);
            background-color: #ffffff;
        }

        body {
            line-height: 1.5;
        }

        main#print {
            padding: var(--print-padding);
            max-width: 960px;
            margin: 0 auto;
        }

        header.print-header, footer.print-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--print-border);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        footer.print-footer {
            border-top: 1px solid var(--print-border);
            border-bottom: none;
            padding-top: 1rem;
            margin-top: 2rem;
            font-size: 0.875rem;
            color: var(--print-muted);
        }

        h1, h2, h3, h4, h5, h6 {
            margin-top: 0;
            font-weight: 600;
        }

        table.print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        table.print-table th,
        table.print-table td {
            padding: 0.75rem 0.5rem;
            border: 1px solid var(--print-border);
            text-align: left;
        }

        table.print-table thead th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        .text-muted {
            color: var(--print-muted);
        }

        .text-end {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 999px;
            background-color: #e5e7eb;
            font-size: 0.75rem;
        }

        .badge-primary {
            background-color: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        @media print {
            body {
                margin: 0;
            }

            main#print {
                padding: 2rem 1.5rem;
            }

            a[href]::after {
                content: '';
            }
        }
    </style>
    @stack('print-styles')
</head>
<body>
<main id="print">
    @yield('content')
</main>
@stack('print-scripts')
</body>
</html>
