@props([
    'title' => 'No data yet',
    'description' => 'Get started by creating your first record.',
    'action' => null,
])

<section {{ $attributes->class('ui-empty')->merge(['data-ui' => 'empty-state']) }}>
    <div class="ui-empty__illustration" aria-hidden="true">◎</div>
    <h3 class="ui-empty__title">{{ $title }}</h3>
    <p class="ui-empty__description">{{ $description }}</p>
    @if($action)
        <div class="ui-empty__action">{!! $action !!}</div>
    @endif
</section>
