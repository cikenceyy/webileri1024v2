@props([
    'lines' => 3,
])

<div {{ $attributes->class('ui-skeleton')->merge(['data-ui' => 'skeleton']) }}>
    @for($line = 0; $line < $lines; $line++)
        <div class="ui-skeleton__line"></div>
    @endfor
</div>
