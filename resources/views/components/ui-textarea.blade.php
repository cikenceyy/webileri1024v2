@props([
    'label' => null,
    'name' => null,
    'rows' => 3,
    'help' => null,
    'error' => null,
    'value' => null,
])

@php
    $fieldId = $attributes->get('id', $name);
    $boundError = $error;

    if (! $boundError && $name) {
        $boundError = $errors->first($name);
    }

    $boundValue = $value;

    if ($attributes->has('value')) {
        $boundValue = $attributes->get('value');
    } elseif ($name) {
        $boundValue = old($name, $value);
    }

    $slotContent = trim((string) $slot);

    if ($boundValue === null && $slotContent !== '') {
        $boundValue = $slotContent;
    }
@endphp

<div {{ $attributes->class(['ui-field', $boundError ? 'is-invalid' : null])->except(['value']) }} data-ui="field">
    @if($label)
        <label class="ui-field__label" for="{{ $fieldId }}">{{ $label }}</label>
    @endif

    <div class="ui-field__control">
        <textarea
            name="{{ $name }}"
            id="{{ $fieldId }}"
            rows="{{ $rows }}"
            {{ $attributes->except(['value', 'id', 'name'])->merge(['class' => 'ui-textarea', 'data-ui' => 'textarea']) }}
        >{{ $boundValue }}</textarea>
    </div>

    @if($help && ! $boundError)
        <p class="ui-field__help">{{ $help }}</p>
    @endif

    @if($boundError)
        <p class="ui-field__error" role="alert">{{ $boundError }}</p>
    @endif
</div>
