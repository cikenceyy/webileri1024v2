@props([
    'code' => '404',
    'title' => 'Sayfa bulunamadı',
    'description' => 'Aradığınız sayfa bulunamadı veya taşınmış olabilir.',
    'hint' => null,
    'meta' => [],
])

@php
    $metaItems = is_array($meta) ? array_filter($meta) : [];
@endphp

<section {{ $attributes->class('ui-error')->merge(['data-ui' => 'error-state']) }}>
    <div class="ui-error__media" aria-hidden="true">
        <div class="ui-error__glyph">⚭</div>
        <div class="ui-error__badge">{{ $code }}</div>
    </div>

    <div class="ui-error__content">
        <h1 class="ui-error__title">{{ $title }}</h1>
        <p class="ui-error__description">{{ $description }}</p>
        @if($hint)
            <p class="ui-error__hint">{{ $hint }}</p>
        @endif

        @if(trim($slot) !== '')
            <div class="ui-error__actions" role="group" aria-label="Hata için aksiyonlar">{!! $slot !!}</div>
        @endif

        @if($metaItems)
            <dl class="ui-error__meta">
                @foreach($metaItems as $label => $value)
                    <div class="ui-error__meta-item">
                        <dt>{{ $label }}</dt>
                        <dd>{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>
