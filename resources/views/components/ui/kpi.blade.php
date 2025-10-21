@props([
    'label',
    'value',
    'delta' => null,
    'trend' => null,
])

<section {{ $attributes->class('ui-kpi')->merge(['data-ui' => 'kpi']) }}>
    <header class="ui-kpi__header">
        <span class="ui-kpi__label">{{ $label }}</span>
        @if($trend)
            <span class="ui-kpi__trend">{{ $trend }}</span>
        @endif
    </header>
    <div class="ui-kpi__value">{{ $value }}</div>
    @if($delta)
        <div class="ui-kpi__delta">{{ $delta }}</div>
    @endif
</section>
