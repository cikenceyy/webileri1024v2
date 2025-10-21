@props([
    'items' => [],
])

<div {{ $attributes->class('ui-toolbar')->merge(['role' => 'toolbar', 'data-ui' => 'toolbar']) }}>
    @foreach($items as $item)
        <button type="button" class="ui-toolbar__button" data-action="{{ $item['action'] ?? 'click' }}">
            @if(!empty($item['icon']))
                <span class="ui-toolbar__icon" aria-hidden="true">{{ $item['icon'] }}</span>
            @endif
            <span class="ui-toolbar__label">{{ $item['label'] }}</span>
        </button>
    @endforeach
    {{ $slot }}
</div>
