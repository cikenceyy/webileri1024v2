@php
    $type = $type ?? 'text';
    $value = $value ?? null;
    $hint = $meta['hint'] ?? null;
    $accept = $meta['accept'] ?? null;
@endphp

@if($type === 'textarea' || $type === 'multiline')
    <textarea class="form-control" name="{{ $name }}" rows="{{ $meta['rows'] ?? 4 }}">{{ $value }}</textarea>
@elseif($type === 'image' || $type === 'file')
    @if($value)
        <div class="bg-light border rounded px-3 py-2 mb-2 d-flex justify-content-between align-items-center">
            <a href="{{ $value }}" target="_blank" class="small">{{ __('View current file') }}</a>
            <label class="form-check-label small">
                <input type="checkbox" class="form-check-input" name="{{ $name }}_remove" value="1"> {{ __('Remove') }}
            </label>
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        </div>
    @endif
    <input type="file" class="form-control" name="{{ $name }}" data-field-type="{{ $type }}" @if($accept) accept="{{ $accept }}" @endif>
@elseif($type === 'link')
    <input type="url" class="form-control" name="{{ $name }}" value="{{ $value }}" placeholder="https://...">
@else
    <input type="text" class="form-control" name="{{ $name }}" value="{{ $value }}">
@endif

@if($hint)
    <small class="text-muted d-block mt-1">{{ $hint }}</small>
@endif
