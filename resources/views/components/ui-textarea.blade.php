@props([
    'label' => null,
    'name' => null,
    'rows' => 3,
    'help' => null,
    'error' => null,
])

<div {{ $attributes->class(['ui-field', $error ? 'is-invalid' : null]) }} data-ui="field">
    @if($label)
        <label class="ui-field__label" for="{{ $attributes['id'] ?? $name }}">{{ $label }}</label>
    @endif

    <div class="ui-field__control">
        <textarea
            name="{{ $name }}"
            id="{{ $attributes['id'] ?? $name }}"
            rows="{{ $rows }}"
            {{ $attributes->merge(['class' => 'ui-textarea', 'data-ui' => 'textarea']) }}
        >{{ $slot }}</textarea>
    </div>

    @if($help && !$error)
        <p class="ui-field__help">{{ $help }}</p>
    @endif

    @if($error)
        <p class="ui-field__error" role="alert">{{ $error }}</p>
    @endif
</div>
