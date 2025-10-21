@props([
    'label' => null,
    'name' => null,
    'help' => null,
    'error' => null,
])

<x-ui-input
    {{ $attributes->merge(['type' => 'date']) }}
    :label="$label"
    :name="$name"
    :help="$help"
    :error="$error"
/>
