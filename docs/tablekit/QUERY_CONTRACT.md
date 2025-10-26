# Query Contract

TableKit normalises query parameters so client and server implementations behave the same way. Controllers can continue to rely on existing filters without modification.

| Parameter | Description | Example |
| --- | --- | --- |
| `q` | Global search term. | `?q=warehouse` |
| `sort` | Comma separated sort keys. Prefix with `-` for descending. | `?sort=doc_no,-created_at` |
| `page` | Current page number (only set in client mode when > 1). | `?page=3` |
| `perPage` | Page size when server pagination is used. | `?perPage=50` |
| `filters[key]` | Text/number filters. | `?filters[customer]=Acme` |
| `filters[key][]` | Multi-select filters (badge/enum). | `?filters[status][]=draft&filters[status][]=posted` |
| `filters[key][from]`<br>`filters[key][to]` | Date range filters. | `?filters[issued_at][from]=2024-01-01` |

## Server Mode

When TableKit enters server mode (`data-count > threshold` or JS disabled) the toolbar submits the form using `GET`. All query parameters listed above are preserved, so existing controllers and paginator logic continue working.

## Client Mode

In client mode the dataset is rendered once and TableKit updates the URL using `history.replaceState`. Navigating back/forward will restore the previous filter and sort state. Server pagination links inside the component are hidden and replaced with compact client-side controls.
