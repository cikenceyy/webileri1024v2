@props([
    'value' => null,
    'name' => null,
    'placeholder' => 'Click to edit',
])

@php($inputId = $name ? 'inline-' . $name : uniqid('inline-'))

<div
    {{ $attributes->class('ui-inline-edit')->merge(['data-ui' => 'inline-edit']) }}
    @if($name) data-field="{{ $name }}" @endif
>
    <button
        type="button"
        class="ui-inline-edit__display"
        data-action="open"
        data-placeholder="{{ $placeholder }}"
        aria-expanded="false"
        aria-controls="{{ $inputId }}-form"
    >{{ $value ?? $placeholder }}</button>
    <form class="ui-inline-edit__form" method="post" data-action="save" id="{{ $inputId }}-form">
        <label class="visually-hidden" for="{{ $inputId }}">{{ $placeholder }}</label>
        <input id="{{ $inputId }}" name="{{ $name }}" class="ui-inline-edit__input" value="{{ $value }}">
        <div class="ui-inline-edit__actions">
            <x-ui.button type="submit" size="sm">Save</x-ui.button>
            <x-ui.button type="button" size="sm" variant="ghost" data-action="cancel">Cancel</x-ui.button>
        </div>
    </form>
    <div class="ui-inline-edit__feedback" aria-live="polite" data-ui="inline-status"></div>
    <div class="ui-inline-edit__undo" data-ui="inline-undo" hidden>
        <span class="ui-inline-edit__undo-label">Değişiklik kaydedildi.</span>
        <button type="button" class="ui-inline-edit__undo-action" data-action="undo">Geri al</button>
    </div>
</div>
