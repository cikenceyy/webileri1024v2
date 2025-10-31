{{--
    Amaç: Arama bileşeni varsayılanlarını TR diline çekmek.
    İlişkiler: PROMPT-1 — TR Dil Birliği.
    Notlar: Etiket ve placeholder TR karşılıkları ile güncellendi.
--}}
@props([
    'label' => 'Ara',
    'name' => 'search',
    'placeholder' => 'Ara…',
])

<x-ui-input
    {{ $attributes->merge(['type' => 'search']) }}
    :label="$label"
    :name="$name"
    :prefix="'<span aria-hidden="true" class="ui-icon">🔍</span>'"
    :help="null"
    :error="null"
    placeholder="{{ $placeholder }}"
/>
