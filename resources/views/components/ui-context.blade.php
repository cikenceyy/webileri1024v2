@props([
    'module' => null, // İstersen attribute ile manuel geçebilirsin
    'page'   => null,
])

@php
    // --- Güvenli string yardımcıları ---
    $asString = function ($v): string {
        return is_string($v) ? trim($v) : (is_numeric($v) ? trim((string)$v) : '');
    };
    $safeSlug = function (string $v): string {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $v);
        return strtolower(trim($slug ?? '', '-'));
    };

    // --- Öncelik: component attribute -> section -> view var ---
    $moduleContext = $asString($module);
    $pageContext   = $asString($page);

    if ($moduleContext === '') {
        $moduleContext = $asString($__env->yieldContent('module'));
    }
    if ($pageContext === '') {
        $pageContext = $asString($__env->yieldContent('page'));
    }

    if ($moduleContext === '' && isset($module)) {
        $moduleContext = $asString($module);
    }
    if ($pageContext === '' && isset($page)) {
        $pageContext = $asString($page);
    }

    // --- Route adına göre otomatik modül adı (section/attr yoksa) ---
    if ($moduleContext === '') {
        $routeName = request()->route()?->getName() ?? '';
        $prefixMap = [
            'admin.inventory.' => 'Inventory',
            'admin.crmsales.'  => 'Marketing',
            'admin.marketing.' => 'Marketing',
            'admin.logistics.' => 'Logistics',
            'admin.finance.'   => 'Finance',
            'admin.cms.'       => 'CMS', // CMS desteği
        ];
        foreach ($prefixMap as $prefix => $name) {
            if ($routeName !== '' && str_starts_with($routeName, $prefix)) {
                $moduleContext = $name;
                break;
            }
        }
    }

    $moduleSlug = $moduleContext !== '' ? $safeSlug($moduleContext) : '';
@endphp

<main
    id="main-content"
    class="layout-main"
    tabindex="-1"
    data-ui="content"
    @if($moduleContext !== '') data-module="{{ $moduleContext }}" @endif
    @if($moduleSlug   !== '') data-module-slug="{{ $moduleSlug }}"   @endif
    @if($pageContext  !== '') data-page="{{ $pageContext }}"         @endif
>
    {{ $slot }}
</main>
