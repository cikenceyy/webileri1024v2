@php
    $mode = $mode ?? 'form';
    $label = $definition['label'] ?? ucfirst(str_replace('_', ' ', $blockKey));
    $fields = $definition['fields'] ?? [];
    $isRepeater = !empty($definition['repeater']);
    $items = [];

    if ($isRepeater) {
        if (is_iterable($values ?? null)) {
            foreach ($values as $key => $value) {
                $items[$key] = is_array($value) ? $value : [];
            }
        }
    }
@endphp

@if($isRepeater)
    @php($htmlId = 'repeater-' . $localeKey . '-' . $blockKey)
    <div class="{{ $mode === 'editor' ? 'repeater' : 'mb-4' }}" data-repeater data-block-key="{{ $blockKey }}" data-locale="{{ $localeKey }}" id="{{ $htmlId }}">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong class="small text-uppercase text-muted">{{ $label }}</strong>
            <button class="btn btn-sm btn-outline-primary" type="button" data-repeater-add>{{ __('Add item') }}</button>
        </div>

        <template data-repeater-template>
            <div class="border rounded p-3 mb-3 repeater-item" data-repeater-item>
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <strong class="small text-uppercase text-muted">{{ __('Item') }}</strong>
                    <div class="btn-group btn-group-sm" role="group">
                        @if($mode === 'editor')
                            <button class="btn btn-outline-secondary" type="button" data-repeater-up aria-label="{{ __('Move up') }}">&uarr;</button>
                            <button class="btn btn-outline-secondary" type="button" data-repeater-down aria-label="{{ __('Move down') }}">&darr;</button>
                        @endif
                        <button class="btn btn-outline-danger" type="button" data-repeater-remove>{{ __('Remove') }}</button>
                    </div>
                </div>
                @include('cms::admin.cms.partials.repeater-fields', [
                    'fields' => $fields,
                    'namePrefix' => "content[{$localeKey}][{$blockKey}][__INDEX__]",
                    'values' => [],
                ])
            </div>
        </template>

        <div class="repeater-items" data-repeater-items>
            @forelse($items as $index => $item)
                <div class="border rounded p-3 mb-3 repeater-item" data-repeater-item>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong class="small text-uppercase text-muted">{{ __('Item') }} #{{ $loop->iteration }}</strong>
                        <div class="btn-group btn-group-sm" role="group">
                            @if($mode === 'editor')
                                <button class="btn btn-outline-secondary" type="button" data-repeater-up aria-label="{{ __('Move up') }}">&uarr;</button>
                                <button class="btn btn-outline-secondary" type="button" data-repeater-down aria-label="{{ __('Move down') }}">&darr;</button>
                            @endif
                            <button class="btn btn-outline-danger" type="button" data-repeater-remove>{{ __('Remove') }}</button>
                        </div>
                    </div>
                    @include('cms::admin.cms.partials.repeater-fields', [
                        'fields' => $fields,
                        'namePrefix' => "content[{$localeKey}][{$blockKey}][{$index}]",
                        'values' => $item,
                    ])
                </div>
            @empty
                <p class="text-muted small" data-repeater-empty>{{ __('No items yet.') }}</p>
            @endforelse
        </div>
    </div>
@else
    @include('cms::admin.cms.partials.repeater-fields', [
        'fields' => $fields,
        'namePrefix' => "content[{$localeKey}][{$blockKey}]",
        'values' => is_array($values ?? null) ? $values : [],
    ])
@endif
