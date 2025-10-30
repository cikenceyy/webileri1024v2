{{--
    Yetkiye bağlı aksiyon butonu: izin yoksa pasif ve ipucu gösterir.
--}}
@props([
    'permission' => null,
    'ability' => null,
    'arguments' => [],
    'tag' => 'a',
    'href' => '#',
    'tooltip' => __('Bu işlem için yetkiniz yok'),
])

@php
    $user = auth()->user();
    $allowed = true;

    if (($permission || $ability) && ! $user) {
        $allowed = false;
    }

    if ($permission && $user) {
        if (method_exists($user, 'hasPermissionTo') && class_exists(\Spatie\Permission\Models\Permission::class)) {
            $allowed = $user->hasPermissionTo($permission);
        }
    }

    if ($allowed && $ability && $user) {
        $allowed = \Illuminate\Support\Facades\Gate::allows($ability, $arguments);
    }

    $classes = $attributes->get('class');
    $classes = trim($classes . ' ' . ($allowed ? '' : 'disabled opacity-75 pe-none'));
    $attributes = $attributes->merge(['class' => $classes]);
@endphp

@if($tag === 'button')
    <button
        type="button"
        {{ $attributes }}
        @unless($allowed) disabled aria-disabled="true" data-bs-toggle="tooltip" title="{{ $tooltip }}" @endunless
    >
        {{ $slot }}
    </button>
@else
    <a
        {{ $attributes->merge(['href' => $allowed ? $href : '#']) }}
        @unless($allowed) aria-disabled="true" data-bs-toggle="tooltip" title="{{ $tooltip }}" onclick="return false;" @endunless
    >
        {{ $slot }}
    </a>
@endif
