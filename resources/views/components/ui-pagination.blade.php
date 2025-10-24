@props([
    'current' => null,
    'total' => null,
    'paginator' => null,
])

@php
    $paginator = $paginator ?? null;
    $pages = [];
    $previousUrl = null;
    $nextUrl = null;
    $currentPage = null;

    if ($paginator instanceof Illuminate\Contracts\Pagination\LengthAwarePaginator) {
        $currentPage = $paginator->currentPage();
        $lastPage = max(1, $paginator->lastPage());
        $previousUrl = $paginator->previousPageUrl();
        $nextUrl = $paginator->nextPageUrl();
        $window = 2;
        $start = max(1, $currentPage - $window);
        $end = min($lastPage, $currentPage + $window);

        if ($start > 1) {
            $pages[] = ['page' => 1, 'url' => $paginator->url(1), 'active' => $currentPage === 1];
            if ($start > 2) {
                $pages[] = ['separator' => true];
            }
        }

        for ($page = $start; $page <= $end; $page++) {
            $pages[] = [
                'page' => $page,
                'url' => $paginator->url($page),
                'active' => $page === $currentPage,
            ];
        }

        if ($end < $lastPage) {
            if ($end < $lastPage - 1) {
                $pages[] = ['separator' => true];
            }
            $pages[] = ['page' => $lastPage, 'url' => $paginator->url($lastPage), 'active' => $lastPage === $currentPage];
        }
    } elseif ($paginator instanceof Illuminate\Contracts\Pagination\Paginator) {
        $currentPage = $paginator->currentPage();
        $previousUrl = $paginator->previousPageUrl();
        $nextUrl = $paginator->nextPageUrl();
        $pages[] = [
            'page' => $currentPage,
            'url' => $paginator->url($currentPage),
            'active' => true,
        ];
        if ($paginator->hasMorePages()) {
            $pages[] = [
                'page' => $currentPage + 1,
                'url' => $paginator->url($currentPage + 1),
                'active' => false,
            ];
        }
    } else {
        $currentPage = max(1, (int) ($current ?? 1));
        $lastPage = max(1, (int) ($total ?? 1));
        $previousUrl = $currentPage > 1 ? '#' : null;
        $nextUrl = $currentPage < $lastPage ? '#' : null;

        for ($page = 1; $page <= $lastPage; $page++) {
            $pages[] = [
                'page' => $page,
                'url' => '#',
                'active' => $page === $currentPage,
            ];
        }
    }
@endphp

<nav {{ $attributes->class('ui-pagination')->merge(['role' => 'navigation', 'aria-label' => 'Sayfalandırma', 'data-ui' => 'pagination']) }}>
    @if($previousUrl)
        <a class="ui-pagination__control" href="{{ $previousUrl }}" rel="prev" aria-label="Önceki sayfa">‹</a>
    @else
        <span class="ui-pagination__control is-disabled" aria-disabled="true" aria-label="Önceki sayfa">‹</span>
    @endif
    <ol class="ui-pagination__list">
        @foreach($pages as $page)
            <li class="ui-pagination__item">
                @if(!empty($page['separator']))
                    <span class="ui-pagination__ellipsis" aria-hidden="true">…</span>
                @else
                    @php
                        $isActive = !empty($page['active']);
                        $url = $page['url'] ?? null;
                    @endphp
                    @if($url)
                        <a class="ui-pagination__page {{ $isActive ? 'is-active' : '' }}" href="{{ $url }}"
                            aria-current="{{ $isActive ? 'page' : 'false' }}"
                            aria-label="Sayfa {{ $page['page'] }}">
                            {{ $page['page'] }}
                        </a>
                    @else
                        <span class="ui-pagination__page {{ $isActive ? 'is-active' : '' }} is-disabled"
                            aria-disabled="true"
                            aria-current="{{ $isActive ? 'page' : 'false' }}">
                            {{ $page['page'] }}
                        </span>
                    @endif
                @endif
            </li>
        @endforeach
    </ol>
    @if($nextUrl)
        <a class="ui-pagination__control" href="{{ $nextUrl }}" rel="next" aria-label="Sonraki sayfa">›</a>
    @else
        <span class="ui-pagination__control is-disabled" aria-disabled="true" aria-label="Sonraki sayfa">›</span>
    @endif
</nav>
