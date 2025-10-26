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
                $classes = 'tablekit__action tablekit__action--'.$variant;
            @endphp
            <a href="{{ $href }}" class="{{ $classes }}" @if($target) target="{{ $target }}" @endif @if($rel) rel="{{ $rel }}" @endif>
                {{ $label }}
            </a>
        @endforeach
    </div>
@endif
