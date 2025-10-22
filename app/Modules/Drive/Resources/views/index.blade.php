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

    $activeStats = $stats[$tab] ?? ['total' => $mediaItems->total()];

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

<div class="drive" data-drive-root
     data-drive-mode="{{ $initialView === 'list' ? 'list' : 'grid' }}"
     data-drive-view-preference="{{ $initialView }}"
     data-upload-url="{{ route('admin.drive.media.store') }}"
     data-upload-many-url="{{ route('admin.drive.media.store_many') }}"
     data-replace-url-template="{{ route('admin.drive.media.replace', ['media' => '__ID__']) }}"
     data-toggle-important-template="{{ route('admin.drive.media.toggle_important', ['media' => '__ID__']) }}"
     data-category-default="{{ $activeCategory }}"
     data-category-limits='@json($categoryLimits)'
     data-picker-mode="{{ $pickerMode ? '1' : '0' }}'>
    <x-ui-page-header
        title="Drive"
        description="Dosyalarınızı modern arayüzle yönetin, arayın ve düzenleyin">
        @if(! $pickerMode)
            <x-slot name="actions">
                @can('create', Media::class)
                    <x-ui-button variant="primary" data-action="drive-open-upload">
                        <svg aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="me-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Dosya Yükle
                    </x-ui-button>
                @endcan
            </x-slot>
        @endif
    </x-ui-page-header>

    @if(session('status'))
        <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
    @endif

    @if($errors->any())
        <x-ui-alert type="danger" dismissible>
            {{ $errors->first() }}
        </x-ui-alert>
    @endif

    <div class="drive__body">
        <aside class="drive__sidebar">
            <div class="drive-sidebar">
                <div class="drive-sidebar__header">
                    <h2 class="drive-sidebar__title">Klasörler</h2>
                    <p class="drive-sidebar__subtitle">Tüm sekmeler ve kategoriler burada listelenir.</p>
                </div>

                <div class="drive-folders">
                    @foreach($folderGroups as $groupTitle => $keys)
                        @if(count($keys))
                            <section class="drive-folders__group">
                                <h3 class="drive-folders__title">{{ $groupTitle }}</h3>
                                <ul class="drive-folders__list">
                                    @foreach($keys as $key)
                                        @php
                                            $isActive = $tab === $key;
                                            $folderStat = $stats[$key] ?? ['total' => 0];
                                            $importantCount = $folderStat['important'] ?? null;
                                            $icon = $folderIcons[$key] ?? $folderIcons['default'];
                                        @endphp
                                        <li>
                                            <a class="drive-folder {{ $isActive ? 'is-active' : '' }}"
                                               href="{{ $buildTabUrl($key) }}"
                                               aria-current="{{ $isActive ? 'page' : 'false' }}">
                                                <span class="drive-folder__icon">{!! $icon !!}</span>
                                                <span class="drive-folder__labels">
                                                    <span class="drive-folder__name">{{ $tabs[$key] ?? ucfirst($key) }}</span>
                                                    <span class="drive-folder__meta">
                                                        <span class="drive-folder__count">{{ number_format((int) ($folderStat['total'] ?? 0)) }}</span>
                                                        @if(! is_null($importantCount) && $importantCount > 0)
                                                            <span class="drive-folder__important">★ {{ number_format($importantCount) }}</span>
                                                        @endif
                                                    </span>
                                                </span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endif
                    @endforeach
                </div>

                <div class="drive-sidebar__note">
                    @if($pickerMode)
                        <strong>Seçici modu:</strong> Sadece “Ürün Görselleri” kategorisinden seçim yapabilirsiniz.
                    @else
                        Dosyaları yıldızlayarak "Önemli" sekmesinde hızlı erişim sağlayın.
                    @endif
                </div>
            </div>
        </aside>

        <div class="drive__main">

            <x-ui-card class="drive-panel">
                <form method="GET" action="{{ route('admin.drive.media.index') }}" class="drive-panel__form" data-drive-filter-form>
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    @if($pickerMode)
                        <input type="hidden" name="picker" value="1">
                    @endif

                    <div class="drive-panel__toolbar">
                        <div class="drive-panel__right">
                            <div class="drive-panel__search">
                                <x-ui-input name="q" :value="request('q')" label="Ara" placeholder="Dosya adı, tür veya etiket" />
                            </div>
                        </div>
                    </div>

                    <div class="drive-panel__advanced" data-drive-filters hidden>
                        <div class="drive-panel__advanced-grid">
                            <x-ui-input name="ext" :value="request('ext')" label="Uzantı" placeholder="pdf" />
                            <x-ui-input name="mime" :value="request('mime')" label="MIME" placeholder="image/png" />
                            <x-ui-input name="uploader" :value="request('uploader')" label="Yükleyen ID" placeholder="1" />
                            <x-ui-input type="date" name="date_from" :value="request('date_from')" label="Başlangıç" />
                            <x-ui-input type="date" name="date_to" :value="request('date_to')" label="Bitiş" />
                            <x-ui-input type="number" name="size_min" :value="request('size_min')" label="Min. Boyut (MB)" min="0" step="1" />
                            <x-ui-input type="number" name="size_max" :value="request('size_max')" label="Maks. Boyut (MB)" min="0" step="1" />
                        </div>
                    </div>
                </form>
            </x-ui-card>

            @if(! $pickerMode)
                <x-ui-card class="drive-upload" id="drive-upload-panel" data-drive-upload-panel hidden>
                    <div class="drive-upload__header">
                        <div>
                            <h2 class="drive-upload__title">Dosya yükle</h2>
                            <p class="drive-upload__description">Dosyaları sürükleyip bırakın veya bilgisayarınızdan seçin. Aynı anda en fazla 10 dosya yüklenebilir.</p>
                        </div>
                        <div class="drive-upload__controls">
                            <x-ui-select name="upload_category" label="Kategori" class="mb-0" data-drive-category-select :options="$categories" :value="$activeCategory" />
                            <x-ui-button variant="ghost" data-action="drive-close-upload" aria-label="Yükleme panelini kapat">Kapat</x-ui-button>
                        </div>
                    </div>
                    <div class="drive-upload__drop" tabindex="0" role="button" data-drive-dropzone>
                        <svg class="drive-upload__illustration" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0 4 4m-4-4-4 4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 16.5a4.5 4.5 0 0 0-3.91-4.455A5 5 0 0 0 6.5 9.5a5.002 5.002 0 0 0-4.5 4.978" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16" />
                        </svg>
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

            <div class="drive-results">
                @if($mediaItems->count())
                    <div class="drive-view drive-view--grid" data-drive-view="grid">
                        <div class="drive-grid">
                            @foreach($mediaItems as $media)
                                <article class="drive-card" data-drive-row data-id="{{ $media->id }}" data-important="{{ $media->is_important ? '1' : '0' }}">
                                    <header class="drive-card__header">
                                        <span class="drive-card__icon"><x-ui-file-icon :ext="$media->ext" size="36" /></span>
                                        <div class="drive-card__meta">
                                            <h3 class="drive-card__title" title="{{ $media->original_name }}">{{ $media->original_name }}</h3>
                                            <p class="drive-card__subtitle">{{ $media->mime }} · {{ $formatSize($media->size) }}</p>
                                        </div>
                                        @if(! $pickerMode)
                                            <x-ui-badge
                                                type="warning"
                                                tone="soft"
                                                data-drive-important-badge
                                                :hidden="! $media->is_important"
                                            >★</x-ui-badge>
                                        @endif
                                    </header>
                                    <div class="drive-card__body">
                                        <dl class="drive-card__details">
                                            <div>
                                                <dt>Uzantı</dt>
                                                <dd>{{ strtoupper($media->ext) }}</dd>
                                            </div>
                                            <div>
                                                <dt>Yükleyen</dt>
                                                <dd>
                                                    @if($media->uploader)
                                                        <span title="{{ $media->uploader->name }}">{{ $media->uploader->name }}</span>
                                                    @else
                                                        <span class="text-muted">Sistem</span>
                                                    @endif
                                                </dd>
                                            </div>
                                            <div>
                                                <dt>Tarih</dt>
                                                <dd>{{ $media->created_at?->diffForHumans() }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                    <footer class="drive-card__actions">
                                        @if($pickerMode)
                                            <button type="button" class="drive-action-button" data-action="drive-picker-select"
                                                    data-id="{{ $media->id }}" data-name="{{ $media->original_name }}" data-ext="{{ $media->ext }}"
                                                    data-mime="{{ $media->mime }}" data-size="{{ $media->size }}">
                                                Seç
                                            </button>
                                        @else
                                            @can('markImportant', $media)
                                                <button
                                                    type="button"
                                                    class="drive-action-button drive-action-button--star {{ $media->is_important ? 'is-on' : '' }}"
                                                    data-action="drive-toggle-important"
                                                    data-url="{{ route('admin.drive.media.toggle_important', $media) }}"
                                                    aria-pressed="{{ $media->is_important ? 'true' : 'false' }}"
                                                    title="Önemli işaretle" aria-label="Önemli işaretle">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.75.75 0 0 1 1.04 0l2.122 2.05 2.918.424a.75.75 0 0 1 .415 1.279l-2.11 2.057.498 2.9a.75.75 0 0 1-1.088.791L12 12.973l-2.634 1.387a.75.75 0 0 1-1.088-.79l.498-2.9-2.11-2.058a.75.75 0 0 1 .415-1.279l2.918-.424 2.122-2.05Z" />
                                                    </svg>
                                                </button>
                                            @endcan
                                            @can('view', $media)
                                                <a class="drive-action-button" href="{{ route('admin.drive.media.download', $media) }}">İndir</a>
                                            @endcan
                                            @can('replace', $media)
                                                <button type="button" class="drive-action-button" data-action="drive-open-replace"
                                                        data-id="{{ $media->id }}" data-name="{{ $media->original_name }}">
                                                    Değiştir
                                                </button>
                                            @endcan
                                            @can('delete', $media)
                                                <form method="POST" action="{{ route('admin.drive.media.destroy', $media) }}" class="drive-action-delete" onsubmit="return confirm('Dosyayı silmek istediğinize emin misiniz?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="tab" value="{{ $tab }}">
                                                    <x-ui-button type="submit" variant="danger" size="sm">Sil</x-ui-button>
                                                </form>
                                            @endcan
                                        @endif
                                    </footer>
                                </article>
                            @endforeach
                        </div>
                    </div>

                    <div class="drive-view drive-view--list" data-drive-view="list">
                        <x-ui-card class="drive-list">
                            <x-ui-table dense>
                                <thead>
                                    <tr>
                                        <th scope="col">Dosya</th>
                                        <th scope="col">Ad</th>
                                        <th scope="col">Boyut</th>
                                        <th scope="col">Tür</th>
                                        <th scope="col">Yükleyen</th>
                                        <th scope="col">Tarih</th>
                                        <th scope="col" class="text-end">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mediaItems as $media)
                                        <tr
                                            data-drive-row
                                            data-id="{{ $media->id }}"
                                            data-important="{{ $media->is_important ? '1' : '0' }}"
                                            @class(['drive-row', 'is-important' => $media->is_important])
                                        >
                                            <td>
                                                <x-ui-file-icon :ext="$media->ext" size="28" class="me-2" />
                                            </td>
                                            <td>
                                                <div class="drive-file">
                                                    <span class="drive-file__name" title="{{ $media->original_name }}">{{ $media->original_name }}</span>
                                                    <x-ui-badge
                                                        type="warning"
                                                        tone="soft"
                                                        data-drive-important-badge
                                                        :hidden="! $media->is_important"
                                                    >★</x-ui-badge>
                                                </div>
                                                <div class="drive-file__meta">{{ $media->ext }}</div>
                                            </td>
                                            <td>{{ $formatSize($media->size) }}</td>
                                            <td>{{ $media->mime }}</td>
                                            <td>
                                                @if($media->uploader)
                                                    <span class="text-ellipsis" title="{{ $media->uploader->name }}">{{ $media->uploader->name }}</span>
                                                @else
                                                    <span class="text-muted">Sistem</span>
                                                @endif
                                            </td>
                                            <td>{{ $media->created_at?->diffForHumans() }}</td>
                                            <td class="text-end">
                                                <div class="drive-actions">
                                                    @if($pickerMode)
                                                        <button type="button" class="drive-action-button" data-action="drive-picker-select"
                                                                data-id="{{ $media->id }}" data-name="{{ $media->original_name }}" data-ext="{{ $media->ext }}"
                                                                data-mime="{{ $media->mime }}" data-size="{{ $media->size }}">
                                                            Seç
                                                        </button>
                                                    @else
                                                        @can('markImportant', $media)
                                                            <button
                                                                type="button"
                                                                class="drive-action-button drive-action-button--star {{ $media->is_important ? 'is-on' : '' }}"
                                                                data-action="drive-toggle-important"
                                                                data-url="{{ route('admin.drive.media.toggle_important', $media) }}"
                                                                aria-pressed="{{ $media->is_important ? 'true' : 'false' }}"
                                                                title="Önemli işaretle" aria-label="Önemli işaretle">
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.75.75 0 0 1 1.04 0l2.122 2.05 2.918.424a.75.75 0 0 1 .415 1.279l-2.11 2.057.498 2.9a.75.75 0 0 1-1.088.791L12 12.973l-2.634 1.387a.75.75 0 0 1-1.088-.79l.498-2.9-2.11-2.058a.75.75 0 0 1 .415-1.279l2.918-.424 2.122-2.05Z" />
                                                                </svg>
                                                            </button>
                                                        @endcan
                                                        @can('view', $media)
                                                            <a class="drive-action-button" href="{{ route('admin.drive.media.download', $media) }}">İndir</a>
                                                        @endcan
                                                        @can('replace', $media)
                                                            <button type="button" class="drive-action-button" data-action="drive-open-replace"
                                                                    data-id="{{ $media->id }}" data-name="{{ $media->original_name }}">
                                                                Değiştir
                                                            </button>
                                                        @endcan
                                                        @can('delete', $media)
                                                            <form method="POST" action="{{ route('admin.drive.media.destroy', $media) }}" class="drive-action-delete" onsubmit="return confirm('Dosyayı silmek istediğinize emin misiniz?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="tab" value="{{ $tab }}">
                                                                <x-ui-button type="submit" variant="danger" size="sm">Sil</x-ui-button>
                                                            </form>
                                                        @endcan
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </x-ui-table>
                        </x-ui-card>
                    </div>
                @else
                    <x-ui-empty icon="folder" title="Henüz dosya yok" description="İlk dosyanızı yüklediğinizde burada görünecek." />
                @endif
            </div>

            <div class="drive__pagination">
                <x-ui-pagination :paginator="$mediaItems" />
            </div>
        </div>
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
