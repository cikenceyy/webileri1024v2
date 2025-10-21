@props([
    'tabs' => [],
    'active' => null,
])

<div {{ $attributes->class('ui-tabs')->merge(['data-ui' => 'tabs']) }}>
    <div role="tablist" aria-label="Tabs" class="ui-tabs__list">
        @foreach($tabs as $tab)
            @php($isActive = ($active ?? $tabs[0]['id']) === $tab['id'])
            <button
                class="ui-tabs__tab {{ $isActive ? 'is-active' : '' }}"
                role="tab"
                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                aria-controls="{{ $tab['id'] }}-panel"
                id="{{ $tab['id'] }}-tab"
                data-action="toggle"
                data-target="#{{ $tab['id'] }}-panel"
            >
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>
    <div class="ui-tabs__panels">
        {{ $slot }}
    </div>
</div>
