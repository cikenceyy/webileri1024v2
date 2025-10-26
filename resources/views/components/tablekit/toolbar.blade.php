@props([
    'config',
    'searchPlaceholder' => __('Ara…'),
    'searchValue' => request('q'),
    'perPage' => request('perPage', 25),
    'perPageOptions' => [25, 50, 100],
])

@php
    use Illuminate\Support\Str;
    $formId = Str::uuid()->toString();
@endphp

<form method="get" class="tablekit__toolbar-form" data-tablekit-form novalidate>
    <div class="tablekit__toolbar-grid">
        <div class="tablekit__toolbar-group tablekit__toolbar-group--search">
            <label for="tablekit-search-{{ $formId }}" class="tablekit__label">{{ __('Ara') }}</label>
            <div class="tablekit__search">
                <input type="search"
                       id="tablekit-search-{{ $formId }}"
                       name="q"
                       value="{{ $searchValue }}"
                       placeholder="{{ $searchPlaceholder }}"
                       class="tablekit__input"
                       data-tablekit-search
                >
            </div>
        </div>

        <div class="tablekit__toolbar-group tablekit__toolbar-group--filters" data-tablekit-filters>
            @foreach($config->filterableColumns() as $column)
                <x-table:col :column="$column" :value="request()->input('filters.' . $column->key())" />
            @endforeach
        </div>

        <div class="tablekit__toolbar-group tablekit__toolbar-group--per-page">
            <label for="tablekit-per-page-{{ $formId }}" class="tablekit__label">{{ __('Sayfa başı') }}</label>
            <select id="tablekit-per-page-{{ $formId }}" name="perPage" class="tablekit__select" data-tablekit-per-page>
                @foreach($perPageOptions as $option)
                    <option value="{{ $option }}" @selected((int) $perPage === (int) $option)>{{ $option }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <input type="hidden" name="sort" value="{{ request('sort', $config->defaultSort()) }}" data-tablekit-sort-input>

    <div class="tablekit__toolbar-actions">
        {{ $slot }}
        <button type="submit" class="tablekit__btn tablekit__btn--primary" data-tablekit-apply>{{ __('Uygula') }}</button>
        <button type="reset" class="tablekit__btn tablekit__btn--ghost" data-tablekit-reset>{{ __('Temizle') }}</button>
    </div>
</form>
