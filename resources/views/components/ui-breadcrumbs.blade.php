@props([
    'items' => [],
])

<nav {{ $attributes->class('ui-breadcrumbs')->merge(['aria-label' => 'Breadcrumb', 'data-ui' => 'breadcrumbs']) }}>
    <ol class="ui-breadcrumbs__list">
        @foreach($items as $item)
            <li class="ui-breadcrumbs__item">
                @if(!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="ui-breadcrumbs__link">{{ $item['label'] }}</a>
                @else
                    <span class="ui-breadcrumbs__current" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
