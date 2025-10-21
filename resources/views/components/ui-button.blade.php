@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
    'loading' => false,
])

@php
    $classes = [
        'ui-button',
        'ui-button--' . $variant,
        'ui-button--' . $size,
        $loading ? 'is-loading' : null,
    ];
@endphp

<button {{ $attributes->class(array_filter($classes))->merge(['type' => $type, 'data-ui' => 'button']) }}>
    @if($icon)
        <span class="ui-button__icon" aria-hidden="true">{!! $icon !!}</span>
    @endif
    <span class="ui-button__label">{{ $slot }}</span>
    @if($loading)
        <span class="ui-button__spinner" role="status" aria-live="polite"></span>
    @endif
</button>
