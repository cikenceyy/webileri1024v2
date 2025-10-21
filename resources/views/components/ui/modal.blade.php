@props([
    'title' => null,
    'id' => 'modal',
    'size' => 'md',
    'escClosable' => true,
    'ariaLabel' => null,
])

@php
    $labelId = $title ? $id . '-title' : null;
@endphp

<div
    {{
        $attributes->class('ui-modal')->merge([
            'id' => $id,
            'data-ui' => 'modal',
            'data-size' => $size,
            'data-esc-closable' => $escClosable ? 'true' : 'false',
            'aria-hidden' => 'true',
            'hidden' => true,
        ])
    }}
>
    <div class="ui-modal__overlay" data-action="close"></div>
    <div
        class="ui-modal__dialog"
        role="dialog"
        aria-modal="true"
        @if($labelId) aria-labelledby="{{ $labelId }}" @endif
        @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    >
        <header class="ui-modal__header">
            @if($title)
                <h2 id="{{ $labelId }}" class="ui-modal__title">{{ $title }}</h2>
            @endif
            <button type="button" class="ui-modal__close" data-action="close" aria-label="Close modal">×</button>
        </header>
        <div class="ui-modal__body">
            {{ $slot }}
        </div>
        @isset($footer)
            <footer class="ui-modal__footer">{{ $footer }}</footer>
        @endisset
    </div>
</div>
