@props([
    'ext' => 'file',
    'size' => 40,
])

@php
    $extension = strtolower($ext ?? 'file');
    $display = strtoupper(\Illuminate\Support\Str::limit($extension, 4, ''));
    $sizeValue = is_numeric($size) ? (int) $size : (int) filter_var($size, FILTER_SANITIZE_NUMBER_INT);
    $sizeValue = max($sizeValue, 16);

    $groups = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'tif', 'tiff'],
        'pdf' => ['pdf'],
        'doc' => ['doc', 'docx', 'rtf', 'txt', 'md'],
        'sheet' => ['xls', 'xlsx', 'csv', 'ods'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
        'audio' => ['mp3', 'wav', 'aac', 'ogg', 'flac'],
        'video' => ['mp4', 'mov', 'avi', 'mkv', 'webm'],
        'code' => ['json', 'xml', 'html', 'css', 'js', 'php'],
    ];

    $kind = 'file';

    foreach ($groups as $label => $extensions) {
        if (in_array($extension, $extensions, true)) {
            $kind = $label;
            break;
        }
    }

    if ($display === '') {
        $display = match ($kind) {
            'image' => 'IMG',
            'pdf' => 'PDF',
            'doc' => 'DOC',
            'sheet' => 'SHT',
            'archive' => 'ZIP',
            'audio' => 'AUD',
            'video' => 'VID',
            'code' => 'CODE',
            default => 'FILE',
        };
    }

    $style = trim((string) ($attributes['style'] ?? ''));
    $style = $style !== '' ? rtrim($style, ';') . '; ' : '';
    $style .= '--ui-file-icon-size: ' . $sizeValue . 'px;';
@endphp

<span
    {{
        $attributes
            ->class('ui-file-icon')
            ->merge([
                'data-ui' => 'file-icon',
                'data-kind' => $kind,
                'style' => $style,
                'aria-hidden' => 'true',
            ])
    }}
>
    <span class="ui-file-icon__label">{{ $display }}</span>
</span>
