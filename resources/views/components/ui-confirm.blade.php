{{--
    Amaç: Onay bileşeni varsayılan metinlerini TR diline taşımak ve erişilebilirlik niteliklerini hizalamak.
    İlişkiler: PROMPT-1 — TR Dil Birliği.
    Notlar: Varsayılan başlık ve etiketler TR olarak ayarlandı.
--}}
@props([
    'title' => 'Emin misiniz?',
    'message' => 'Bu işlem geri alınamaz.',
    'confirmLabel' => 'Onayla',
    'cancelLabel' => 'İptal',
    'type' => 'primary',
])

@php
    $danger = $type === 'danger';
@endphp

<x-ui-modal {{ $attributes }} :title="$title" size="sm" :esc-closable="!$danger">
    <p>{{ $message }}</p>
    <x-slot name="footer">
        <div class="ui-confirm__actions" role="group" aria-label="Onaylama işlemleri">
            <x-ui-button type="button" variant="ghost" data-action="close" data-intent="cancel">{{ $cancelLabel }}</x-ui-button>
            <x-ui-button
                type="button"
                :variant="$danger ? 'danger' : 'primary'"
                data-action="close"
                data-intent="confirm"
                data-autofocus="true"
            >
                {{ $confirmLabel }}
            </x-ui-button>
        </div>
    </x-slot>
</x-ui-modal>
