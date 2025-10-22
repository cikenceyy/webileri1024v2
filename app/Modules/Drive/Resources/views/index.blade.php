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

        $categories = array_filter(
            $tabs,
            fn($key) => in_array(
                $key,
                [
                    Media::CATEGORY_DOCUMENTS,
                    Media::CATEGORY_MEDIA_PRODUCTS,
                    Media::CATEGORY_MEDIA_CATALOGS,
                    Media::CATEGORY_PAGES,
                ],
                true,
            ),
            ARRAY_FILTER_USE_KEY,
        );

        $activeCategory = in_array($tab, array_keys($categories), true) ? $tab : Media::CATEGORY_DOCUMENTS;

        $categoryLimits = collect($categoryConfig ?? [])->mapWithKeys(
            fn($config, $key) => [
                $key => [
                    'mimes' => implode(', ', $config['ext'] ?? []),
                    'max' => $formatSize(min((int) ($config['max'] ?? $globalMaxBytes), $globalMaxBytes)),
                ],
            ],
        );

        $totalCount = $mediaItems->total();
        $activeTabLabel = $tabs[$tab] ?? 'Tümü';

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
            'recent' => 'bi bi-clock-history',
            'important' => 'bi bi-star-fill',
            Media::CATEGORY_DOCUMENTS => 'bi bi-file-earmark-text',
            Media::CATEGORY_MEDIA_PRODUCTS => 'bi bi-box-seam',
            Media::CATEGORY_MEDIA_CATALOGS => 'bi bi-book',
            Media::CATEGORY_PAGES => 'bi bi-file-earmark-code',
            'default' => 'bi bi-folder',
        ];

    @endphp

    <div class="drive" 
        data-drive-root data-drive-total="{{ $totalCount }}"
        data-drive-page-size="{{ $mediaItems->perPage() }}" data-drive-search-url="{{ route('admin.drive.media.index') }}"
        data-drive-active-tab="{{ $tab }}" data-category-default="{{ $activeCategory }}"
        data-category-limits='@json($categoryLimits)' data-picker-mode="{{ $pickerMode ? '1' : '0' }}"
        data-upload-url="{{ route('admin.drive.media.store') }}"
        data-upload-many-url="{{ route('admin.drive.media.store_many') }}"
        data-replace-url-template="{{ route('admin.drive.media.replace', ['media' => '__ID__']) }}"
        data-toggle-important-template="{{ route('admin.drive.media.toggle_important', ['media' => '__ID__']) }}"
        data-download-url-template="{{ route('admin.drive.media.download', ['media' => '__ID__']) }}"
        data-delete-url-template="{{ route('admin.drive.media.destroy', ['media' => '__ID__']) }}"
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
                        @foreach ($folderGroups as $groupTitle => $keys)
                            @if (count($keys))
                                @php
                                    $groupId = 'drive-tree-' . Str::slug($groupTitle) . '-' . $loop->index;
                                @endphp
                                <li class="drive-tree__group" data-drive-tree-item>
                                    <div class="drive-tree__toggle" aria-controls="{{ $groupId }}">
                                        <span class="drive-tree__toggle-label">{{ $groupTitle }}</span>
                                    </div>
                                    <ul class="drive-tree__panel" id="{{ $groupId }}" data-drive-tree-panel role="group" aria-hidden="false">
                                        @foreach ($keys as $key)
                                            @php
                                                $isActive = $tab === $key;
                                                $folderStat = $stats[$key] ?? ['total' => 0];
                                                $importantCount = $folderStat['important'] ?? null;
                                                $icon = $folderIcons[$key] ?? $folderIcons['default'];
                                                $itemUrl = $buildTabUrl($key);
                                            @endphp
                                            <li class="drive-tree__item">
                                                <a href="{{ $itemUrl }}" class="drive-tree__link {{ $isActive ? 'is-active' : '' }}" data-drive-folder-link data-drive-folder="{{ $key }}" aria-current="{{ $isActive ? 'page' : 'false' }}" >
                                                    <span class="drive-tree__icon">
                                                        <i class="{!! $icon !!}"> </i>
                                                    </span>
                                                    <span class="drive-tree__info">
                                                        <span class="drive-tree__name">
                                                            {{ $tabs[$key] ?? ucfirst($key) }}
                                                        </span>
                                                        <span class="drive-tree__stats">
                                                            <span class="drive-tree__count">
                                                                {{ number_format((int) ($folderStat['total'] ?? 0)) }}
                                                            </span>
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
                    </ul>
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
                            $searchIndex = Str::lower($media->original_name . ' ' . $media->mime . ' ' . $media->ext);
                            $uploaderName = $media->uploader?->name ?? 'Sistem';
                            $importantTitleOn = 'Önemli işaretini kaldır';
                            $importantTitleOff = 'Önemli olarak işaretle';
                        @endphp
                        <x-ui-card class="drive-card {{ $media->is_important ? 'drive-card--important' : '' }}"
                            data-drive-row data-id="{{ $media->id }}" data-search="{{ $searchIndex }}"
                            data-name="{{ Str::lower($media->original_name) }}" data-ext="{{ Str::lower($media->ext) }}"
                            data-mime="{{ Str::lower($media->mime) }}"
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
                                            data-size="{{ $media->size }}">Seç</x-ui-button>
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
                                <x-ui-select name="upload_category" label="Kategori" class="mb-0"
                                    data-drive-category-select :options="$categories" :value="$activeCategory" />
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
                                @if (($categoryLimits[$activeCategory] ?? null) !== null)
                                    Kabul edilen uzantılar: {{ $categoryLimits[$activeCategory]['mimes'] }} · Maks
                                    {{ $categoryLimits[$activeCategory]['max'] }}
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
