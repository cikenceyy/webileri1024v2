@props([
    'title' => null,
    'id' => 'drawer',
    'side' => 'end',
    'width' => 'md',
    'escClosable' => true,
    'ariaLabel' => null,
])

@php
    $labelId = $title ? $id . '-title' : null;
@endphp

<aside
    {{
        $attributes->class('ui-drawer')->merge([
            'id' => $id,
            'data-ui' => 'drawer',
            'data-side' => $side,
            'data-width' => $width,
            'data-esc-closable' => $escClosable ? 'true' : 'false',
            'aria-hidden' => 'true',
            'hidden' => true,
        ])
    }}
>
    <div class="ui-drawer__overlay" data-action="close"></div>
    <div
        class="ui-drawer__panel"
        role="dialog"
        aria-modal="true"
        @if($labelId) aria-labelledby="{{ $labelId }}" @endif
        @if($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    >
        <header class="ui-drawer__header">
            @if($title)
                <h2 id="{{ $labelId }}" class="ui-drawer__title">{{ $title }}</h2>
            @endif
            <button type="button" class="ui-drawer__close" data-action="close" aria-label="Close drawer">×</button>
        </header>
        <div class="ui-drawer__body">
            {{ $slot }}
        </div>
        @isset($footer)
            <footer class="ui-drawer__footer">{{ $footer }}</footer>
        @endisset
    </div>
</aside>
