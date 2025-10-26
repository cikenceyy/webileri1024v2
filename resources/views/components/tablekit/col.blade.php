@props(['column', 'value' => null])

@php
    use App\Core\Support\TableKit\Column;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;
    use function collect;

    /** @var Column $column */
    $key = $column->key();
    $label = $column->label();
    $id = 'tablekit-filter-'.$key.'-'.Str::uuid()->toString();
    $type = $column->type();
    $enum = $column->enum();
    $options = $column->options();
@endphp

<div class="tablekit__filter" data-tablekit-filter-wrapper data-tablekit-filter-type="{{ $type }}" data-tablekit-filter-key="{{ $key }}">
    <label for="{{ $id }}" class="tablekit__label">{{ $label }}</label>

    @switch($type)
        @case('badge')
        @case('enum')
            @php
                $selected = collect(Arr::wrap($value))->map(fn ($item) => (string) $item)->all();
            @endphp
            <select id="{{ $id }}" name="filters[{{ $key }}][]" class="tablekit__select" multiple data-tablekit-filter="enum">
                @foreach($enum ?? [] as $enumKey => $enumLabel)
                    <option value="{{ $enumKey }}" @selected(in_array((string) $enumKey, $selected, true))>{{ $enumLabel }}</option>
                @endforeach
            </select>
            @break

        @case('number')
        @case('money')
            <input id="{{ $id }}"
                   type="number"
                   step="{{ Arr::get($options, 'step', $type === 'money' ? '0.01' : '1') }}"
                   class="tablekit__input"
                   name="filters[{{ $key }}]"
                   value="{{ is_array($value) ? Arr::get($value, 'value', '') : $value }}"
                   data-tablekit-filter="number"
            >
            @break

        @case('date')
            @php
                $from = is_array($value) ? Arr::get($value, 'from') : null;
                $to = is_array($value) ? Arr::get($value, 'to') : null;
            @endphp
            <div class="tablekit__date-range" data-tablekit-filter="date-range" data-locale="{{ app()->getLocale() }}">
                <input type="date" name="filters[{{ $key }}][from]" value="{{ $from }}" class="tablekit__input" aria-label="{{ $label }} {{ __('başlangıç') }}">
                <span class="tablekit__date-separator">—</span>
                <input type="date" name="filters[{{ $key }}][to]" value="{{ $to }}" class="tablekit__input" aria-label="{{ $label }} {{ __('bitiş') }}">
            </div>
            @break

        @default
            <input id="{{ $id }}"
                   type="text"
                   class="tablekit__input"
                   name="filters[{{ $key }}]"
                   value="{{ is_array($value) ? Arr::get($value, 'value', '') : $value }}"
                   placeholder="{{ Arr::get($options, 'placeholder', $label) }}"
                   data-tablekit-filter="text"
            >
    @endswitch
</div>
