@props([
    'label' => 'Search',
    'name' => 'search',
    'placeholder' => 'Search…',
])

<x-ui-input
    {{ $attributes->merge(['type' => 'search']) }}
    :label="$label"
    :name="$name"
    :prefix="'<span aria-hidden="true" class="ui-icon">🔍</span>'"
    :help="null"
    :error="null"
    placeholder="{{ $placeholder }}"
/>
