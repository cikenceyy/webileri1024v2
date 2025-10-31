{{--
    Amaç: Boş durum bileşeninin varsayılan metinlerini TR dil birliğine taşımak.
    İlişkiler: PROMPT-1 — TR Dil Birliği.
    Notlar: İlk kayıt yönlendirmesi TR olarak sadeleştirildi.
--}}
@props([
    'title' => 'Henüz veri yok',
    'description' => 'İlk kaydınızı oluşturarak başlayın.',
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
