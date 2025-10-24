@php
    $fields = $fields ?? [];
    $namePrefix = $namePrefix ?? '';
    $values = $values ?? [];
@endphp

@foreach($fields as $fieldKey => $fieldDefinition)
    <div class="mb-3">
        <label class="form-label small text-uppercase">
            {{ $fieldDefinition['label'] ?? ucfirst(str_replace('_', ' ', $fieldKey)) }}
        </label>
        @include('cms::admin.cms.partials.field', [
            'type' => $fieldDefinition['type'] ?? 'text',
            'name' => trim($namePrefix) !== '' ? $namePrefix . '[' . $fieldKey . ']' : $fieldKey,
            'value' => $values[$fieldKey] ?? null,
            'meta' => $fieldDefinition,
        ])
    </div>
@endforeach
