<div {{ $attributes->merge(['class' => 'ui-toast-stack', 'data-ui' => 'toast-container', 'aria-live' => 'polite', 'aria-atomic' => 'true']) }}>
    {{ $slot }}
</div>
