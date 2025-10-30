{{--
    Drive dosyalarını modül kayıtlarına iliştirmek için ortak modal bileşeni.
    data-context="module.record" ile hangi kayıt için açıldığı belirtilmelidir.
--}}
<x-ui-modal id="driveAttachModal" size="lg" data-controller="drive-attach" aria-labelledby="driveAttachModalTitle">
    <x-slot name="title">
        <span id="driveAttachModalTitle">{{ __('Drive’dan Dosya Ekle') }}</span>
    </x-slot>

    <div class="drive-attach__toolbar mb-3">
        <div class="input-group">
            <span class="input-group-text" id="drive-attach-search-label">
                <i class="bi bi-search" aria-hidden="true"></i>
            </span>
            <input
                type="search"
                class="form-control"
                placeholder="{{ __('Dosya ara…') }}"
                aria-label="{{ __('Dosya ara') }}"
                data-drive-attach-target="search"
                data-action="input->drive-attach#debouncedSearch"
            >
        </div>
    </div>

    <div class="drive-attach__body" data-drive-attach-target="list"></div>

    <x-slot name="footer">
        <x-ui-button type="button" class="btn-secondary" data-action="drive-attach#close">
            {{ __('Kapat') }}
        </x-ui-button>
        <x-ui-button type="button" class="btn-primary" data-action="drive-attach#confirm" data-drive-attach-target="confirm">
            {{ __('Seçilenleri Ekle') }}
        </x-ui-button>
    </x-slot>
</x-ui-modal>
