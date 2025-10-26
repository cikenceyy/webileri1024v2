# Column Configuration

Column definitions drive both the rendered table and the client-side behaviour. Each column is described using an associative array. Below is the full set of supported options.

| Key | Type | Description |
| --- | --- | --- |
| `key` | string | Unique identifier for the column, also used as the data key in each row. |
| `label` | string | Human readable label. Displayed in table headers and used for responsive labels. |
| `type` | string | One of `text`, `number`, `money`, `badge`, `chip`, `signal`, `date`, `enum`, `select`, `actions`. Determines default formatting and filter UI. |
| `sortable` | bool | Enables sorting. In client mode sorting happens locally, in server mode query parameters are updated. |
| `filterable` | bool | Renders a filter control in the toolbar. |
| `hidden_xs` | bool | Hides the column in the mobile card layout. |
| `enum` | array | Available values for `badge` or `enum` columns (`value => label`). Used for dropdown filters and badge labelling. |
| `formatter` | callable|string | Optional formatter callback. Receives the row, column and raw value. Should return a string or an array containing `html`, `display` and `raw`. |
| `options` | array | Additional settings (e.g. `['precision' => 2]` for numbers or money, `['tone' => 'warning']` for chips). |

## Row Structure

Rows are passed to the component as arrays with the following structure:

```php
[
    'id' => 'optional-row-id',
    'cells' => [
        'select' => ['value' => 'SO-1001', 'checked' => true],
        'doc_no' => 'SO-1001',
        'status' => 'draft',
        'signal' => ['level' => 'warning', 'label' => 'Stok Az'],
        'grand_total' => ['amount' => 1250.50, 'currency' => 'TRY'],
        'actions' => [
            ['label' => 'Görüntüle', 'href' => route('...')],
        ],
    ],
    'meta' => view('components.tablekit.row-meta', ['chips' => [$warehouseChip]])->render(),
]
```

If `cells` is omitted the array itself is used. Each cell can be:

- A scalar value (`string|int|float`).
- An array with `raw`, `display` and/or `html` keys to override formatting.
- A money definition (`['amount' => 10.5, 'currency' => 'TRY']`).
- A chip collection (`[['label' => 'Depo 1', 'tone' => 'primary']]`).
- A signal definition (`['level' => 'danger', 'label' => 'Stok Yok']`).
- An actions array (automatically rendered by `<x-table:row-actions>`).
- A selection meta array (`['value' => 'ROW-ID', 'disabled' => false, 'checked' => true]`) used when `type` is `select`.

Rows can optionally include a `meta` key containing a pre-rendered HTML snippet (chips, statuses, secondary descriptions). When present it is displayed beneath the first column and is available to the client dataset for filtering.

## Table Options

When calling `TableConfig::make($columns, $options)` the following options are recognised:

| Option | Type | Description |
| --- | --- | --- |
| `client_threshold` | int | Overrides the automatic client/server switch threshold (default 500). |
| `default_sort` | string | Default sort expression (`doc_no` or `-created_at`). |
| `data_count` | int | Explicit dataset count for server mode (used with paginators). |
| `virtual` | bool | Enables virtual scrolling in client mode. Recommended for 1k+ rows or console streams. |
| `row_height` | int | Row height in pixels used by the virtualiser (defaults to 48). |

## Formatters

Formatter callbacks can be declared as global functions, invokable classes or `Class@method` strings. The callback receives four arguments (`$row`, `$column`, `$raw`, `$cell`) and should return either a string or an array with the keys described above. Formatters are executed on the server so the returned markup is included in the JSON dataset for client mode.
