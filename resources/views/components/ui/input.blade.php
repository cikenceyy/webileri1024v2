@props([
    'label' => null,
    'name' => null,
    'help' => null,
    'error' => null,
    'type' => 'text',
    'prefix' => null,
    'suffix' => null,
    'value' => null,
])

@php
    $inputId = $attributes->get('id', $name);
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

    if (in_array($type, ['file', 'password'], true)) {
        $boundValue = null;
    }
@endphp

<div {{ $attributes->class(['ui-field', $boundError ? 'is-invalid' : null])->merge(['data-ui' => 'field'])->except(['value']) }}>
    @if($label)
        <label class="ui-field__label" for="{{ $inputId }}">{{ $label }}</label>
    @endif

    <div class="ui-field__control">
        @if($prefix)
            <span class="ui-field__affix ui-field__affix--prefix">{!! $prefix !!}</span>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            @if(! is_null($boundValue)) value="{{ $boundValue }}" @endif
            {{ $attributes->except(['value', 'id', 'name'])->merge(['class' => 'ui-input', 'data-ui' => 'input']) }}
        >

        @if($suffix)
            <span class="ui-field__affix ui-field__affix--suffix">{!! $suffix !!}</span>
        @endif
    </div>

    @if($help && ! $boundError)
        <p class="ui-field__help">{{ $help }}</p>
    @endif

    @if($boundError)
        <p class="ui-field__error" role="alert">{{ $boundError }}</p>
    @endif
</div>
