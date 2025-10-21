@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'href' => null,
])

@php
    $base = 'btn d-inline-flex align-items-center gap-2';
    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-outline-secondary',
        'danger' => 'btn-danger',
        'link' => 'btn-link text-decoration-none',
    ];

    $sizes = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $classes = trim($base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? ''));
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'role' => 'button']) }}>
        @if($icon)
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <i class="{{ $icon }}" aria-hidden="true"></i>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
