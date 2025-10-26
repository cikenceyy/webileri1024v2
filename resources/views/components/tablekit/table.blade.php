@props([
    'config',
    'rows' => [],
    'paginator' => null,
    'id' => null,
    'emptyText' => __('Kayıt bulunamadı.'),
    'dense' => false,
    'filters' => null,
])

@php
    use App\Core\Support\TableKit\TableConfig;
    use Illuminate\Pagination\LengthAwarePaginator;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    use function collect;

    /** @var TableConfig $config */
    $config = $config instanceof TableConfig ? $config : TableConfig::make([], []);
    $rowsCollection = $rows instanceof Collection ? $rows : collect($rows);

    $totalCount = $config->dataCount() ?? ($paginator instanceof LengthAwarePaginator ? $paginator->total() : $rowsCollection->count());
    $mode = $config->determineMode($totalCount);
    $dataset = $config->prepareDataset($rowsCollection);

    $componentId = $id ?? 'tablekit-'.Str::uuid()->toString();
    $columnCount = count($config->columns());
    $toolbarContent = isset($toolbar) ? trim($toolbar) : '';
    $stepperSummaryContent = isset($stepperSummary) ? trim($stepperSummary) : '';
    $bulkContent = isset($bulk) ? trim($bulk) : '';
    $rowMetaTemplate = isset($rowMeta) ? trim($rowMeta) : '';
    $statusCount = $mode === 'server' ? $totalCount : $rowsCollection->count();
    $isDense = filter_var($dense, FILTER_VALIDATE_BOOLEAN);
    $filterKeys = is_array($filters) ? implode(',', $filters) : (is_string($filters) ? $filters : '');
    $emptyContent = isset($empty) ? trim($empty) : '';
@endphp

<div {{ $attributes->class(['tablekit', 'tablekit--dense' => $isDense])->merge([
    'id' => $componentId,
    'data-tablekit' => 'true',
    'data-tablekit-mode' => $mode,
    'data-tablekit-count' => $totalCount,
    'data-tablekit-client-threshold' => $config->clientThreshold(),
    'data-tablekit-default-sort' => $config->defaultSort() ?? '',
    'data-tablekit-virtual' => $config->virtual() ? 'true' : 'false',
    'data-tablekit-row-height' => $config->virtualRowHeight() ?? '',
    'data-tablekit-selectable' => $config->hasSelectionColumn() ? 'true' : 'false',
    'data-tablekit-dense' => $isDense ? 'true' : 'false',
    'data-tablekit-filters' => $filterKeys,
]) }}>
    <script type="application/json" data-tablekit-dataset>
        {!! json_encode($dataset, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

    @if($rowMetaTemplate !== '')
        <template data-tablekit-row-meta-template>{!! $rowMetaTemplate !!}</template>
    @endif

    @if($stepperSummaryContent !== '')
        <div class="tablekit__stepper-summary" data-tablekit-stepper-summary aria-live="polite">
            {!! $stepperSummaryContent !!}
        </div>
    @endif

    @if($toolbarContent !== '')
        <div class="tablekit__toolbar" data-tablekit-toolbar>
            {!! $toolbarContent !!}
        </div>
    @endif

    <div class="tablekit__table-wrapper" role="region" aria-live="polite">
        <table class="tablekit__table" role="table">
            <thead class="tablekit__head">
            <tr role="row">
                @foreach($config->columns() as $column)
                    @php
                        $thClasses = ['tablekit__th'];
                        if ($column->hiddenOnXs()) {
                            $thClasses[] = 'tablekit__th--hidden-xs';
                        }
                        if ($column->type() === \App\Core\Support\TableKit\Column::TYPE_SELECTION) {
                            $thClasses[] = 'tablekit__th--select';
                        }
                        if ($loop->first) {
                            $thClasses[] = 'tablekit__th--sticky';
                        }
                    @endphp
                    <th scope="col"
                        role="columnheader"
                        class="{{ implode(' ', $thClasses) }}"
                        data-tablekit-col="{{ $column->key() }}"
                        @if($column->sortable())
                            data-tablekit-sortable="true"
                            tabindex="0"
                            aria-sort="none"
                        @endif
                    >
                        @if($column->type() === \App\Core\Support\TableKit\Column::TYPE_SELECTION)
                            <span class="tablekit__checkbox-wrapper">
                                <input type="checkbox" class="tablekit__checkbox" data-tablekit-select-all>
                            </span>
                        @else
                            <span class="tablekit__th-label">{{ $column->label() }}</span>
                            @if($column->sortable())
                                <span class="tablekit__sort-indicator" aria-hidden="true"></span>
                            @endif
                        @endif
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody class="tablekit__body" data-tablekit-body>
            @forelse($rowsCollection as $row)
                @php
                    $cells = Arr::get($row, 'cells', $row);
                    $rowMeta = Arr::get($row, 'meta');
                    $rowId = Arr::get($row, 'id');
                @endphp
                <tr role="row" class="tablekit__row" tabindex="0" data-tablekit-row @if($rowId) data-row-id="{{ $rowId }}" @endif>
                    @foreach($config->columns() as $column)
                        @php
                            $cellValue = $cells[$column->key()] ?? null;
                            $contextCells = $cells;
                            if (! array_key_exists('id', $contextCells) && $rowId) {
                                $contextCells['id'] = $rowId;
                            }
                            $cell = $column->prepareCell($contextCells, $cellValue);
                            $tdClasses = ['tablekit__cell'];
                            if ($column->hiddenOnXs()) {
                                $tdClasses[] = 'tablekit__cell--hidden-xs';
                            }
                            if ($loop->first) {
                                $tdClasses[] = 'tablekit__cell--sticky';
                            }
                            if ($column->type() === \App\Core\Support\TableKit\Column::TYPE_SELECTION) {
                                $tdClasses[] = 'tablekit__cell--select';
                            }
                        @endphp
                        <td role="cell"
                            class="{{ implode(' ', $tdClasses) }}"
                            data-tablekit-col="{{ $column->key() }}"
                            data-tablekit-col-label="{{ $column->label() }}"
                            data-tablekit-raw="{{ is_scalar($cell['raw']) ? e($cell['raw']) : '' }}"
                        >
                            <div class="tablekit__cell-main">{!! $cell['html'] !!}</div>
                            @if($loop->first && $rowMeta)
                                <div class="tablekit__row-meta" data-tablekit-row-meta>{!! $rowMeta !!}</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr class="tablekit__empty" data-tablekit-empty>
                    <td colspan="{{ $columnCount }}">
                        @if($emptyContent !== '')
                            {!! $emptyContent !!}
                        @else
                            {{ $emptyText }}
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="tablekit__footer">
        <div class="tablekit__status" aria-live="polite" data-tablekit-status>
            {{ $statusCount }} kayıt listeleniyor
        </div>
        <div class="tablekit__pager" data-tablekit-pager>
            @if($paginator instanceof LengthAwarePaginator)
                {{ $paginator->links() }}
            @endif
        </div>
        @if($bulkContent !== '')
            <div class="tablekit__bulk" data-tablekit-bulk>
                <div class="tablekit__bulk-count" data-tablekit-bulk-count>0</div>
                <div class="tablekit__bulk-actions">{!! $bulkContent !!}</div>
            </div>
        @endif
    </div>
</div>
