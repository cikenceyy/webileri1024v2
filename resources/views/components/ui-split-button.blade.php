@props([
    'label',
    'actions' => [],
    'primaryVariant' => 'primary',
])

<div {{ $attributes->class('ui-split-button')->merge(['data-ui' => 'split-button']) }}>
    <x-ui-button type="button" :variant="$primaryVariant">{{ $label }}</x-ui-button>
    <button class="ui-split-button__toggle" type="button" data-action="toggle" aria-haspopup="menu" aria-expanded="false">
        <span class="visually-hidden">Toggle options</span>
        <span aria-hidden="true" class="ui-icon">▾</span>
    </button>

    <ul class="ui-split-button__menu" role="menu">
        @foreach ($actions as $action)
            <li role="none">
                <a role="menuitem" href="{{ $action['href'] ?? '#' }}" class="ui-split-button__item" data-action="{{ $action['action'] ?? 'select' }}">
                    {{ $action['label'] ?? 'Action' }}
                </a>
            </li>
        @endforeach
    </ul>
</div>
