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

        <div class="tablekit__toolbar-group tablekit__toolbar-group--advanced">
            <label for="tablekit-advanced-{{ $formId }}" class="tablekit__label">{{ __('Gelişmiş Filtre') }}</label>
            <input type="text"
                   id="tablekit-advanced-{{ $formId }}"
                   name="filter_text"
                   value="{{ request('filter_text') }}"
                   class="tablekit__input"
                   data-tablekit-advanced
                   placeholder="status:open,closed due_date:2025-01-01..2025-01-31"
                   aria-describedby="tablekit-advanced-help-{{ $formId }}">
            <small id="tablekit-advanced-help-{{ $formId }}" class="form-text text-muted">{{ __('Örnek: durum:open,closed termin:2025-01-01..2025-01-31') }}</small>
        </div>

        <div class="tablekit__toolbar-group tablekit__toolbar-group--saved" data-tablekit-saved>
            <label class="tablekit__label">{{ __('Kaydedilmiş Filtreler') }}</label>
            <div class="tablekit__saved-controls">
                <select class="tablekit__select" data-tablekit-saved-list aria-label="{{ __('Kaydedilmiş filtreler') }}"></select>
                <div class="tablekit__saved-buttons">
                    <button type="button" class="tablekit__btn tablekit__btn--ghost" data-tablekit-saved-apply>{{ __('Uygula') }}</button>
                    <button type="button" class="tablekit__btn tablekit__btn--ghost" data-tablekit-saved-default>{{ __('Varsayılan Yap') }}</button>
                    <button type="button" class="tablekit__btn tablekit__btn--ghost" data-tablekit-saved-delete>{{ __('Sil') }}</button>
                </div>
            </div>
            <button type="button" class="tablekit__btn tablekit__btn--secondary mt-2" data-tablekit-saved-save>{{ __('Filtreyi Kaydet') }}</button>
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
        <div class="tablekit__export-control">
            <label for="tablekit-export-format-{{ $formId }}" class="tablekit__sr">{{ __('Export formatı') }}</label>
            <select id="tablekit-export-format-{{ $formId }}" class="tablekit__select" data-tablekit-export-format>
                <option value="csv">CSV</option>
                <option value="xlsx">XLSX</option>
            </select>
            <button type="button" class="tablekit__btn tablekit__btn--secondary" data-tablekit-export>{{ __('Export') }}</button>
        </div>
        <button type="submit" class="tablekit__btn tablekit__btn--primary" data-tablekit-apply>{{ __('Uygula') }}</button>
        <button type="reset" class="tablekit__btn tablekit__btn--ghost" data-tablekit-reset>{{ __('Temizle') }}</button>
    </div>
</form>
