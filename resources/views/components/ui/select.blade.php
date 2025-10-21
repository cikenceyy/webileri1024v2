@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => null,
])

@php
    $id = $attributes->get('id', $name.'-select');
    $value = old($name, $attributes->get('value'));
    $error = $errors->first($name);
@endphp

<div class="mb-3">
    @if($label)
        <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    @endif

    <select name="{{ $name }}" id="{{ $id }}" {{ $attributes->class(['form-select', 'is-invalid' => $error])->except('value') }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $key => $labelOption)
            <option value="{{ $key }}" @selected((string) $key === (string) $value)>{{ $labelOption }}</option>
        @endforeach
    </select>

    @if($error)
        <div class="invalid-feedback">{{ $error }}</div>
    @endif
</div>
