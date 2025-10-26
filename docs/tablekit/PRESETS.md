# TableKit Presets

Preset files live under `app/Core/Support/TableKit/Presets` and provide ready-to-use column definitions for the most common admin lists. Each preset returns an array containing a `columns` definition and optional config `options` used when constructing the `TableConfig` instance.

| Preset | Target views | Notes |
| --- | --- | --- |
| `invoices` | `finance::admin.invoices.index`, console invoice streams | Includes `grand_total_formatted` and `due_at_human` fallbacks for client mode. |
| `shipments` | `logistics::shipments.index`, console shipment streams | Exposes progress columns via `progress_percent` preformatted text. |
| `grns` | `procurement::grns.index`, inbound receipt consoles | Keeps status badges aligned with warehouse receiving flows. |
| `orders` | `marketing::orders.index`, O2C console | Uses `grand_total_formatted` and `due_date_human`. |
| `workorders` | `production::workorders.index`, manufacturing console | Provides plan start/end fields for both formats. |
| `transfers` | `inventory::transfers.index`, internal transfers | Contains selection-ready actions column. |
| `products` | `inventory::products.index`, merchandising | Uses `category_chain` and `updated_at_human` helper fields. |

To load a preset programmatically:

```php
use App\Core\Support\TableKit\PresetRepository;
use App\Core\Support\TableKit\TableConfig;

$preset = app(PresetRepository::class)->load('shipments');

if ($preset) {
    $config = TableConfig::make($preset['columns'], $preset['options']);
}
```

Preset files are standard PHP arrays, so teams can duplicate and adapt them per module if special requirements arise. Keep `options['preformatted']` aligned with the dataset keys when controllers return both raw and formatted fields.
