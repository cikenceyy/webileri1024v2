@php
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    $tableId = $attributes->get('id') ?? 'table-' . Str::random(8);
    $mode = ($mode ?? 'client') === 'server' ? 'server' : 'client';
    $columns = array_map(fn ($column) => array_merge(['sortable' => false, 'align' => null, 'type' => 'text'], $column), $columns ?? []);
    $pageSizeOptions = array_values($pageSizeOptions ?? [25, 50, 100]);
    $defaultPageSize = (int) ($defaultPageSize ?? ($pageSizeOptions[0] ?? 25));
    if (! in_array($defaultPageSize, $pageSizeOptions, true)) {
        $defaultPageSize = $pageSizeOptions[0] ?? 25;
    }
    $dataset = array_values($dataset ?? []);
    $selectable = (bool) ($selectable ?? false);
    $searchName = $searchName ?? 'q';
    $searchValue = $searchValue ?? '';
    $searchPlaceholder = $searchPlaceholder ?? __('Ara');
    $pageSizeParam = $pageSizeParam ?? 'per_page';
    $filters = $filters ?? [];
    $initialRows = $mode === 'client' ? array_slice($dataset, 0, $defaultPageSize) : [];

    $renderCell = function (array $row, array $column) {
        $key = $column['key'];
        $value = Arr::get($row, $key, '');
        $classes = ['py-3'];
        if (! empty($column['align'])) {
            $classes[] = 'text-' . $column['align'];
        }
        if (! ($column['wrap'] ?? true)) {
            $classes[] = 'text-nowrap';
        }

        $content = e($value);
        $type = $column['type'] ?? 'text';

        if ($type === 'link') {
            $urlKey = $column['urlKey'] ?? ($key . '_url');
            $url = Arr::get($row, $urlKey);
            if ($url) {
                $content = '<a href="' . e($url) . '" class="fw-semibold">' . $content . '</a>';
            }
        } elseif ($type === 'badge') {
            $badgeKey = $column['badgeKey'] ?? ($key . '_badge');
            $variant = Arr::get($row, $badgeKey, 'bg-secondary');
            $content = '<span class="badge ' . e($variant) . '">' . $content . '</span>';
        } elseif ($type === 'actions') {
            $content = '';
            foreach (Arr::get($row, $key, []) as $action) {
                $label = e($action['label'] ?? __('Aç'));
                $url = e($action['url'] ?? '#');
                $variant = e($action['variant'] ?? 'outline-primary');
                $size = e($action['size'] ?? 'sm');
                $icon = $action['icon'] ?? null;
                $content .= '<a href="' . $url . '" class="btn btn-' . $variant . ' btn-' . $size . ' me-2">';
                if ($icon) {
                    $content .= '<i class="' . e($icon) . ' me-1" aria-hidden="true"></i>';
                }
                $content .= $label . '</a>';
            }
            $content = rtrim($content) ?: '<span class="text-muted">' . __('Aksiyon yok') . '</span>';
        }

        return '<td class="' . implode(' ', array_filter($classes)) . '">' . $content . '</td>';
    };
@endphp

<div {{ $attributes->merge(['class' => 'ui-table card shadow-sm']) }}
    data-ui="table"
    data-table-id="{{ $tableId }}"
    data-table-mode="{{ $mode }}"
    data-page-size-options='@json($pageSizeOptions)'
    data-default-page-size="{{ $defaultPageSize }}"
    data-search-name="{{ $searchName }}"
    data-page-size-param="{{ $pageSizeParam }}"
    @if($selectable) data-table-selectable="true" @endif
    @if($mode === 'client') data-table-dataset='@json($dataset)' @endif
>
    <div class="card-header">
        <form method="get" class="row g-2 align-items-end" data-ui="table-search" @if($mode === 'client') data-table-behavior="client" @endif>
            <div class="col-md-4">
                <label class="form-label" for="{{ $tableId }}-search">{{ __('Ara') }}</label>
                <input type="search"
                    class="form-control"
                    id="{{ $tableId }}-search"
                    name="{{ $searchName }}"
                    value="{{ $searchValue }}"
                    placeholder="{{ $searchPlaceholder }}"
                    data-table-search-input
                    data-debounce="250"
                >
            </div>
            @foreach($filters as $filter)
                @php
                    $filterId = $tableId . '-filter-' . $filter['key'];
                    $filterName = $filter['name'] ?? $filter['key'];
                    $filterValue = $filter['value'] ?? '';
                    $filterField = $filter['field'] ?? $filter['key'];
                    $filterType = $filter['type'] ?? 'string';
                @endphp
                <div class="col-md-3">
                    <label class="form-label" for="{{ $filterId }}">{{ $filter['label'] }}</label>
                    <select class="form-select"
                        id="{{ $filterId }}"
                        name="{{ $filterName }}"
                        data-table-filter
                        data-filter-key="{{ $filter['key'] }}"
                        data-filter-field="{{ $filterField }}"
                        data-filter-type="{{ $filterType }}"
                    >
                        @foreach($filter['options'] ?? [] as $option)
                            <option value="{{ $option['value'] }}" @selected((string) $filterValue === (string) $option['value'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="col-md-2">
                <label class="form-label" for="{{ $tableId }}-page-size">{{ __('Sayfa Boyutu') }}</label>
                <select class="form-select" id="{{ $tableId }}-page-size" name="{{ $pageSizeParam }}" data-table-page-size>
                    @foreach($pageSizeOptions as $option)
                        <option value="{{ $option }}" @selected((int) ($pageSize ?? $defaultPageSize) === (int) $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>
            @if($mode === 'server')
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-secondary">{{ __('Uygula') }}</button>
                </div>
            @endif
        </form>
    </div>
    <div class="table-responsive" data-ui="scroll-container">
        <table class="table align-middle mb-0" data-table-grid>
            <thead class="table-light">
                <tr>
                    @if($selectable)
                        <th scope="col" class="w-auto">
                            <input type="checkbox" class="form-check-input" data-table-select-all aria-label="{{ __('Tüm satırları seç') }}">
                        </th>
                    @endif
                    @foreach($columns as $column)
                        @php
                            $alignClass = $column['align'] ? 'text-' . $column['align'] : '';
                        @endphp
                        <th scope="col"
                            @if($alignClass) class="{{ $alignClass }}" @endif
                            @if(($column['sortable'] ?? false) && $mode === 'client')
                                aria-sort="none"
                                data-column-key="{{ $column['key'] }}"
                            @endif
                        >
                            @if(($column['sortable'] ?? false) && $mode === 'client')
                                <button type="button" class="btn btn-link p-0" data-table-sort data-sort-key="{{ $column['key'] }}">
                                    {{ $column['label'] }}
                                    <i class="bi bi-arrow-down-up ms-1" aria-hidden="true"></i>
                                </button>
                            @else
                                {{ $column['label'] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody data-table-body>
                @if($mode === 'client')
                    @foreach($initialRows as $row)
                        <tr data-row-id="{{ $row['id'] }}">
                            @if($selectable)
                                <td>
                                    <input type="checkbox" class="form-check-input" data-table-select-row value="{{ $row['id'] }}" aria-label="{{ __('Satırı seç') }}">
                                </td>
                            @endif
                            @foreach($columns as $column)
                                {!! $renderCell($row, $column) !!}
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div data-table-summary role="status" aria-live="polite"></div>
        @if($mode === 'client')
            <nav aria-label="{{ __('Sayfalama') }}">
                <ul class="pagination mb-0" data-table-pagination></ul>
            </nav>
        @else
            {!! $paginator ?? '' !!}
        @endif
    </div>
    @if($selectable)
        <div class="alert alert-secondary d-flex align-items-center justify-content-between gap-3 mb-0" data-table-selection hidden>
            <div>
                <strong data-table-selection-count>0</strong> {{ __('kayıt seçildi') }}
            </div>
            <div class="d-flex gap-2">
                {{ $bulkActions ?? '' }}
                <button type="button" class="btn btn-outline-secondary btn-sm" data-table-clear-selection>{{ __('Temizle') }}</button>
            </div>
        </div>
    @endif
</div>
@if($mode === 'client')
    <script type="application/json" data-table-config="{{ $tableId }}">@json(['columns' => $columns, 'selectable' => $selectable])</script>
@endif
