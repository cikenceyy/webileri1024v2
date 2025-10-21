@props([
    'size' => 'md',
    'label' => 'Loading',
])

<span {{ $attributes->class(['ui-spinner', 'ui-spinner--' . $size])->merge(['role' => 'status', 'aria-live' => 'polite', 'data-ui' => 'spinner']) }}>
    <span class="ui-spinner__circle" aria-hidden="true"></span>
    <span class="ui-spinner__label">{{ $label }}</span>
</span>
