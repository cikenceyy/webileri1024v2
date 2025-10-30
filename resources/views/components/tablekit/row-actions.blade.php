@props(['actions' => []])

@php
    use Illuminate\Support\Arr;
@endphp

@if(! empty($actions))
    <div class="tablekit__actions">
        @foreach($actions as $action)
            @php
                $action = Arr::wrap($action);
                $label = $action['label'] ?? 'Aksiyon';
                $href = $action['href'] ?? '#';
                $variant = $action['variant'] ?? 'ghost';
                $target = $action['target'] ?? null;
                $rel = $target === '_blank' ? 'noopener noreferrer' : null;
                $permission = $action['permission'] ?? null;
                $ability = $action['ability'] ?? null;
                $arguments = $action['arguments'] ?? [];
                $tooltip = $action['tooltip'] ?? __('Bu işlem için yetkiniz yok');
                $classes = 'tablekit__action tablekit__action--'.$variant;
            @endphp
            <x-auth.action
                class="{{ $classes }}"
                href="{{ $href }}"
                :permission="$permission"
                :ability="$ability"
                :arguments="$arguments"
                :tooltip="$tooltip"
                @if($target) target="{{ $target }}" @endif
                @if($rel) rel="{{ $rel }}" @endif
            >
                {{ $label }}
            </x-auth.action>
        @endforeach
    </div>
@endif
