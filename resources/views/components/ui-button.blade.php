@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
    'href' => null,
    'tag' => null,
    'loading' => false,
])

@php
    $tagName = $tag ?? ($href ? 'a' : 'button');

    $classes = array_filter([
        'ui-button',
        'ui-button--' . $variant,
        'ui-button--' . $size,
        $loading ? 'is-loading' : null,
    ]);

    $iconMarkup = null;

    if ($icon) {
        $iconMarkup = str_contains($icon, '<')
            ? $icon
            : '<i class="' . e($icon) . '" aria-hidden="true"></i>';
    }

    $commonAttributes = $attributes->class($classes)->merge([
        'data-ui' => 'button',
    ]);

    $defaultTagAttributes = $href
        ? $commonAttributes->merge(['href' => $href])
        : $commonAttributes;
@endphp

@switch($tagName)
    @case('a')
        <a href="{{ $href }}" {{ $commonAttributes->merge(['role' => 'button']) }}>
            @if($iconMarkup)
                <span class="ui-button__icon" aria-hidden="true">{!! $iconMarkup !!}</span>
            @endif
            <span class="ui-button__label">{{ $slot }}</span>
            @if($loading)
                <span class="ui-button__spinner" role="status" aria-live="polite"></span>
            @endif
        </a>
        @break

    @case('button')
        <button {{ $commonAttributes->merge(['type' => $type]) }}>
            @if($iconMarkup)
                <span class="ui-button__icon" aria-hidden="true">{!! $iconMarkup !!}</span>
            @endif
            <span class="ui-button__label">{{ $slot }}</span>
            @if($loading)
                <span class="ui-button__spinner" role="status" aria-live="polite"></span>
            @endif
        </button>
        @break

    @default
        <{{ $tagName }} {{ $defaultTagAttributes }}>
            @if($iconMarkup)
                <span class="ui-button__icon" aria-hidden="true">{!! $iconMarkup !!}</span>
            @endif
            <span class="ui-button__label">{{ $slot }}</span>
            @if($loading)
                <span class="ui-button__spinner" role="status" aria-live="polite"></span>
            @endif
        </{{ $tagName }}>
@endswitch
