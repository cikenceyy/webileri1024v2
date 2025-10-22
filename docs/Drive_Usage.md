# Drive Module Usage Guide

## Overview

The Drive module provides tenant-scoped file management with granular permissions and reusable UI primitives. The recent update adds a cross-module picker API, richer JSON payloads for media records, and end-to-end policy alignment with the new `drive.file.*` permission namespace.

Key capabilities include:

* Upload, replace, delete, download, and mark files as important without leaving the listing view.
* Client-side optimistic updates for upload, replace, delete, and importance toggles.
* A reusable picker modal that can be triggered from any module via HTML data attributes or the global `window.Drive` API.
* Consistent company scoping enforced through policies, middleware, and feature tests.

## Storage quotas

Every company starts with **1 GB** of Drive storage. The current usage and remaining quota are displayed beneath the folder tree
inside the Drive UI, complete with a live progress bar that updates after uploads, replacements, or deletions.

* Customize the default limit through the `drive_storage_limit_bytes` column on the `companies` table. Higher tiers can be
  provisioned by updating this field per tenant.
* Override the platform-wide default (still 1 GB) with the `DRIVE_DEFAULT_STORAGE_LIMIT_BYTES` environment variable if you need a
  different baseline.

All calculations stay tenant-scoped; soft-deleted media are excluded from the usage total.

## Permissions

The Drive module expects the following Spatie permission keys:

| Ability | Permission key |
| --- | --- |
| View listing / metadata | `drive.file.view` |
| Download files | `drive.file.download` |
| Upload new files | `drive.file.upload` |
| Replace existing files | `drive.file.update` |
| Delete files | `drive.file.delete` |
| Toggle important flag | `drive.file.mark` |

Assign permissions per tenant team via `PermissionRegistrar::setPermissionsTeamId()` to honour multi-company isolation.

## Picker Modal Integration

### Declarative markup (recommended)

Add triggers, inputs, and preview containers using `data-drive-picker-*` attributes. The Drive picker host script wires everything automatically.

```blade
@php
    $pickerKey = 'product-cover';
    $initial = $media ? [
        'id' => $media->id,
        'name' => $media->original_name,
        'mime' => $media->mime,
        'ext' => $media->ext,
        'size' => $media->size,
        'url' => route('admin.drive.media.download', $media),
    ] : null;
@endphp

<button
    type="button"
    class="btn btn-outline-secondary"
    data-drive-picker-open
    data-drive-picker-key="{{ $pickerKey }}"
    data-drive-picker-modal="drivePickerModal"
    data-drive-picker-folder="{{ \App\Modules\Drive\Domain\Models\Media::CATEGORY_MEDIA_PRODUCTS }}"
>
    Drive'dan Seç
</button>

<button
    type="button"
    class="btn btn-outline-danger"
    data-drive-picker-clear
    data-drive-picker-key="{{ $pickerKey }}"
>
    Temizle
</button>

<input
    type="hidden"
    name="media_id"
    data-drive-picker-input
    data-drive-picker-key="{{ $pickerKey }}"
    value="{{ old('media_id', $media?->id) }}"
>

<div
    class="border rounded p-3"
    data-drive-picker-preview
    data-drive-picker-key="{{ $pickerKey }}"
    data-drive-picker-template="inventory-media"
    data-empty-message="Drive içinden bir kapak görseli seçin."
    data-drive-picker-value='@json($initial)'
>
    {{-- Optional: server-rendered fallback preview --}}
</div>
```

Ensure the modal exists once in the layout:

```blade
<x-ui-modal id="drivePickerModal" size="xl">
    <x-slot name="title">Drive'dan Dosya Seç</x-slot>
    <div class="ratio ratio-16x9">
        <iframe
            data-drive-picker-frame
            data-drive-picker-src="{{ route('admin.drive.media.index', ['picker' => 1]) }}"
            src="{{ route('admin.drive.media.index', ['tab' => 'media_products', 'picker' => 1]) }}"
            allow="autoplay"
            title="Drive Picker"
        ></iframe>
    </div>
</x-ui-modal>
```

### Programmatic usage

The picker host exposes a lightweight API that resolves a promise-like flow via callbacks:

```js
window.Drive.open({
    modalId: 'drivePickerModal',
    folderId: 'media_products',
    onSelect(file) {
        console.log('Selected media', file);
        // file => { id, name, original_name, ext, mime, size, path, url, download_url, category }
    },
});
```

Call `window.Drive.close()` to manually dismiss the modal if needed.

## REST Endpoints

All routes are scoped under `/admin/drive` and protected by the `tenant`, `auth`, and `verified` middleware stack.

| Method | URI | Controller action | Notes |
| --- | --- | --- | --- |
| GET | `/admin/drive` | `MediaController@index` | Listing & filters (supports `picker=1`). |
| POST | `/admin/drive/upload` | `MediaController@store` | JSON response includes `media` + `message`. |
| POST | `/admin/drive/upload-many` | `MediaController@storeMany` | Batch upload endpoint. |
| POST | `/admin/drive/{media}/replace` | `MediaController@replace` | Returns updated media payload. |
| DELETE | `/admin/drive/{media}` | `MediaController@destroy` | Soft deletes by default. |
| POST | `/admin/drive/{media}/toggle-important` | `MediaController@toggleImportant` | Toggles boolean flag. |
| GET | `/admin/drive/{media}/download` | `MediaController@download` | Authorizes via `drive.file.download`. |

## Frontend behaviours

* Upload, replace, delete, and important toggles refresh the DOM optimistically using Vite-powered module scripts.
* Picker mode broadcasts `drive:picker:*` `postMessage` events to the opener window; listeners are automatically attached by `resources/js/components/drive-picker-host.js`.
* Every media card stores metadata through `data-drive-row` attributes, enabling quick DOM updates without layout shifts.

## Cleanup log

No redundant Drive assets were detected during the refactor. Existing scripts and styles were consolidated, and unused selector hooks (`data-product-media-*`, `data-company-logo-*`) were removed.
