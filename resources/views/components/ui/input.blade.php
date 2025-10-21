@props([
    'name',
    'type' => 'text',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'help' => null,
])

@php
    $id = $attributes->get('id', $name.'-field');
    $error = $errors->first($name);
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->class(['form-control', 'is-invalid' => $error]) }}
    />

    @if($help && !$error)
        <div class="form-text">{{ $help }}</div>
    @endif

    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif
</div>
