# Column Configuration

Column definitions drive both the rendered table and the client-side behaviour. Each column is described using an associative array. Below is the full set of supported options.

| Key | Type | Description |
| --- | --- | --- |
| `key` | string | Unique identifier for the column, also used as the data key in each row. |
| `label` | string | Human readable label. Displayed in table headers and used for responsive labels. |
| `type` | string | One of `text`, `number`, `money`, `badge`, `date`, `enum`, `actions`. Determines default formatting and filter UI. |
| `sortable` | bool | Enables sorting. In client mode sorting happens locally, in server mode query parameters are updated. |
| `filterable` | bool | Renders a filter control in the toolbar. |
| `hidden_xs` | bool | Hides the column in the mobile card layout. |
| `enum` | array | Available values for `badge` or `enum` columns (`value => label`). Used for dropdown filters and badge labelling. |
| `formatter` | callable|string | Optional formatter callback. Receives the row, column and raw value. Should return a string or an array containing `html`, `display` and `raw`. |
| `options` | array | Additional settings (e.g. `['precision' => 2]` for numbers or money). |

## Row Structure

Rows are passed to the component as arrays with the following structure:

```php
[
    'id' => 'optional-row-id',
    'cells' => [
        'doc_no' => 'SO-1001',
        'status' => 'draft',
        'grand_total' => ['amount' => 1250.50, 'currency' => 'TRY'],
        'actions' => [
            ['label' => 'Görüntüle', 'href' => route('...')],
        ],
    ],
]
```

If `cells` is omitted the array itself is used. Each cell can be:

- A scalar value (`string|int|float`).
- An array with `raw`, `display` and/or `html` keys to override formatting.
- A money definition (`['amount' => 10.5, 'currency' => 'TRY']`).
- An actions array (automatically rendered by `<x-table:row-actions>`).

## Formatters

Formatter callbacks can be declared as global functions, invokable classes or `Class@method` strings. The callback receives four arguments (`$row`, `$column`, `$raw`, `$cell`) and should return either a string or an array with the keys described above. Formatters are executed on the server so the returned markup is included in the JSON dataset for client mode.
