@props([
    'title' => 'Are you sure?',
    'message' => 'This action cannot be undone.',
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
    'type' => 'primary',
])

@php
    $danger = $type === 'danger';
@endphp

<x-ui-modal {{ $attributes }} :title="$title" size="sm" :esc-closable="!$danger">
    <p>{{ $message }}</p>
    <x-slot name="footer">
        <div class="ui-confirm__actions" role="group" aria-label="Confirmation actions">
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
