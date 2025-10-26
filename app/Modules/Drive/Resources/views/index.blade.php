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
        use App\Modules\Drive\Support\DriveStructure;
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

        $moduleLabels = Media::moduleOptions();
        $moduleOptions = collect($moduleLabels)
            ->map(fn($label, $slug) => ['value' => $slug, 'label' => $label])
            ->values()
            ->all();
        $folderDefinitions = collect($folderConfig ?? [])->mapWithKeys(fn($config) => [$config['key'] => $config]);
        $folderIconMap = $folderDefinitions->mapWithKeys(function ($config, $key) {
            return [
                $key => ($config['type'] ?? Media::TYPE_DOCUMENT) === Media::TYPE_MEDIA ? 'bi bi-image' : 'bi bi-file-earmark-text',
            ];
        })->all();
        $defaultModule = DriveStructure::defaultModule();
        $defaultFolder = DriveStructure::defaultFolder($defaultModule);
        $activeModule = $defaultModule;
        $activeFolder = $defaultFolder;

        if (Str::startsWith($tab, 'module_')) {
            $rest = Str::after($tab, 'module_');
            if (Str::contains($rest, '__')) {
                [$moduleSlug, $folderKey] = explode('__', $rest, 2);
                if (array_key_exists($moduleSlug, $moduleLabels)) {
                    $activeModule = $moduleSlug;
                }
                if ($folderDefinitions->has($folderKey)) {
                    $activeFolder = $folderKey;
                }
            } elseif (array_key_exists($rest, $moduleLabels)) {
                $activeModule = $rest;
                $activeFolder = DriveStructure::defaultFolder($activeModule);
            }
        } elseif (Str::startsWith($tab, 'folder_')) {
            $maybeFolder = Str::after($tab, 'folder_');
            if ($folderDefinitions->has($maybeFolder)) {
                $activeFolder = $maybeFolder;
            }
        } elseif (in_array($tab, ['recent_documents', 'important_documents'], true)) {
            $activeFolder = DriveStructure::normalizeFolderKey('documents', $activeModule);
        } elseif (in_array($tab, ['recent_media', 'important_media'], true)) {
            $activeFolder = DriveStructure::normalizeFolderKey('media', $activeModule);
        }

        // Backwards compatibility for templates that still reference the older variable name.
        $activeCategory = $activeFolder;

        $categoryLimits = $folderDefinitions->mapWithKeys(
            fn($config, $key) => [
                $key => [
                    'mimes' => implode(', ', $config['ext'] ?? []),
                    'max' => $formatSize(min((int) ($config['max'] ?? $globalMaxBytes), $globalMaxBytes)),
                ],
            ],
        );

        $totalCount = $mediaItems->total();
        $activeTabLabel = $tabs[$tab] ?? 'Tümü';

        $moduleIcons = [
            Media::MODULE_CMS => 'bi bi-layout-text-window',
            Media::MODULE_MARKETING => 'bi bi-megaphone',
            Media::MODULE_FINANCE => 'bi bi-cash-stack',
            Media::MODULE_LOGISTICS => 'bi bi-truck',
            Media::MODULE_INVENTORY => 'bi bi-boxes',
            Media::MODULE_PRODUCTION => 'bi bi-gear-wide-connected',
            Media::MODULE_HR => 'bi bi-people',
        ];

        $quickGroups = [
            [
                'title' => 'Son Yüklenenler',
                'items' => [
                    ['key' => 'recent_documents', 'label' => 'Belgeler', 'icon' => 'bi bi-file-earmark-text'],
                    ['key' => 'recent_media', 'label' => 'Medya', 'icon' => 'bi bi-image'],
                ],
            ],
            [
                'title' => 'Önemliler',
                'items' => [
                    ['key' => 'important_documents', 'label' => 'Belgeler', 'icon' => 'bi bi-star-fill'],
                    ['key' => 'important_media', 'label' => 'Medya', 'icon' => 'bi bi-star-fill'],
                ],
            ],
        ];
        $quickGroups = collect($quickGroups)
            ->map(function ($group) use ($tabs) {
                $items = collect($group['items'] ?? [])
                    ->filter(fn($item) => array_key_exists($item['key'], $tabs))
                    ->values()
                    ->all();

                return array_merge($group, ['items' => $items]);
            })
            ->filter(fn($group) => count($group['items']) > 0)
            ->values()
            ->all();

        $moduleNavigation = collect($moduleNavigation ?? [])->map(function ($module) use ($moduleIcons) {
            $folders = collect($module['folders'] ?? [])->map(function ($folder) use ($module) {
                return [
                    'key' => 'module_' . $module['module'] . '__' . $folder['key'],
                    'folder' => $folder['key'],
                    'label' => $folder['label'],
                ];
            })->values()->all();

            return array_merge($module, [
                'key' => 'module_' . $module['module'],
                'icon' => $moduleIcons[$module['module']] ?? 'bi bi-folder',
                'folders' => $folders,
            ]);
        })->values();

        $buildTabUrl = static function (string $key) use ($pickerMode) {
            return route(
                'admin.drive.media.index',
                array_filter([
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
                ]),
            );
        };

        $folderIcons = [
            'recent_documents' => 'bi bi-file-earmark-text',
            'recent_media' => 'bi bi-image',
            'important_documents' => 'bi bi-star-fill',
            'important_media' => 'bi bi-star-fill',
            'default' => 'bi bi-folder',
        ];

        $storage = $storage ?? ['limit' => 0, 'used' => 0, 'remaining' => 0, 'percentage' => 0];
        $storageLimit = (int) ($storage['limit'] ?? 0);
        $storageUsed = (int) ($storage['used'] ?? 0);
        $storageRemaining = max($storageLimit - $storageUsed, 0);
        $storagePercent =
            $storageLimit > 0
                ? min(100, max(0, (float) ($storage['percentage'] ?? ($storageUsed / max($storageLimit, 1)) * 100)))
                : 0;

    @endphp

    <div class="drive" 
        data-drive-root data-drive-total="{{ $totalCount }}"
        data-drive-page-size="{{ $mediaItems->perPage() }}" data-drive-search-url="{{ route('admin.drive.media.index') }}"
        data-drive-active-tab="{{ $tab }}" data-category-default="{{ $activeFolder }}"
        data-category-active="{{ $activeFolder }}"
        data-module-default="{{ $defaultModule }}" data-module-active="{{ $activeModule }}"
        data-category-limits='@json($categoryLimits)' data-picker-mode="{{ $pickerMode ? '1' : '0' }}"
        data-upload-url="{{ route('admin.drive.media.store') }}"
        data-upload-many-url="{{ route('admin.drive.media.store_many') }}"
        data-replace-url-template="{{ route('admin.drive.media.replace', ['media' => '__ID__']) }}"
        data-toggle-important-template="{{ route('admin.drive.media.toggle_important', ['media' => '__ID__']) }}"
        data-download-url-template="{{ route('admin.drive.media.download', ['media' => '__ID__']) }}"
        data-delete-url-template="{{ route('admin.drive.media.destroy', ['media' => '__ID__']) }}"
        data-drive-storage-limit="{{ $storageLimit }}"
        data-drive-storage-used="{{ $storageUsed }}"
    >


        @if (session('status'))
            <x-ui-alert type="success" dismissible>{{ session('status') }}</x-ui-alert>
        @endif

        @if ($errors->any())
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
                        @foreach ($quickGroups as $groupIndex => $group)
                            @php
                                $groupTitle = $group['title'] ?? 'Kısayollar';
                                $items = $group['items'] ?? [];
                                $groupId = 'drive-tree-quick-' . $groupIndex;
                            @endphp
                            @if (count($items))
                                <li class="drive-tree__group" data-drive-tree-item>
                                    <button class="drive-tree__toggle" type="button" aria-controls="{{ $groupId }}" aria-expanded="true" data-drive-tree-toggle>
                                        <span class="drive-tree__toggle-label">{{ $groupTitle }}</span>
                                        <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                    </button>
                                    <ul class="drive-tree__panel" id="{{ $groupId }}" data-drive-tree-panel role="group" aria-hidden="false">
                                        @foreach ($items as $item)
                                            @php
                                                $key = $item['key'];
                                                $label = $item['label'] ?? ($tabs[$key] ?? ucfirst($key));
                                                $iconClass = $item['icon'] ?? ($folderIcons[$key] ?? $folderIcons['default']);
                                                $isActive = $tab === $key;
                                                $folderStat = $stats[$key] ?? ['total' => 0];
                                                $importantCount = $folderStat['important'] ?? null;
                                                $itemUrl = $buildTabUrl($key);
                                            @endphp
                                            <li class="drive-tree__item">
                                                <a href="{{ $itemUrl }}" class="drive-tree__link {{ $isActive ? 'is-active' : '' }}" data-drive-folder-link data-drive-folder="{{ $key }}" aria-current="{{ $isActive ? 'page' : 'false' }}">
                                                    <span class="drive-tree__icon">
                                                        <i class="{{ $iconClass }}" aria-hidden="true"></i>
                                                    </span>
                                                    <span class="drive-tree__info">
                                                        <span class="drive-tree__name">{{ $label }}</span>
                                                        <span class="drive-tree__stats">
                                                            <span class="drive-tree__count">{{ number_format((int) ($folderStat['total'] ?? 0)) }}</span>
                                                            @if (!is_null($importantCount) && $importantCount > 0)
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

                        @if ($moduleNavigation->isNotEmpty())
                            @php
                                $modulesGroupId = 'drive-tree-modules';
                            @endphp
                            <li class="drive-tree__group" data-drive-tree-item>
                                <button class="drive-tree__toggle" type="button" aria-controls="{{ $modulesGroupId }}" aria-expanded="true" data-drive-tree-toggle>
                                    <span class="drive-tree__toggle-label">Modüller</span>
                                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                </button>
                                <ul class="drive-tree__panel" id="{{ $modulesGroupId }}" data-drive-tree-panel role="group" aria-hidden="false">
                                    @foreach ($moduleNavigation as $moduleIndex => $module)
                                        @php
                                            $modulePanelId = 'drive-tree-module-' . Str::slug($module['module']) . '-' . $moduleIndex;
                                            $moduleActive = Str::startsWith($tab, 'module_' . $module['module']);
                                            $moduleKey = $module['key'];
                                            $moduleStats = $stats[$moduleKey] ?? ['total' => 0];
                                            $moduleImportant = $moduleStats['important'] ?? null;
                                            $moduleUrl = $buildTabUrl($moduleKey);
                                            $moduleLinkActive = $tab === $moduleKey;
                                        @endphp
                                        <li class="drive-tree__item drive-tree__item--module">
                                            <button class="drive-tree__toggle drive-tree__toggle--module" type="button" aria-controls="{{ $modulePanelId }}" aria-expanded="{{ $moduleActive ? 'true' : 'false' }}" data-drive-tree-toggle>
                                                <span class="drive-tree__icon">
                                                    <i class="{{ $module['icon'] }}" aria-hidden="true"></i>
                                                </span>
                                                <span class="drive-tree__info">
                                                    <span class="drive-tree__name">{{ $module['label'] }}</span>
                                                    <span class="drive-tree__stats">
                                                        <span class="drive-tree__count">{{ number_format((int) ($moduleStats['total'] ?? 0)) }}</span>
                                                        @if (!is_null($moduleImportant) && $moduleImportant > 0)
                                                            <span class="drive-tree__badge" aria-label="Önemli dosya sayısı">
                                                                <i class="bi bi-star-fill" aria-hidden="true"></i>
                                                                {{ number_format($moduleImportant) }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                </span>
                                                <i class="bi bi-chevron-down" aria-hidden="true"></i>
                                            </button>
                                            <ul class="drive-tree__panel drive-tree__panel--module" id="{{ $modulePanelId }}" data-drive-tree-panel role="group" @if (!$moduleActive) hidden aria-hidden="true" @else aria-hidden="false" @endif>
                                                <li class="drive-tree__item">
                                                    <a href="{{ $moduleUrl }}" class="drive-tree__link {{ $moduleLinkActive ? 'is-active' : '' }}" data-drive-folder-link data-drive-folder="{{ $moduleKey }}" aria-current="{{ $moduleLinkActive ? 'page' : 'false' }}">
                                                        <span class="drive-tree__icon">
                                                            <i class="{{ $module['icon'] }}" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="drive-tree__info">
                                                            <span class="drive-tree__name">{{ $module['label'] }} · Tümü</span>
                                                            <span class="drive-tree__stats">
                                                                <span class="drive-tree__count">{{ number_format((int) ($moduleStats['total'] ?? 0)) }}</span>
                                                                @if (!is_null($moduleImportant) && $moduleImportant > 0)
                                                                    <span class="drive-tree__badge" aria-label="Önemli dosya sayısı">
                                                                        <i class="bi bi-star-fill" aria-hidden="true"></i>
                                                                        {{ number_format($moduleImportant) }}
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        </span>
                                                    </a>
                                                </li>
                                                @foreach ($module['folders'] as $folder)
                                                    @php
                                                        $folderKey = $folder['key'];
                                                        $folderStats = $stats[$folderKey] ?? ['total' => 0];
                                                        $folderImportant = $folderStats['important'] ?? null;
                                                        $folderUrl = $buildTabUrl($folderKey);
                                                        $folderActive = $tab === $folderKey;
                                                        $folderIcon = $folderIconMap[$folder['folder']] ?? $folderIcons['default'];
                                                    @endphp
                                                    <li class="drive-tree__item">
                                                        <a href="{{ $folderUrl }}" class="drive-tree__link {{ $folderActive ? 'is-active' : '' }}" data-drive-folder-link data-drive-folder="{{ $folderKey }}" aria-current="{{ $folderActive ? 'page' : 'false' }}">
                                                            <span class="drive-tree__icon">
                                                                <i class="{{ $folderIcon }}" aria-hidden="true"></i>
                                                            </span>
                                                            <span class="drive-tree__info">
                                                                <span class="drive-tree__name">{{ $folder['label'] }}</span>
                                                                <span class="drive-tree__stats">
                                                                    <span class="drive-tree__count">{{ number_format((int) ($folderStats['total'] ?? 0)) }}</span>
                                                                    @if (!is_null($folderImportant) && $folderImportant > 0)
                                                                        <span class="drive-tree__badge" aria-label="Önemli dosya sayısı">
                                                                            <i class="bi bi-star-fill" aria-hidden="true"></i>
                                                                            {{ number_format($folderImportant) }}
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            </span>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>

                    <div class="drive-tree__usage" data-drive-storage>
                        <div class="drive-tree__usage-header">
                            <span class="drive-tree__usage-title">Depolama</span>
                            <span class="drive-tree__usage-meta">
                                <span data-drive-storage-used-label>{{ $formatSize($storageUsed) }}</span>
                                /
                                <span data-drive-storage-limit-label>{{ $formatSize($storageLimit) }}</span>
                            </span>
                        </div>
                        <div class="drive-tree__usage-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                            aria-valuenow="{{ (int) round($storagePercent) }}" data-drive-storage-bar>
                            <div class="drive-tree__usage-fill" data-drive-storage-fill style="width: {{ $storagePercent }}%"></div>
                        </div>
                        <div class="drive-tree__usage-footer">
                            <span>Kalan: <span data-drive-storage-remaining-label>{{ $formatSize($storageRemaining) }}</span></span>
                            <span data-drive-storage-percent-label>{{ number_format($storagePercent, $storagePercent >= 10 ? 0 : 1) }}%</span>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="drive__content">
                <div class="drive__toolbar" data-drive-search>
                    <form class="drive__search" data-drive-search-form method="GET"
                        action="{{ route('admin.drive.media.index') }}">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        @if ($pickerMode)
                            <input type="hidden" name="picker" value="1">
                        @endif
                        <x-ui-input type="search" name="q" :value="request('q')" placeholder="Dosya adı, uzantı veya etiket" data-drive-search-input />
                        <x-ui-button type="submit" variant="secondary" data-drive-search-submit>Ara</x-ui-button>
                    </form>

                     <div>
                        @if (!$pickerMode)
                            <x-ui-button variant="outline-primary" data-action="drive-open-upload"
                                class="drive__upload-trigger ui-button ui-button--secondary ui-button--md">
                                <i class="bi bi-upload" aria-hidden="true"></i>
                                <span>Dosya Yükle</span>
                            </x-ui-button>
                        @endif
                    </div>
                </div>


                <div class="drive__grid" data-drive-grid>
                    @forelse($mediaItems as $media)
                        @php
                            $moduleLabel = Media::moduleLabel($media->module);
                            $searchIndex = Str::lower($media->original_name . ' ' . $media->mime . ' ' . $media->ext . ' ' . $media->module . ' ' . $moduleLabel);
                            $uploaderName = $media->uploader?->name ?? 'Sistem';
                            $importantTitleOn = 'Önemli işaretini kaldır';
                            $importantTitleOff = 'Önemli olarak işaretle';
                        @endphp
                        <x-ui-card class="drive-card {{ $media->is_important ? 'drive-card--important' : '' }}"
                            data-drive-row data-id="{{ $media->id }}" data-search="{{ $searchIndex }}"
                            data-name="{{ Str::lower($media->original_name) }}"
                            data-original-name="{{ $media->original_name }}"
                            data-ext="{{ Str::lower($media->ext) }}"
                            data-mime="{{ Str::lower($media->mime) }}"
                            data-size="{{ (int) $media->size }}"
                            data-category="{{ $media->category }}"
                            data-module="{{ $media->module }}"
                            data-module-label="{{ Str::lower($moduleLabel ?? '') }}"
                            data-path="{{ $media->path }}"
                            data-important="{{ $media->is_important ? '1' : '0' }}"
                            data-download-url="{{ route('admin.drive.media.download', $media) }}"
                            data-delete-url="{{ route('admin.drive.media.destroy', $media) }}"
                            data-toggle-important-url="{{ route('admin.drive.media.toggle_important', $media) }}"
                            >

                            <div class="drive-card__body">
                                <div>
                                    <div class="drive-card__details">
                                        <div class="drive-card__icon">
                                            <x-ui-file-icon :ext="$media->ext" size="44" />
                                        </div>
                                        <div class="drive-card__info">
                                            <p class="drive-card__meta">
                                                · {{ $uploaderName }}
                                                <br/>
                                                · {{ $media->created_at?->diffForHumans() }}
                                                <br/>
                                                · {{ $formatSize($media->size) }}
                                                @if ($moduleLabel)
                                                    <br/>
                                                    · {{ $moduleLabel }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <h3 class="drive-card__title" title="{{ $media->original_name }}">
                                        {{ $media->original_name }}
                                    </h3>
                                </div>

                                <div class="drive-card__actions" role="group" aria-label="{{ $media->original_name }} dosya aksiyonları">
                                    @if ($pickerMode)
                                        <x-ui-button variant="primary" size="sm" data-action="drive-picker-select"
                                            data-id="{{ $media->id }}" data-name="{{ $media->original_name }}"
                                            data-ext="{{ $media->ext }}" data-mime="{{ $media->mime }}"
                                            data-size="{{ $media->size }}"
                                            data-path="{{ $media->path }}"
                                            data-url="{{ route('admin.drive.media.download', $media) }}">Seç</x-ui-button>
                                    @else

                                        @can('markImportant', $media)
                                            <x-ui-button variant="ghost" size="sm"
                                                class="drive-card__action drive-card__action--important {{ $media->is_important ? 'is-active' : '' }}"
                                                icon="{{ $media->is_important ? 'bi bi-star-fill' : 'bi bi-star' }}"
                                                data-action="drive-toggle-important"
                                                data-url="{{ route('admin.drive.media.toggle_important', $media) }}"
                                                aria-pressed="{{ $media->is_important ? 'true' : 'false' }}"
                                                data-title-on="{{ $importantTitleOn }}"
                                                data-title-off="{{ $importantTitleOff }}" data-bs-toggle="tooltip"
                                                title="{{ $media->is_important ? $importantTitleOn : $importantTitleOff }}">
                                                <span class="visually-hidden">Önemli olarak işaretle</span>
                                            </x-ui-button>
                                        @endcan

                                        @can('view', $media)
                                            <x-ui-button tag="a" href="{{ route('admin.drive.media.download', $media) }}"
                                                variant="ghost" size="sm" class="drive-card__action" icon="bi bi-download"
                                                data-bs-toggle="tooltip" title="İndir">
                                                <span class="visually-hidden">İndir</span>
                                            </x-ui-button>
                                        @endcan

                                        @can('replace', $media)
                                            <x-ui-button variant="ghost" size="sm" class="drive-card__action"
                                                icon="bi bi-arrow-repeat" data-action="drive-open-replace"
                                                data-id="{{ $media->id }}" data-name="{{ $media->original_name }}"
                                                data-bs-toggle="tooltip" title="Dosyayı değiştir">
                                                <span class="visually-hidden">Değiştir</span>
                                            </x-ui-button>
                                        @endcan

                                        @can('delete', $media)
                                            <x-ui-button variant="ghost" size="sm"
                                                class="drive-card__action drive-card__action--danger" icon="bi bi-trash"
                                                data-action="drive-delete" data-id="{{ $media->id }}"
                                                data-name="{{ $media->original_name }}"
                                                data-url="{{ route('admin.drive.media.destroy', $media) }}"
                                                data-bs-toggle="tooltip" title="Sil">
                                                <span class="visually-hidden">Sil</span>
                                            </x-ui-button>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </x-ui-card>
                    @empty
                        <x-ui-card class="drive-card drive-card--empty">
                            <x-ui-empty icon="folder" title="Henüz dosya yok"
                                description="İlk dosyanızı yükleyerek alanı doldurun." />
                        </x-ui-card>
                    @endforelse
                </div>

                <div class="drive__empty" data-drive-empty @if ($mediaItems->count()) hidden @endif>
                    <x-ui-empty icon="search" title="Sonuç bulunamadı"
                        description="Arama kriterinizi güncelleyerek yeniden deneyin." />
                </div>

                @if ($mediaItems->hasPages())
                    <div class="drive__pagination" data-drive-pagination>
                        <x-ui-pagination :paginator="$mediaItems" />
                    </div>
                @endif

                @if (!$pickerMode)
                    <x-ui-card class="drive-upload" data-drive-upload-panel hidden>
                        <div class="drive-upload__header">
                            <div>
                                <h2 class="drive-upload__title">Dosya yükle</h2>
                                <p class="drive-upload__description">Dosyaları sürükleyip bırakın veya bilgisayarınızdan
                                    seçin. Aynı anda en fazla 10 dosya yüklenebilir.</p>
                            </div>
                            <div class="drive-upload__controls">
                                <x-ui-select name="module" label="Modül" class="mb-0"
                                    data-drive-module-select :options="$moduleOptions" :value="$activeModule" />
                                <x-ui-button variant="ghost" data-action="drive-close-upload">Kapat</x-ui-button>
                            </div>
                        </div>
                        <div class="drive-upload__drop" data-drive-dropzone tabindex="0" role="button">
                            <i class="bi bi-cloud-arrow-up drive-upload__icon" aria-hidden="true"></i>
                            <p class="drive-upload__lead">Dosyalarınızı buraya bırakın</p>
                            <p class="drive-upload__hint">veya</p>
                            <label class="drive-upload__trigger">
                                Dosya Seç
                                <input type="file" name="files[]" multiple class="visually-hidden"
                                    data-drive-file-input>
                            </label>
                            <p class="drive-upload__note" data-drive-category-note>
                                @if (($categoryLimits[$activeFolder] ?? null) !== null)
                                    Kabul edilen uzantılar: {{ $categoryLimits[$activeFolder]['mimes'] }} · Maks
                                    {{ $categoryLimits[$activeFolder]['max'] }}
                                @endif
                            </p>
                        </div>
                        <div class="drive-upload__progress" data-drive-progress hidden>
                            <div class="drive-upload__progress-head">
                                <span class="drive-upload__progress-title">Yükleme durumu</span>
                                <button type="button" class="drive-upload__progress-clear"
                                    data-action="drive-clear-progress">Temizle</button>
                            </div>
                            <div class="drive-upload__progress-items" data-drive-progress-items></div>
                        </div>
                    </x-ui-card>
                @endif
            </section>
        </div>

        @if (!$pickerMode)
            <x-ui-modal id="driveReplaceModal" :title="'Dosyayı değiştir'">
                <div class="drive-replace" data-drive-replace-modal>
                    <p class="drive-replace__summary" data-drive-replace-name></p>
                    <x-ui-input type="file" name="drive_replace" id="drive-replace-input" label="Yeni dosya"
                        data-drive-replace-input />
                    <p class="drive-replace__error" data-drive-replace-error hidden></p>
                    <div class="drive-replace__actions">
                        <x-ui-button variant="ghost" data-action="close">Vazgeç</x-ui-button>
                        <x-ui-button variant="primary" data-action="drive-submit-replace">Kaydet</x-ui-button>
                    </div>
                </div>
            </x-ui-modal>

            <x-ui-confirm id="driveDeleteConfirm" title="Dosyayı sil"
                message="Seçili dosyayı silmek üzeresiniz. Bu işlem geri alınamaz." type="danger" confirm-label="Sil"
                cancel-label="Vazgeç" />
        @endif
    @endsection
