import bus from '../lib/bus.js';

const DEFAULT_MODAL_ID = 'drivePickerModal';
const DRIVE_MESSAGE_PREFIX = 'drive:';
const ORIGIN = window.location.origin;

const EXT_GROUPS = {
    image: ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'tif', 'tiff'],
    pdf: ['pdf'],
    doc: ['doc', 'docx', 'rtf', 'txt', 'md'],
    sheet: ['xls', 'xlsx', 'csv', 'ods'],
    archive: ['zip', 'rar', '7z', 'tar', 'gz'],
    audio: ['mp3', 'wav', 'aac', 'ogg', 'flac'],
    video: ['mp4', 'mov', 'avi', 'mkv', 'webm'],
    code: ['json', 'xml', 'html', 'css', 'js', 'php'],
};

const pickers = new Map();
let initialized = false;
let activePicker = null;

const normalizeExt = (value) => (value || '').toString().trim().toLowerCase();

const getFileKind = (ext) => {
    const normalized = normalizeExt(ext);
    let kind = 'file';

    Object.entries(EXT_GROUPS).forEach(([label, list]) => {
        if (list.includes(normalized)) {
            kind = label;
        }
    });

    return kind;
};

const getFileLabel = (ext, kind) => {
    const normalized = normalizeExt(ext);
    const display = normalized ? normalized.slice(0, 4).toUpperCase() : '';

    if (display) {
        return display;
    }

    return (
        {
            image: 'IMG',
            pdf: 'PDF',
            doc: 'DOC',
            sheet: 'SHT',
            archive: 'ZIP',
            audio: 'AUD',
            video: 'VID',
            code: 'CODE',
        }[kind] || 'FILE'
    );
};

const escapeHtml = (value) => {
    const text = (value ?? '').toString();
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

const formatBytes = (value) => {
    const bytes = Number(value) || 0;
    if (bytes >= 1_073_741_824) {
        return `${(bytes / 1_073_741_824).toFixed(2)} GB`;
    }
    if (bytes >= 1_048_576) {
        return `${(bytes / 1_048_576).toFixed(2)} MB`;
    }
    if (bytes >= 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }
    return `${bytes} B`;
};

const renderIconMarkup = (ext, size = 36) => {
    const kind = getFileKind(ext);
    const label = getFileLabel(ext, kind);
    return `
        <span class="ui-file-icon" data-ui="file-icon" data-kind="${escapeHtml(kind)}" aria-hidden="true" style="--ui-file-icon-size: ${Math.max(size, 16)}px">
            <span class="ui-file-icon__label">${escapeHtml(label)}</span>
        </span>
    `;
};

const renderEmpty = (preview, message = '') => {
    if (!preview) return;
    const text = message || preview.dataset.emptyMessage || '';
    const template = preview.dataset.drivePickerTemplate || 'default';
    const emptyClass = template === 'inventory-media' ? 'inventory-media-empty' : 'text-muted';

    preview.innerHTML = text
        ? `<div class="${emptyClass}">${escapeHtml(text)}</div>`
        : '';
    preview.dataset.drivePickerState = 'empty';
    preview.dataset.drivePickerValue = 'null';
};

const renderPreview = (preview, file) => {
    if (!preview) return;

    const template = preview.dataset.drivePickerTemplate || 'default';
    const name = escapeHtml(file?.name || file?.original_name || 'Dosya');
    const mime = escapeHtml(file?.mime || '');
    const ext = file?.ext || file?.extension || '';
    const sizeLabel = formatBytes(file?.size || 0);
    const icon = renderIconMarkup(ext, template === 'inventory-media' ? 36 : 28);

    let markup = `
        <div class="drive-picker-preview">
            ${icon}
            <div class="drive-picker-preview__meta">
                <div class="drive-picker-preview__name">${name}</div>
                <div class="drive-picker-preview__desc">${mime ? `${mime} · ` : ''}${sizeLabel}</div>
            </div>
        </div>
    `;

    if (template === 'inventory-media') {
        markup = `
            <div class="inventory-media-preview">
                ${icon}
                <div class="inventory-media-preview__meta">
                    <div class="inventory-media-preview__name">${name}</div>
                    <div class="inventory-media-preview__desc">${mime ? `${mime} · ` : ''}${sizeLabel}</div>
                </div>
            </div>
        `;
    }

    preview.innerHTML = markup.trim();
    preview.dataset.drivePickerState = 'filled';
    preview.dataset.drivePickerValue = JSON.stringify(file ?? null);
};

const ensurePickerEntry = (key) => {
    if (!key) {
        return null;
    }

    const existing = pickers.get(key) ?? {};
    pickers.set(key, existing);
    return existing;
};

const registerPreview = (preview) => {
    const key = preview?.dataset.drivePickerKey;
    if (!key) return;

    const entry = ensurePickerEntry(key);
    entry.preview = preview;
    entry.emptyMessage = preview.dataset.emptyMessage || entry.emptyMessage || '';
    entry.template = preview.dataset.drivePickerTemplate || entry.template || 'default';

    let initial = null;
    try {
        initial = JSON.parse(preview.dataset.drivePickerValue ?? 'null');
    } catch (error) {
        initial = null;
    }

    if (initial) {
        renderPreview(preview, initial);
    } else {
        renderEmpty(preview, entry.emptyMessage);
    }
};

const registerInput = (input) => {
    const key = input?.dataset.drivePickerKey;
    if (!key) return;

    const entry = ensurePickerEntry(key);
    entry.input = input;
};

const getFrame = (modalId) => {
    if (!modalId) return null;
    const modal = document.getElementById(modalId);
    if (!modal) return null;
    return modal.querySelector('[data-drive-picker-frame]');
};

const resolveFrameSrc = (frame) => {
    if (!frame) return null;
    if (frame.dataset.drivePickerSrc) {
        return frame.dataset.drivePickerSrc;
    }
    const current = frame.getAttribute('src') || '';
    frame.dataset.drivePickerSrc = current || `${window.location.origin}/admin/drive`;
    return frame.dataset.drivePickerSrc;
};

const closePicker = (modalId = null) => {
    const targetId = modalId || activePicker?.modalId;
    if (!targetId) return;
    bus.emit('ui:modal:close', { id: targetId });
    if (activePicker?.modalId === targetId) {
        activePicker = null;
    }
};

const handleSelection = (key, file) => {
    if (!file) return;

    if (activePicker?.onSelect) {
        activePicker.onSelect(file);
        return;
    }

    if (!key || !pickers.has(key)) {
        return;
    }

    const entry = pickers.get(key);
    if (entry?.input) {
        entry.input.value = file.id ?? '';
        entry.input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (entry?.preview) {
        renderPreview(entry.preview, file);
    }
};

const openPicker = ({
    key = null,
    modalId = DEFAULT_MODAL_ID,
    folderId = null,
    ext = null,
    mime = null,
    trigger = null,
    onSelect = null,
    query = {},
} = {}) => {
    const frame = getFrame(modalId);
    if (!frame) {
        console.warn('Drive picker iframe bulunamadı:', modalId);
        return;
    }

    const baseSrc = resolveFrameSrc(frame);
    const url = new URL(baseSrc || `${window.location.origin}/admin/drive`, window.location.origin);
    url.searchParams.set('picker', '1');

    if (folderId) {
        url.searchParams.set('tab', folderId);
    }

    if (ext) {
        url.searchParams.set('ext', ext);
    }

    if (mime) {
        url.searchParams.set('mime', mime);
    }

    Object.entries(query || {}).forEach(([param, value]) => {
        if (value === undefined || value === null || value === '') return;
        url.searchParams.set(param, value);
    });

    frame.src = url.toString();
    frame.dataset.drivePickerActiveKey = key || '';

    activePicker = {
        key,
        modalId,
        onSelect,
    };

    bus.emit('ui:modal:open', { id: modalId, source: trigger || null });
};

const handleMessage = (event) => {
    if (event.origin !== ORIGIN) {
        return;
    }

    const { type, payload } = event.data || {};
    if (typeof type !== 'string' || !type.startsWith(DRIVE_MESSAGE_PREFIX)) {
        return;
    }

    const message = type.replace(DRIVE_MESSAGE_PREFIX, '');

    if (message === 'picker:selected') {
        const file = payload?.file ?? null;
        const key =
            activePicker?.key
            || document
                .querySelector('[data-drive-picker-frame][data-drive-picker-active-key]')
                ?.dataset?.drivePickerActiveKey;
        handleSelection(key, file);
        closePicker();
    }
};

const attachDomListeners = () => {
    document.addEventListener('click', (event) => {
        const openTrigger = event.target.closest('[data-drive-picker-open]');
        if (openTrigger) {
            event.preventDefault();
            const key = openTrigger.dataset.drivePickerKey || null;
            const modalId = openTrigger.dataset.drivePickerModal || DEFAULT_MODAL_ID;
            const folderId = openTrigger.dataset.drivePickerFolder || null;
            const ext = openTrigger.dataset.drivePickerExt || null;
            const mime = openTrigger.dataset.drivePickerMime || null;

            ensurePickerEntry(key);

            openPicker({
                key,
                modalId,
                folderId,
                ext,
                mime,
                trigger: openTrigger,
            });
        }

        const clearTrigger = event.target.closest('[data-drive-picker-clear]');
        if (clearTrigger) {
            event.preventDefault();
            const key = clearTrigger.dataset.drivePickerKey;
            const entry = key ? pickers.get(key) : null;
            if (!entry) {
                return;
            }
            if (entry.input) {
                entry.input.value = '';
                entry.input.dispatchEvent(new Event('change', { bubbles: true }));
            }
            if (entry.preview) {
                renderEmpty(entry.preview, entry.emptyMessage);
            }
        }
    });
};

const bootstrap = () => {
    document.querySelectorAll('[data-drive-picker-preview]').forEach((preview) => registerPreview(preview));
    document.querySelectorAll('[data-drive-picker-input]').forEach((input) => registerInput(input));
};

const initDrivePickerHost = () => {
    if (initialized) {
        return;
    }

    initialized = true;
    bootstrap();
    attachDomListeners();
    window.addEventListener('message', handleMessage);
    bus.on('ui:overlay:closed', ({ id, type }) => {
        if (type === 'modal' && activePicker?.modalId === id) {
            activePicker = null;
        }
    });

    window.Drive = window.Drive || {};
    window.Drive.open = (options = {}) => openPicker(options);
    window.Drive.close = (modalId) => closePicker(modalId);
};

export { initDrivePickerHost };
