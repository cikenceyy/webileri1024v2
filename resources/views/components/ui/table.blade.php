@props([
    'tableId' => null,
    'columns' => [],
    'rows' => [],
    'totals' => [],
    'rowActions' => null,
    'dense' => null,
    'stickyHeader' => true,
    'stickyTotals' => true,
    'searchable' => true,
    'searchPlaceholder' => 'Ara...',
    'searchLabel' => 'Tabloda ara',
    'searchAction' => null,
])

@php
    use Illuminate\Support\Str;

    $resolvedColumns = collect($columns)->map(function ($column, $index) {
        $label = $column['label'] ?? ('Column ' . ($index + 1));
        $id = $column['id'] ?? Str::slug($label, '_');

        return array_merge([
            'id' => $id,
            'label' => $label,
            'type' => $column['type'] ?? 'text',
            'numeric' => $column['numeric'] ?? in_array($column['type'] ?? null, ['numeric', 'number'], true),
            'pin' => $column['pin'] ?? null,
            'totalizable' => array_key_exists('totalizable', $column) ? (bool) $column['totalizable'] : false,
            'unit' => $column['unit'] ?? null,
            'meta' => $column['meta'] ?? null,
            'clamp' => $column['clamp'] ?? null,
        ], $column);
    })->values();

    $columnCount = $resolvedColumns->count();
    $hasRowActionsSlot = isset($rowActionsSlot) && trim($rowActionsSlot) !== '';
    $hasRowActions = $rowActions || $hasRowActionsSlot;
    $columnCount += $hasRowActions ? 1 : 0;

    $rowCollection = collect($rows);
    $rowCount = $rowCollection->count();
    $hasCustomRows = isset($slot) && trim($slot) !== '';

    $routeKey = Str::slug(request()->path(), '_');
    $routeKey = $routeKey !== '' ? $routeKey : 'root';
    $tableKey = $tableId ?? ($attributes->get('id') ?: 'ui_table_' . substr(md5($resolvedColumns->pluck('id')->join('_')), 0, 8));
    $tableKey = Str::slug($tableKey, '_');

    $densityState = match (true) {
        $dense === false, $dense === 'comfortable' => 'comfortable',
        $dense === true, $dense === 'compact' => 'compact',
        default => 'compact',
    };

    $searchQuery = request()->query('q', '');
    $remainingQuery = request()->except('q');
    $clearUrl = ($searchAction ?? url()->current()) . (empty($remainingQuery) ? '' : ('?' . http_build_query($remainingQuery)));
@endphp

<div
    {{
        $attributes
            ->class([
                'ui-table',
                $densityState === 'compact' ? 'ui-table--dense' : null,
                $stickyHeader ? 'ui-table--sticky-head' : null,
                $stickyTotals ? 'ui-table--sticky-totals' : null,
            ])
            ->merge([
                'data-ui' => 'table',
                'data-density' => $densityState,
                'data-sticky-head' => $stickyHeader ? 'true' : 'false',
                'data-sticky-totals' => $stickyTotals ? 'true' : 'false',
                'data-route-key' => $routeKey,
                'data-table-id' => $tableKey,
            ])
    }}
>
    <div class="ui-table__controls" role="group" aria-label="Table view controls">
        @if($searchable)
            <form
                method="GET"
                action="{{ $searchAction ?? url()->current() }}"
                class="ui-table__search"
                data-ui="table-search"
            >
                <label class="ui-table__search-label" for="{{ $tableKey }}-search">{{ $searchLabel }}</label>
                <div class="ui-table__search-controls">
                    <input
                        id="{{ $tableKey }}-search"
                        type="search"
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="{{ $searchPlaceholder }}"
                        class="ui-table__search-input"
                    />
                    <button type="submit" class="ui-table__search-submit">Ara</button>
                    <a class="ui-table__search-reset" href="{{ $clearUrl }}">Temizle</a>
                </div>
                @foreach(request()->except('q') as $param => $value)
                    @if(is_array($value))
                        @foreach($value as $item)
                            <input type="hidden" name="{{ $param }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                    @endif
                @endforeach
            </form>
        @endif

        <div class="ui-table__control-group">
            <button
                type="button"
                class="ui-table__control"
                data-action="table-density"
                aria-pressed="{{ $densityState === 'compact' ? 'true' : 'false' }}"
                aria-label="Tablo yoğunluğunu değiştir"
            >
                <span class="ui-table__control-label">Yoğunluk</span>
                <span class="ui-table__control-value" data-ui="density-state">{{ $densityState === 'compact' ? 'Compact' : 'Comfortable' }}</span>
            </button>

            @isset($headerExtra)
                <div class="ui-table__control ui-table__control--extra">
                    {{ $headerExtra }}
                </div>
            @endisset
        </div>
    </div>

    <div class="ui-table__viewport" data-ui="scroll-container" tabindex="0">
        <table
            class="ui-table__element"
            role="grid"
            aria-rowcount="{{ $rowCount ?: 'auto' }}"
            aria-colcount="{{ $columnCount }}"
            data-ui="table-grid"
        >
            <caption class="visually-hidden">Veri tablosu</caption>
            <thead class="ui-table__head" role="rowgroup">
                <tr class="ui-table__row ui-table__row--head" role="row">
                    @foreach($resolvedColumns as $column)
                        @php
                            $headClasses = ['ui-table__cell', 'ui-table__cell--head'];
                            if ($column['numeric']) {
                                $headClasses[] = 'ui-table__cell--numeric';
                            }
                            if (($column['pin'] ?? null) === 'left') {
                                $headClasses[] = 'ui-table__cell--pinned-left';
                            }
                            if (($column['pin'] ?? null) === 'right') {
                                $headClasses[] = 'ui-table__cell--pinned-right';
                            }
                        @endphp
                        <th
                            scope="col"
                            role="columnheader"
                            aria-colindex="{{ $loop->iteration }}"
                            class="{{ implode(' ', $headClasses) }}"
                            data-column-id="{{ $column['id'] }}"
                            data-pin="{{ $column['pin'] ?? '' }}"
                        >
                            <div class="ui-table__head-inner">
                                <span class="ui-table__label">{{ $column['label'] }}</span>
                                @if($column['unit'] || $column['meta'])
                                    <span class="ui-table__meta">{{ $column['meta'] ?? $column['unit'] }}</span>
                                @endif
                            </div>
                        </th>
                    @endforeach

                    @if($hasRowActions)
                        <th
                            scope="col"
                            role="columnheader"
                            aria-colindex="{{ $resolvedColumns->count() + 1 }}"
                            class="ui-table__cell ui-table__cell--head ui-table__cell--actions ui-table__cell--pinned-right"
                            data-column-id="actions"
                            data-pin="right"
                        >
                            <span class="ui-table__label">Aksiyon</span>
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="ui-table__body" role="rowgroup" data-ui="table-body">
                @if($hasCustomRows)
                    {{ $slot }}
                @else
                    @foreach($rowCollection as $rowIndex => $row)
                        <tr class="ui-table__row" role="row" data-row-index="{{ $rowIndex + 1 }}">
                            @foreach($resolvedColumns as $column)
                                @php
                                    $value = data_get($row, $column['id']);
                                    $cellClasses = ['ui-table__cell'];
                                    if ($column['numeric']) {
                                        $cellClasses[] = 'ui-table__cell--numeric';
                                    }
                                    if (($column['pin'] ?? null) === 'left') {
                                        $cellClasses[] = 'ui-table__cell--pinned-left';
                                    }
                                    if (($column['pin'] ?? null) === 'right') {
                                        $cellClasses[] = 'ui-table__cell--pinned-right';
                                    }
                                @endphp
                                <td
                                    class="{{ implode(' ', $cellClasses) }}"
                                    role="gridcell"
                                    aria-colindex="{{ $loop->iteration }}"
                                    data-column-id="{{ $column['id'] }}"
                                >
                                    @if(is_array($value) && ($value['type'] ?? null) === 'badge')
                                        <x-ui.badge :variant="$value['variant'] ?? 'info'">{{ $value['label'] ?? '' }}</x-ui.badge>
                                    @elseif(is_array($value) && ($value['type'] ?? null) === 'clamp')
                                        <span class="u-clamp-{{ $value['lines'] ?? 2 }}">{{ $value['value'] ?? '' }}</span>
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach

                            @if($hasRowActions)
                                <td
                                    class="ui-table__cell ui-table__cell--actions ui-table__cell--pinned-right"
                                    role="gridcell"
                                    aria-colindex="{{ $resolvedColumns->count() + 1 }}"
                                    data-column-id="actions"
                                >
                                    @if(isset($rowActionsSlot))
                                        {{ $rowActionsSlot }}
                                    @elseif(isset($row['actions']))
                                        <x-ui.row-actions :actions="$row['actions']" />
                                    @elseif($rowActions)
                                        <x-ui.row-actions :actions="$rowActions" />
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot class="ui-table__totals" role="rowgroup" data-ui="table-totals">
                @if(isset($footerTotals))
                    {{ $footerTotals }}
                @elseif(!empty($totals))
                    <tr class="ui-table__row ui-table__row--totals" role="row">
                        @foreach($resolvedColumns as $column)
                            @php
                                $value = $totals[$column['id']] ?? null;
                                $cellClasses = ['ui-table__cell', 'ui-table__cell--total'];
                                if ($column['numeric']) {
                                    $cellClasses[] = 'ui-table__cell--numeric';
                                }
                                if (($column['pin'] ?? null) === 'left') {
                                    $cellClasses[] = 'ui-table__cell--pinned-left';
                                }
                                if (($column['pin'] ?? null) === 'right') {
                                    $cellClasses[] = 'ui-table__cell--pinned-right';
                                }
                            @endphp
                            <td
                                class="{{ implode(' ', $cellClasses) }}"
                                role="gridcell"
                                aria-colindex="{{ $loop->iteration }}"
                                data-column-id="{{ $column['id'] }}"
                            >
                                {{ $value }}
                            </td>
                        @endforeach
                        @if($hasRowActions)
                            <td class="ui-table__cell ui-table__cell--total ui-table__cell--actions ui-table__cell--pinned-right" role="gridcell" aria-colindex="{{ $resolvedColumns->count() + 1 }}" data-column-id="actions"></td>
                        @endif
                    </tr>
                @endif
            </tfoot>
        </table>
    </div>
    <p class="ui-table__assistive" id="{{ $tableKey }}-assistive">Ok tuşlarıyla hücreler arasında gezinin, Enter ile düzenleyin, Esc ile iptal edin.</p>
</div>
