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
    $inputId = $attributes['id'] ?? $name;
@endphp

<div {{ $attributes->class(['ui-field', $error ? 'is-invalid' : null])->merge(['data-ui' => 'field'])->except(['value']) }}>
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
            {{ $attributes->except(['value', 'id', 'name'])->merge(['class' => 'ui-input', 'data-ui' => 'input']) }}
            @if(! is_null($value)) value="{{ $value }}" @endif
        >

        @if($suffix)
            <span class="ui-field__affix ui-field__affix--suffix">{!! $suffix !!}</span>
        @endif
    </div>

    @if($help && !$error)
        <p class="ui-field__help">{{ $help }}</p>
    @endif

    @if($error)
        <p class="ui-field__error" role="alert">{{ $error }}</p>
    @endif
</div>
