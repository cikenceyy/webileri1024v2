{{--
    Amaç: TableKit satır aksiyonlarını TR dilinde ve güvenli öznitelik bağlarıyla sunmak.
    İlişkiler: PROMPT-1, PROMPT-3 — TR Dil Birliği, TableKit’e Geçiş.
    Notlar: Null öznitelikler bağlanmıyor; varsayılan ipucu metni doğrudan TR.
--}}

@props(['actions' => []])

@php
    use Illuminate\Support\Arr;
@endphp

@if(! empty($actions))
    <div class="tablekit__actions">
        @foreach($actions as $action)
            @php
                $action = Arr::wrap($action);
                $label = $action['label'] ?? 'İşlem';
                $href = $action['href'] ?? '#';
                $variant = $action['variant'] ?? 'ghost';
                $target = $action['target'] ?? null;
                $rel = $target === '_blank' ? 'noopener noreferrer' : null;
                $permission = $action['permission'] ?? null;
                $ability = $action['ability'] ?? null;
                $arguments = $action['arguments'] ?? [];
                $tooltip = $action['tooltip'] ?? 'Bu işlem için yetkiniz yok.';
                $classes = 'tablekit__action tablekit__action--'.$variant;
            @endphp
            <x-auth.action
                class="{{ $classes }}"
                href="{{ $href }}"
                :permission="$permission"
                :ability="$ability"
                :arguments="$arguments"
                :tooltip="$tooltip"
                :target="$target"
                :rel="$rel"
            >
                {{ $label }}
            </x-auth.action>
        @endforeach
    </div>
@endif