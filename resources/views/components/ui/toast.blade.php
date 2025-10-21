@props([
    'title' => null,
    'message' => null,
    'variant' => 'info',
    'timeout' => 4000,
])

@php
    $variantClass = 'ui-toast ui-toast--'.$variant;
@endphp

<div {{ $attributes->merge([
    'class' => $variantClass,
    'data-ui' => 'toast',
    'role' => 'status',
    'aria-live' => 'polite',
    'aria-atomic' => 'true',
    'data-timeout' => $timeout,
]) }}>
    <div class="ui-toast__content">
        @if($title)
            <h3 class="ui-toast__title">{{ $title }}</h3>
        @endif
        <p class="ui-toast__message">{{ $message ?? $slot }}</p>
    </div>
    <button type="button" class="ui-toast__dismiss" data-action="close" aria-label="Kapat">&times;</button>
</div>
