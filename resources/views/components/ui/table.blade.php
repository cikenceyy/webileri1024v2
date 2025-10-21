@props([
    'responsive' => true,
])

<div {{ $attributes->class(['table-responsive' => $responsive]) }}>
    <table class="table align-middle">
        @isset($thead)
            <thead>
                {{ $thead }}
            </thead>
        @endisset
        @isset($tbody)
            <tbody>
                {{ $tbody }}
            </tbody>
        @endisset
        @isset($slot)
            {{ $slot }}
        @endisset
    </table>
</div>
