@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'placeholder' => null,
    'help' => null,
    'error' => null,
    'value' => null,
])

@php
    $rawValue = $value instanceof \UnitEnum ? $value->value : $value;

    if ($name) {
        $submitted = old($name);

        if (! is_null($submitted)) {
            $rawValue = $submitted;
        }
    }

    $selectId = $attributes['id'] ?? $name;
    $isMultiple = $attributes->has('multiple');
    $boundError = $error;

    if (! $boundError && $name) {
        $boundError = $errors->first($name);
    }
    $selectedValues = collect(Illuminate\Support\Arr::wrap($rawValue))
        ->filter(static fn ($item) => ! is_null($item) && $item !== '')
        ->map(static fn ($item) => (string) $item)
        ->when(! $isMultiple, static fn ($collection) => $collection->take(1))
        ->values();

    $normalized = collect($options)->map(function ($option, $key) {
        if (is_array($option)) {
            return [
                'value' => (string) ($option['value'] ?? $key),
                'label' => $option['label'] ?? ($option['value'] ?? $key),
                'disabled' => (bool) ($option['disabled'] ?? false),
            ];
        }

        $value = is_int($key) ? $option : $key;

        return [
            'value' => (string) $value,
            'label' => $option,
            'disabled' => false,
        ];
    })->values();
@endphp

<div {{ $attributes->class(['ui-field', $boundError ? 'is-invalid' : null])->merge(['data-ui' => 'field'])->except(['value']) }}>
    @if($label)
        <label class="ui-field__label" for="{{ $selectId }}">{{ $label }}</label>
    @endif

    <div class="ui-field__control">
        <select
            name="{{ $name }}"
            id="{{ $selectId }}"
            {{ $attributes->except(['value', 'multiple', 'id', 'name'])->merge(['class' => 'ui-select', 'data-ui' => 'select']) }}
            @if($isMultiple) multiple @endif
        >
            @if($placeholder)
                <option value="" disabled @if($selectedValues->isEmpty()) selected @endif>{{ $placeholder }}</option>
            @endif
            @foreach($normalized as $option)
                <option value="{{ $option['value'] }}" @if($option['disabled']) disabled @endif @if($selectedValues->contains($option['value'])) selected @endif>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    @if($help && ! $boundError)
        <p class="ui-field__help">{{ $help }}</p>
    @endif

    @if($boundError)
        <p class="ui-field__error" role="alert">{{ $boundError }}</p>
    @endif
</div>
