@props([
    'config',
    'rows' => [],
    'paginator' => null,
    'id' => null,
    'emptyText' => __('Kayıt bulunamadı.'),
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
    $statusCount = $mode === 'server' ? $totalCount : $rowsCollection->count();
@endphp

<div {{ $attributes->class(['tablekit'])->merge([
    'id' => $componentId,
    'data-tablekit' => 'true',
    'data-tablekit-mode' => $mode,
    'data-tablekit-count' => $totalCount,
    'data-tablekit-client-threshold' => $config->clientThreshold(),
    'data-tablekit-default-sort' => $config->defaultSort() ?? '',
]) }}>
    <script type="application/json" data-tablekit-dataset>
        {!! json_encode($dataset, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
    </script>

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
                        <span class="tablekit__th-label">{{ $column->label() }}</span>
                        @if($column->sortable())
                            <span class="tablekit__sort-indicator" aria-hidden="true"></span>
                        @endif
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody class="tablekit__body" data-tablekit-body>
            @forelse($rowsCollection as $row)
                @php
                    $cells = Arr::get($row, 'cells', $row);
                @endphp
                <tr role="row" class="tablekit__row" tabindex="0">
                    @foreach($config->columns() as $column)
                        @php
                            $cellValue = $cells[$column->key()] ?? null;
                            $cell = $column->prepareCell($cells, $cellValue);
                            $tdClasses = ['tablekit__cell'];
                            if ($column->hiddenOnXs()) {
                                $tdClasses[] = 'tablekit__cell--hidden-xs';
                            }
                            if ($loop->first) {
                                $tdClasses[] = 'tablekit__cell--sticky';
                            }
                        @endphp
                        <td role="cell"
                            class="{{ implode(' ', $tdClasses) }}"
                            data-tablekit-col="{{ $column->key() }}"
                            data-tablekit-col-label="{{ $column->label() }}"
                            data-tablekit-raw="{{ is_scalar($cell['raw']) ? e($cell['raw']) : '' }}"
                        >{!! $cell['html'] !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr class="tablekit__empty">
                    <td colspan="{{ $columnCount }}">{{ $emptyText }}</td>
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
    </div>
</div>
