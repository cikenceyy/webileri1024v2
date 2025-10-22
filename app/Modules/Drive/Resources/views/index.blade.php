@extends('layouts.admin')

@section('title', 'Drive')
@section('module', 'Drive')

@push('page-styles')
    @vite('app/Modules/Drive/Resources/scss/drive.scss')
@endpush

@push('page-scripts')
    @vite('app/Modules/Drive/Resources/js/drive.js')
@endpush

@section('content')
@php
    use App\Modules\Drive\Domain\Models\Media;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Str;

    $formatSize = static function (?int $bytes): string {
        $bytes = $bytes ?? 0;

        if ($bytes >= 1_073_741_824) {
            return number_format($bytes / 1_073_741_824, 2) . ' GB';
        }

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    };

    $categories = array_filter($tabs, fn ($key) => in_array($key, [
        Media::CATEGORY_DOCUMENTS,
        Media::CATEGORY_MEDIA_PRODUCTS,
        Media::CATEGORY_MEDIA_CATALOGS,
        Media::CATEGORY_PAGES,
    ], true), ARRAY_FILTER_USE_KEY);

    $activeCategory = in_array($tab, array_keys($categories), true)
        ? $tab
        : Media::CATEGORY_DOCUMENTS;

    $categoryLimits = collect($categoryConfig ?? [])->mapWithKeys(fn ($config, $key) => [
        $key => [
            'mimes' => implode(', ', $config['ext'] ?? []),
            'max' => $formatSize(min((int) ($config['max'] ?? $globalMaxBytes), $globalMaxBytes)),
        ],
    ]);

    $initialView = request()->cookie('drive_view', 'grid');

    $folderGroups = [
        'Sık Kullanılanlar' => array_values(array_filter(['recent', 'important'], fn ($key) => array_key_exists($key, $tabs))),
        'Kategoriler' => array_values(array_filter(array_keys($categories), fn ($key) => array_key_exists($key, $tabs))),
    ];

    $buildTabUrl = static function (string $key) use ($pickerMode) {
        return route('admin.drive.media.index', array_filter([
            'tab' => $key,
            'picker' => $pickerMode ? 1 : null,
            'q' => request('q'),
            'ext' => request('ext'),
            'mime' => request('mime'),
            'uploader' => request('uploader'),
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
            'size_min' => request('size_min'),
            'size_max' => request('size_max'),
            'sort' => request('sort'),
            'dir' => request('dir'),
        ]));
    };

    $folderIcons = [
        'recent' => '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="8" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l2.5 2.5" /></svg>',
        'important' => '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5a.75.75 0 0 1 1.04 0l2.12 2.05 2.92.42a.75.75 0 0 1 .42 1.28l-2.1 2.05.5 2.9a.75.75 0 0 1-1.1.79L12 12.97l-2.64 1.38a.75.75 0 0 1-1.1-.79l.5-2.9-2.1-2.05a.75.75 0 0 1 .42-1.28l2.92-.42 2.12-2.05Z" /></svg>',
        Media::CATEGORY_DOCUMENTS => '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h6.25L17 7.5v12.75H7a1.25 1.25 0 0 1-1.25-1.25V5a1.25 1.25 0 0 1 1.25-1.25Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M13 3.5v4h4" /></svg>',
        Media::CATEGORY_MEDIA_PRODUCTS => '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="m3 16 4.5-4.5a1.5 1.5 0 0 1 2.12 0L17 19" /><circle cx="16" cy="9" r="1.5" /></svg>',
        Media::CATEGORY_MEDIA_CATALOGS => '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5h16M4 12h16M4 17.5h16" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 5v14" /></svg>',
        Media::CATEGORY_PAGES => '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="5" width="16" height="14" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" d="M4 10h16M9 5v14" /></svg>',
        'default' => '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7a2.25 2.25 0 0 1 2.25-2.25h4.3a2.25 2.25 0 0 1 1.59.66l1.12 1.09a2.25 2.25 0 0 0 1.58.65h4.17A2.25 2.25 0 0 1 21.75 9v8.25A2.25 2.25 0 0 1 19.5 19.5H4.5a2.25 2.25 0 0 1-2.25-2.25Z" /></svg>',
    ];
@endphp

<div
    class="drive"
    data-drive-root
    data-drive-total="{{ $mediaItems->total() }}"
    data-drive-page-size="{{ $mediaItems->perPage() }}"
    data-drive-search-url="{{ route('admin.drive.media.index') }}"
    data-drive-active-tab="{{ $tab }}"
    data-category-default="{{ $activeCategory }}"
    data-category-limits='@json($categoryLimits)'
    data-picker-mode="{{ $pickerMode ? '1' : '0' }}"
    data-upload-url="{{ route('admin.drive.media.store') }}"
    data-upload-many-url="{{ route('admin.drive.media.store_many') }}"
    data-replace-url-template="{{ route('admin.drive.media.replace', ['media' => '__ID__']) }}"
    data-toggle-important-template="{{ route('admin.drive.media.toggle_important', ['media' => '__ID__']) }}"
>
    <x-ui-page-header
        title="Drive"
        description="Dosyalarınızı modern arayüzle yönetin, arayın ve düzenleyin."
    >
        @if(! $pickerMode)
            <x-slot name="actions">
                <x-ui-button variant="primary" data-action="drive-open-upload">
                    <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
                    <span>Dosya Yükle</span>
                </x-ui-button>
            </x-slot>
        @endif
    </x-ui-page-header>

    @if(session('status'))
        <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
    @endif

    @if($errors->any())
        <x-ui-alert type="danger" dismissible>{{ $errors->first() }}</x-ui-alert>
    @endif

    <div class="drive__layout">
        <aside class="drive__sidebar" data-drive-tree>
            <div class="drive-tree">
                <div class="drive-tree__header">
                    <h2 class="drive-tree__title">Klasör ağacı</h2>
                    <p class="drive-tree__description">Sık kullanılanlar ve kategoriler burada.</p>
                </div>
                <ul class="drive-tree__groups">
                    @foreach($folderGroups as $groupTitle => $keys)
                        @if(count($keys))
                            @php
                                $groupId = 'drive-tree-' . Str::slug($groupTitle) . '-' . $loop->index;
                            @endphp
                            <li class="drive-tree__group" data-drive-tree-item>
                                <button
                                    type="button"
                                    class="drive-tree__toggle"
                                    data-drive-tree-toggle
                                    aria-expanded="true"
                                    aria-controls="{{ $groupId }}"
                                >
                                    <span class="drive-tree__toggle-label">{{ $groupTitle }}</span>
                                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                </button>
                                <ul class="drive-tree__panel" id="{{ $groupId }}" data-drive-tree-panel role="group" aria-hidden="false">
                                    @foreach($keys as $key)
                                        @php
                                            $isActive = $tab === $key;
                                            $folderStat = $stats[$key] ?? ['total' => 0];
                                            $importantCount = $folderStat['important'] ?? null;
                                            $icon = $folderIcons[$key] ?? $folderIcons['default'];
                                            $itemUrl = $buildTabUrl($key);
                                        @endphp
                                        <li class="drive-tree__item">
                                            <a
                                                href="{{ $itemUrl }}"
                                                class="drive-tree__link {{ $isActive ? 'is-active' : '' }}"
                                                data-drive-folder-link
                                                data-drive-folder="{{ $key }}"
                                                aria-current="{{ $isActive ? 'page' : 'false' }}"
                                            >
                                                <span class="drive-tree__icon">{!! $icon !!}</span>
                                                <span class="drive-tree__info">
                                                    <span class="drive-tree__name">{{ $tabs[$key] ?? ucfirst($key) }}</span>
                                                    <span class="drive-tree__stats">
                                                        <span class="drive-tree__count">{{ number_format((int) ($folderStat['total'] ?? 0)) }}</span>
                                                        @if(! is_null($importantCount) && $importantCount > 0)
                                                            <span class="drive-tree__badge" aria-label="Önemli dosya sayısı">
                                                                <i class="bi bi-star-fill" aria-hidden="true"></i>
                                                                {{ number_format($importantCount) }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </aside>

        <section class="drive__content">
            <div class="drive-toolbar">
                <form class="drive-toolbar__form" data-drive-search-form method="GET" action="{{ route('admin.drive.media.index') }}">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    @if($pickerMode)
                        <input type="hidden" name="picker" value="1">
                    @endif
                    <x-ui-input
                        type="search"
                        name="q"
                        :value="request('q')"
                        label="Dosya ara"
                        placeholder="Dosya adı, uzantı veya etiket"
                        data-drive-search-input
                    />
                    <x-ui-button type="submit" variant="secondary" data-drive-search-submit>Ara</x-ui-button>
                </form>
                @if(! $pickerMode)
                    <x-ui-button variant="primary" data-action="drive-open-upload" class="drive-toolbar__upload">
                        <i class="bi bi-upload" aria-hidden="true"></i>
                        <span>Dosya Yükle</span>
                    </x-ui-button>
                @endif
            </div>

            <div class="drive-summary">
                <span class="drive-summary__item">
                    <strong>{{ number_format($mediaItems->total()) }}</strong>
                    <span>dosya</span>
                </span>
                <span class="drive-summary__item">
                    <strong>{{ $tabs[$tab] ?? 'Klasör' }}</strong>
                    <span>aktif sekme</span>
                </span>
            </div>

            <div class="drive-collection">
                <div class="drive-collection__grid" data-drive-grid>
                    @if($mediaItems->count())
                        @foreach($mediaItems as $media)
                            @php
                                $searchIndex = Str::lower($media->original_name . ' ' . $media->mime . ' ' . $media->ext);
                                $uploaderName = $media->uploader?->name ?? 'Sistem';
                            @endphp
                            <x-ui-card
                                class="drive-file {{ $media->is_important ? 'drive-file--important' : '' }}"
                                data-drive-row
                                data-id="{{ $media->id }}"
                                data-search="{{ $searchIndex }}"
                                data-name="{{ Str::lower($media->original_name) }}"
                                data-ext="{{ Str::lower($media->ext) }}"
                                data-mime="{{ Str::lower($media->mime) }}"
                                data-important="{{ $media->is_important ? '1' : '0' }}"
                            >
                                <div class="drive-file__header">
                                    <span class="drive-file__icon">
                                        <x-ui-file-icon :ext="$media->ext" size="36" />
                                    </span>
                                    <div class="drive-file__meta">
                                        <h3 class="drive-file__name" title="{{ $media->original_name }}">{{ $media->original_name }}</h3>
                                        <p class="drive-file__detail">{{ strtoupper($media->ext) }} · {{ $media->mime }} · {{ $formatSize($media->size) }}</p>
                                        <p class="drive-file__detail drive-file__detail--muted">
                                            {{ $uploaderName }} · {{ $media->created_at?->diffForHumans() }}
                                        </p>
                                    </div>
                                    @if(! $pickerMode)
                                        <x-ui-badge
                                            type="warning"
                                            tone="soft"
                                            data-drive-important-badge
                                            :hidden="! $media->is_important"
                                        >
                                            Önemli
                                        </x-ui-badge>
                                    @endif
                                </div>

                                @if(! $pickerMode)
                                    <div class="drive-file__actions">
                                        @can('view', $media)
                                            <x-ui-button
                                                tag="a"
                                                href="{{ route('admin.drive.media.download', $media) }}"
                                                variant="ghost"
                                                size="sm"
                                            >İndir</x-ui-button>
                                        @endcan

                                        @can('replace', $media)
                                            <x-ui-button
                                                variant="ghost"
                                                size="sm"
                                                data-action="drive-open-replace"
                                                data-id="{{ $media->id }}"
                                                data-name="{{ $media->original_name }}"
                                            >Değiştir</x-ui-button>
                                        @endcan

                                        @can('markImportant', $media)
                                            <x-ui-button
                                                variant="outline-secondary"
                                                size="sm"
                                                class="drive-file__important {{ $media->is_important ? 'is-on' : '' }}"
                                                data-action="drive-toggle-important"
                                                data-url="{{ route('admin.drive.media.toggle_important', $media) }}"
                                                aria-pressed="{{ $media->is_important ? 'true' : 'false' }}"
                                            >Önemli</x-ui-button>
                                        @endcan
                                    </div>
                                @else
                                    <div class="drive-file__actions">
                                        <x-ui-button
                                            variant="primary"
                                            size="sm"
                                            data-action="drive-picker-select"
                                            data-id="{{ $media->id }}"
                                            data-name="{{ $media->original_name }}"
                                            data-ext="{{ $media->ext }}"
                                            data-mime="{{ $media->mime }}"
                                            data-size="{{ $media->size }}"
                                        >Seç</x-ui-button>
                                    </div>
                                @endif
                            </x-ui-card>
                        @endforeach
                    @else
                        <x-ui-card class="drive-file drive-file--empty">
                            <x-ui-empty
                                icon="folder"
                                title="Henüz dosya yok"
                                description="İlk dosyanızı yükleyerek klasörü doldurun."
                            />
                        </x-ui-card>
                    @endif
                </div>

                <div class="drive-collection__empty" data-drive-empty hidden>
                    <x-ui-empty
                        icon="search"
                        title="Sonuç bulunamadı"
                        description="Arama kriterinizi güncelleyerek yeniden deneyin."
                    />
                </div>
            </div>

            @if($mediaItems->hasPages())
                <div class="drive__pagination" data-drive-pagination>
                    <x-ui-pagination :paginator="$mediaItems" />
                </div>
            @endif

            @if(! $pickerMode)
                <x-ui-card class="drive-upload" data-drive-upload-panel hidden>
                    <div class="drive-upload__header">
                        <div>
                            <h2 class="drive-upload__title">Dosya yükle</h2>
                            <p class="drive-upload__description">Dosyaları sürükleyip bırakın veya bilgisayarınızdan seçin. Aynı anda en fazla 10 dosya yüklenebilir.</p>
                        </div>
                        <div class="drive-upload__controls">
                            <x-ui-select
                                name="upload_category"
                                label="Kategori"
                                class="mb-0"
                                data-drive-category-select
                                :options="$categories"
                                :value="$activeCategory"
                            />
                            <x-ui-button variant="ghost" data-action="drive-close-upload">Kapat</x-ui-button>
                        </div>
                    </div>
                    <div class="drive-upload__drop" data-drive-dropzone tabindex="0" role="button">
                        <i class="bi bi-cloud-arrow-up drive-upload__icon" aria-hidden="true"></i>
                        <p class="drive-upload__lead">Dosyalarınızı buraya bırakın</p>
                        <p class="drive-upload__hint">veya</p>
                        <label class="drive-upload__trigger">
                            Dosya Seç
                            <input type="file" name="files[]" multiple class="visually-hidden" data-drive-file-input>
                        </label>
                        <p class="drive-upload__note" data-drive-category-note>
                            @if(($categoryLimits[$activeCategory] ?? null) !== null)
                                Kabul edilen uzantılar: {{ $categoryLimits[$activeCategory]['mimes'] }} · Maks {{ $categoryLimits[$activeCategory]['max'] }}
                            @endif
                        </p>
                    </div>
                    <div class="drive-upload__progress" data-drive-progress hidden>
                        <div class="drive-upload__progress-head">
                            <span class="drive-upload__progress-title">Yükleme durumu</span>
                            <button type="button" class="drive-upload__progress-clear" data-action="drive-clear-progress">Temizle</button>
                        </div>
                        <div class="drive-upload__progress-items" data-drive-progress-items></div>
                    </div>
                </x-ui-card>
            @endif
        </section>
    </div>
</div>

@if(! $pickerMode)
    <x-ui-modal id="driveReplaceModal" :title="'Dosyayı değiştir'">
        <div class="drive-replace" data-drive-replace-modal>
            <p class="drive-replace__summary" data-drive-replace-name></p>
            <x-ui-input
                type="file"
                name="drive_replace"
                id="drive-replace-input"
                label="Yeni dosya"
                data-drive-replace-input
            />
            <div class="drive-replace__error" data-drive-replace-error hidden></div>
        </div>
        <x-slot name="footer">
            <x-ui-button variant="ghost" data-action="close">Vazgeç</x-ui-button>
            <x-ui-button variant="primary" data-action="drive-submit-replace">Değiştir</x-ui-button>
        </x-slot>
    </x-ui-modal>
@endif
@endsection
