@props([
    'label',
    'value',
    'description' => null,
])

<dl {{ $attributes->class('ui-stat')->merge(['data-ui' => 'stat']) }}>
    <dt class="ui-stat__label">{{ $label }}</dt>
    <dd class="ui-stat__value">{{ $value }}</dd>
    @if($description)
        <dd class="ui-stat__description">{{ $description }}</dd>
    @endif
</dl>
