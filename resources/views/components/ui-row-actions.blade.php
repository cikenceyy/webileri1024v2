@props([
    'actions' => [],
])

@php
    $collection = collect($actions)->filter(fn ($action) => !empty($action['label'] ?? null))->values();
    $primary = $collection->take(2);
    $overflow = $collection->slice(2)->values();
@endphp

<div {{ $attributes->class('ui-row-actions')->merge(['data-ui' => 'row-actions']) }}>
    @foreach($primary as $item)
        <a
            href="{{ $item['href'] ?? '#' }}"
            class="ui-row-actions__button"
            data-action="{{ $item['action'] ?? 'select' }}"
        >
            {{ $item['label'] ?? 'Aksiyon' }}
        </a>
    @endforeach

    @if($overflow->isNotEmpty())
        <details class="ui-row-actions__more">
            <summary class="ui-row-actions__toggle" aria-haspopup="true">
                <span class="visually-hidden">Diğer işlemler</span>
                <span aria-hidden="true" class="ui-icon">⋮</span>
            </summary>
            <ul class="ui-row-actions__menu" role="menu">
                @foreach($overflow as $item)
                    <li role="none">
                        <a
                            role="menuitem"
                            href="{{ $item['href'] ?? '#' }}"
                            class="ui-row-actions__item"
                            data-action="{{ $item['action'] ?? 'select' }}"
                        >
                            {{ $item['label'] ?? 'Aksiyon' }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </details>
    @endif
</div>
