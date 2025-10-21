@props([
    'title' => null,
    'description' => null,
])

<header {{ $attributes->class('ui-page-header')->merge(['data-ui' => 'page-header']) }}>
    <div class="ui-page-header__body">
        @if($title)
            <h1 class="ui-page-header__title">{{ $title }}</h1>
        @endif

        @if($description)
            <p class="ui-page-header__description">{{ $description }}</p>
        @endif

        {{ $slot }}
    </div>

    @isset($actions)
        <div class="ui-page-header__actions">{{ $actions }}</div>
    @endisset
</header>
