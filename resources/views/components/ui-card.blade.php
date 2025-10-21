@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
])

<section {{ $attributes->class('ui-card')->merge(['data-ui' => 'card']) }}>
    @if($title || $actions)
        <header class="ui-card__header">
            <div class="ui-card__titles">
                @if($title)
                    <h3 class="ui-card__title">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="ui-card__subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            @if($actions)
                <div class="ui-card__actions">{!! $actions !!}</div>
            @endif
        </header>
    @endif
    <div class="ui-card__body">
        {{ $slot }}
    </div>
    @isset($footer)
        <footer class="ui-card__footer">{{ $footer }}</footer>
    @endisset
</section>
