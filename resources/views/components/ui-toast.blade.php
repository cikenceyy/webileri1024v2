@props([
    'title' => null,
    'variant' => 'info',
    'message' => null,
    'timeout' => 4000,
])

@php
    $messageContent = $message ?? trim((string) $slot);
@endphp

<div {{ $attributes->merge(['class' => 'ui-toast ui-toast--' . $variant, 'role' => 'status', 'data-ui' => 'toast', 'data-timeout' => $timeout]) }}>
    <div class="ui-toast__content">
        @if($title)
            <h3 class="ui-toast__title">{{ $title }}</h3>
        @endif
        @if($messageContent !== '')
            <p class="ui-toast__message">{{ $messageContent }}</p>
        @endif
    </div>
    <button type="button" class="ui-toast__dismiss" data-action="close" aria-label="Dismiss">×</button>
</div>
