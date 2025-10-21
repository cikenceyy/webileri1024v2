@extends('layouts.admin')

@section('title', 'Drive')
@section('module', 'Drive')

@section('content')
@php
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
        \App\Modules\Drive\Domain\Models\Media::CATEGORY_DOCUMENTS,
        \App\Modules\Drive\Domain\Models\Media::CATEGORY_MEDIA_PRODUCTS,
        \App\Modules\Drive\Domain\Models\Media::CATEGORY_MEDIA_CATALOGS,
        \App\Modules\Drive\Domain\Models\Media::CATEGORY_PAGES,
    ], true), ARRAY_FILTER_USE_KEY);

    $activeCategory = in_array($tab, array_keys($categories), true)
        ? $tab
        : \App\Modules\Drive\Domain\Models\Media::CATEGORY_DOCUMENTS;

    $categoryLimits = collect($categoryConfig ?? [])->mapWithKeys(fn ($config, $key) => [
        $key => [
            'mimes' => implode(', ', $config['ext'] ?? []),
            'max' => $formatSize(min((int) ($config['max'] ?? $globalMaxBytes), $globalMaxBytes)),
        ],
    ]);

    $initialView = request()->cookie('drive_view', 'grid');
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
     data-picker-mode="{{ $pickerMode ? '1' : '0' }}">
    <x-ui-page-header
        title="Drive"
        description="Dosyalarınızı modern bir arayüzle yönetin, arayın ve düzenleyin">
        @if(! $pickerMode)
            <x-slot name="actions">
                @can('create', \App\Modules\Drive\Domain\Models\Media::class)
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

    <div class="drive-overview mb-4">
        <div class="drive-overview__tabs" role="tablist" aria-label="Drive sekmeleri">
            @foreach($tabs as $key => $label)
                @php
                    $isActive = $tab === $key;
                    $total = $stats[$key]['total'] ?? 0;
                    $important = $stats[$key]['important'] ?? null;
                    $url = route('admin.drive.media.index', array_filter([
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
                @endphp
                <a class="drive-overview__tab {{ $isActive ? 'is-active' : '' }}"
                   href="{{ $url }}"
                   role="tab"
                   aria-selected="{{ $isActive ? 'true' : 'false' }}"
                   @if($isActive) aria-current="page" @endif>
                    <span class="drive-overview__tab-label">{{ $label }}</span>
                    <span class="drive-overview__tab-total">{{ $total }}</span>
                    @if(! is_null($important) && $important > 0)
                        <span class="drive-overview__tab-important" aria-label="Önemli sayısı">
                            ★ {{ $important }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
        <div class="drive-overview__hint text-muted">
            @if($pickerMode)
                Sadece “Ürün Görselleri” kategorisinden seçim yapabilirsiniz.
            @else
                Dosyaları yıldızlayarak "Önemli" sekmesinde hızlı erişim sağlayın.
            @endif
        </div>
    </div>

    <x-ui-card class="drive-toolbar mb-4">
        <form method="GET" action="{{ route('admin.drive.media.index') }}" class="drive-toolbar__form" data-drive-filter-form>
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if($pickerMode)
                <input type="hidden" name="picker" value="1">
            @endif
            <div class="drive-toolbar__row">
                <x-ui-input name="q" :value="request('q')" label="Ara" placeholder="Dosya adı, tür, etiket" />
                <x-ui-select name="sort" label="Sırala" :options="[
                    'created_at' => 'Tarih',
                    'size' => 'Boyut',
                    'original_name' => 'Ad',
                ]" :value="request('sort', 'created_at')" />
                <x-ui-select name="dir" label="Yön" :options="[
                    'asc' => 'Artan',
                    'desc' => 'Azalan',
                ]" :value="request('dir', 'desc')" />
                <div class="drive-toolbar__actions">
                    <x-ui-button type="submit" variant="secondary">Filtrele</x-ui-button>
                    <a class="drive-toolbar__reset" href="{{ route('admin.drive.media.index', array_filter([
                        'tab' => $tab,
                        'picker' => $pickerMode ? 1 : null,
                    ])) }}">Sıfırla</a>
                </div>
                <button type="button" class="drive-toolbar__advanced-toggle" data-drive-filter-toggle aria-expanded="false">
                    Gelişmiş Filtreler
                    <svg aria-hidden="true" focusable="false" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </button>
            </div>
            <div class="drive-toolbar__advanced" data-drive-filters hidden>
                <div class="drive-toolbar__grid">
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
        <div class="drive-toolbar__view" role="group" aria-label="Görünüm değiştirme">
            <button type="button" class="drive-toolbar__view-btn" data-drive-view-control="grid" aria-label="Kart görünümü">
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                </svg>
            </button>
            <button type="button" class="drive-toolbar__view-btn" data-drive-view-control="list" aria-label="Liste görünümü">
                <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </x-ui-card>

    @if(! $pickerMode)
        <x-ui-card class="drive-upload" id="drive-upload-panel" data-drive-upload-panel hidden>
            <div class="drive-upload__header">
                <div>
                    <h2 class="h5 mb-1">Dosya yükle</h2>
                    <p class="text-muted mb-0">Dosyaları sürükleyip bırakın veya bilgisayarınızdan seçin. Aynı anda en fazla 10 dosya yüklenebilir.</p>
                </div>
                <div class="drive-upload__controls">
                    <x-ui-select name="upload_category" label="Kategori" class="mb-0" data-drive-category-select :options="$categories" :value="$activeCategory" />
                    <x-ui-button variant="ghost" data-action="drive-close-upload" aria-label="Yükleme panelini kapat">
                        Kapat
                    </x-ui-button>
                </div>
            </div>
            <div class="drive-upload__drop" tabindex="0" role="button" data-drive-dropzone>
                <svg class="mb-3" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0 4 4m-4-4-4 4" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 16.5a4.5 4.5 0 0 0-3.91-4.455A5 5 0 0 0 6.5 9.5a5.002 5.002 0 0 0-4.5 4.978" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16" />
                </svg>
                <p class="fw-semibold mb-1">Dosyalarınızı buraya bırakın</p>
                <p class="text-muted small mb-3">veya</p>
                <label class="drive-upload__trigger">
                    Dosya Seç
                    <input type="file" name="files[]" multiple class="visually-hidden" data-drive-file-input>
                </label>
                <p class="text-muted small mt-3" data-drive-category-note>
                    @if(($categoryLimits[$activeCategory] ?? null) !== null)
                        Kabul edilen uzantılar: {{ $categoryLimits[$activeCategory]['mimes'] }} · Maks {{ $categoryLimits[$activeCategory]['max'] }}
                    @endif
                </p>
            </div>
            <div class="drive-upload__progress" data-drive-progress hidden>
                <div class="drive-upload__progress-head">
                    <span class="fw-semibold">Yükleme durumu</span>
                    <button type="button" class="drive-progress__clear" data-action="drive-clear-progress">Temizle</button>
                </div>
                <div class="drive-upload__progress-items" data-drive-progress-items></div>
            </div>
        </x-ui-card>
    @endif

    @if($mediaItems->count())
        <div class="drive-view drive-view--grid" data-drive-view="grid">
            <div class="drive-grid">
                @foreach($mediaItems as $media)
                    <article class="drive-card" data-drive-row data-id="{{ $media->id }}" data-important="{{ $media->is_important ? '1' : '0' }}">
                        <header class="drive-card__header">
                            <x-ui-file-icon :ext="$media->ext" size="36" />
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
            <x-ui-card>
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
                                    <div class="d-flex align-items-center gap-2 drive-file-cell">
                                        <span class="fw-semibold text-ellipsis" title="{{ $media->original_name }}">{{ $media->original_name }}</span>
                                        <x-ui-badge
                                            type="warning"
                                            tone="soft"
                                            data-drive-important-badge
                                            :hidden="! $media->is_important"
                                        >★</x-ui-badge>
                                    </div>
                                    <div class="text-muted small text-uppercase">{{ $media->ext }}</div>
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

    <div class="mt-4">
        <x-ui-pagination :paginator="$mediaItems" />
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
