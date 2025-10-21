@props([
    'variant' => null,
    'type' => null,
    'tone' => null,
    'soft' => false,
    'icon' => null,
])

@php
    $resolvedVariant = $variant ?? $type ?? 'primary';
    $resolvedTone = $tone ?? ($soft ? 'soft' : 'solid');
@endphp

<span
    {{
        $attributes
            ->class(['ui-badge', 'ui-badge--' . $resolvedVariant])
            ->merge(['data-ui' => 'badge', 'data-tone' => $resolvedTone])
    }}
>
    @if($icon)
        <span class="ui-badge__icon" aria-hidden="true">{!! $icon !!}</span>
    @endif
    <span class="ui-badge__label">{{ $slot }}</span>
</span>
