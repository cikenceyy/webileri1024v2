@props([
    'current' => 1,
    'total' => 1,
])

@php
    $current = (int) $current;
    $total = (int) $total;
@endphp

<nav {{ $attributes->class('ui-pagination')->merge(['role' => 'navigation', 'aria-label' => 'Pagination', 'data-ui' => 'pagination']) }}>
    <button class="ui-pagination__control" type="button" data-action="previous" {{ $current <= 1 ? 'disabled' : '' }} aria-label="Previous page">‹</button>
    <ol class="ui-pagination__list">
        @for($page = 1; $page <= $total; $page++)
            <li class="ui-pagination__item">
                <button type="button" class="ui-pagination__page {{ $page === $current ? 'is-active' : '' }}" data-page="{{ $page }}">
                    {{ $page }}
                </button>
            </li>
        @endfor
    </ol>
    <button class="ui-pagination__control" type="button" data-action="next" {{ $current >= $total ? 'disabled' : '' }} aria-label="Next page">›</button>
</nav>
