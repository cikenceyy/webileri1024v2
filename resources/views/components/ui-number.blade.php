@props([
    'label' => null,
    'name' => null,
    'help' => null,
    'error' => null,
    'step' => '1',
    'min' => null,
    'max' => null,
])

<x-ui-input
    {{ $attributes->merge(['type' => 'number', 'inputmode' => 'decimal']) }}
    :label="$label"
    :name="$name"
    :help="$help"
    :error="$error"
    :min="$min"
    :max="$max"
    step="{{ $step }}"
/>
