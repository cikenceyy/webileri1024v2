@props([
    'variant' => null,
    'type' => null,
    'dismissible' => false,
    'title' => null,
])

@php
    $tone = $variant ?? $type ?? 'info';
@endphp

<div {{ $attributes->class('ui-alert')->merge(['data-ui' => 'alert', 'data-tone' => $tone]) }} role="alert">
    <div class="ui-alert__body">
        @if($title)
            <p class="ui-alert__title">{{ $title }}</p>
        @endif
        {{ $slot }}
    </div>

    @if($dismissible)
        <button type="button" class="ui-alert__close" data-action="dismiss-alert" aria-label="Uyarıyı kapat">×</button>
    @endif
</div>
