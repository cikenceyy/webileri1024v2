# Blade UI Components Catalog

All components live under `resources/views/components/` using the flat `<x-ui-*>` syntax exclusively.

## `<x-ui-button>`

| Prop | Type | Default | Description |
| --- | --- | --- | --- |
| `type` | string | `button` | HTML button type when rendering `<button>`. Ignored if `href` is provided. |
| `variant` | string | `primary` | One of `primary`, `secondary`, `danger`, `link`. Maps to Bootstrap button classes. |
| `size` | string | `md` | Button sizing (`sm`, `md`, `lg`). |
| `icon` | string/null | `null` | Optional Bootstrap Icons class (e.g. `bi bi-plus`). |
| `href` | string/null | `null` | When set, renders as `<a>` with `role="button"`. |

Slots: default slot renders button label.

Example:

```blade
<x-ui-button variant="secondary" icon="bi bi-plus">Yeni Kayıt</x-ui-button>
```

## `<x-ui-input>`

| Prop | Type | Default | Description |
| --- | --- | --- | --- |
| `name` | string | — | Input name (required). |
| `type` | string | `text` | HTML input type. |
| `label` | string/null | `null` | Optional `<label>` text. |
| `value` | mixed | `null` | Default value, merged with `old()`. |
| `placeholder` | string/null | `null` | Placeholder text. |
| `help` | string/null | `null` | Helper text shown when no validation error is present. |

Automatically renders validation feedback using `$errors`.

## `<x-ui-select>`

| Prop | Type | Default | Description |
| --- | --- | --- | --- |
| `name` | string | — | Select name (required). |
| `label` | string/null | `null` | Optional label. |
| `options` | array | `[]` | Key/value pairs for `<option>` items. |
| `placeholder` | string/null | `null` | Adds a blank option as placeholder. |
| `value` | mixed | `old($name)` | Derived from passed attributes or old input. |

## `<x-ui-table>`

| Prop | Type | Default | Description |
| --- | --- | --- | --- |
| `responsive` | bool | `true` | Wraps table in `.table-responsive` when true. |

Slots:

* `thead` — Optional header rows.
* `tbody` — Optional body rows.
* Default slot — Additional markup (e.g., `<caption>`).

## `<x-ui-modal>`

| Prop | Type | Default | Description |
| --- | --- | --- | --- |
| `id` | string | — | Required modal id. |
| `title` | string/null | `null` | Modal heading. |
| `size` | string | `md` | One of `sm`, `md`, `lg`, `xl` mapped to Bootstrap dialog classes. |

Slots:

* Default slot renders modal body.
* `footer` slot for action buttons.

## `<x-ui-toast>`

| Prop | Type | Default | Description |
| --- | --- | --- | --- |
| `title` | string/null | `null` | Optional heading. |
| `message` | string/null | `null` | Toast message (defaults to default slot). |
| `variant` | string | `info` | One of `info`, `success`, `warning`, `danger`; adjusts CSS class `.ui-toast--{variant}`. |
| `timeout` | int | `4000` | Stored in `data-timeout` for JS auto-dismiss. |

Renders markup compatible with existing toast runtime (`data-ui="toast"`).

## `<x-ui-toast-stack>`

Container component providing `data-ui="toast-container"` for runtime-generated notifications. Accepts additional classes via standard attributes.

Example placement in layout:

```blade
<x-ui-toast-stack class="ui-toast-region__inner" />
```
