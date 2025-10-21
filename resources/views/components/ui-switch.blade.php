@props([
    'label' => null,
    'name' => null,
    'help' => null,
    'checked' => false,
])

<div {{ $attributes->class('ui-switch')->merge(['data-ui' => 'switch']) }}>
    <label class="ui-switch__label">
        <input type="checkbox" name="{{ $name }}" class="ui-switch__control" {{ $checked ? 'checked' : '' }}>
        <span class="ui-switch__track" aria-hidden="true"></span>
        <span class="ui-switch__text">
            <span class="ui-switch__title">{{ $label }}</span>
            @if($help)
                <span class="ui-switch__help">{{ $help }}</span>
            @endif
        </span>
    </label>
</div>
