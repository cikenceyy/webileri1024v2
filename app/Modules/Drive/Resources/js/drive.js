import axios from 'axios';
import Tooltip from 'bootstrap/js/dist/tooltip';
import { initLiveSearch, normalizeTerm } from '@/js/components/live-search.js';
import { bus } from '@/js/admin-runtime.js';

const numberFormatter = new Intl.NumberFormat();
const DRIVE_MESSAGE_ORIGIN = window.location.origin;

const postToParent = (type, payload = {}) => {
    if (!type || window === window.parent) {
        return;
    }

    try {
        window.parent.postMessage({ type: `drive:${type}`, payload }, DRIVE_MESSAGE_ORIGIN);
    } catch (error) {
        console.warn('Drive picker iletişimi sırasında hata oluştu.', error);
    }
};

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const toggleHidden = (element, hidden) => {
    if (!element) return;
    if (hidden) {
        element.setAttribute('hidden', '');
    } else {
        element.removeAttribute('hidden');
    }
};

const createProgressItem = (file) => {
    const item = document.createElement('div');
    item.className = 'drive-progress-item';
    item.innerHTML = `
        <div class="drive-progress-item__header">
            <span class="drive-progress-item__name">${file.name}</span>
            <span class="drive-progress-item__status" data-status>Hazırlanıyor…</span>
        </div>
        <div class="drive-progress-item__bar" aria-hidden="true">
            <div class="drive-progress-item__bar-fill" data-fill style="width: 0%"></div>
        </div>
    `;

    const status = item.querySelector('[data-status]');
    const fill = item.querySelector('[data-fill]');

    return {
        element: item,
        setProgress(value) {
            if (!fill) return;
            const percent = Math.max(0, Math.min(100, value));
            fill.style.width = `${percent}%`;
        },
        setStatus(text) {
            if (status) {
                status.textContent = text;
            }
        },
        markError(text) {
            item.classList.add('is-error');
            this.setStatus(text);
        },
        markDone() {
            item.classList.add('is-complete');
            this.setStatus('Tamamlandı');
            this.setProgress(100);
        },
    };
};

const refreshTooltips = (context) => {
    if (!context) return;
    context.querySelectorAll?.('[data-bs-toggle="tooltip"]').forEach((element) => {
        const instance = Tooltip.getOrCreateInstance(element);
        const title = element.getAttribute('title');
        if (title && instance?.setContent) {
            instance.setContent({ '.tooltip-inner': title });
        }
    });
};

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

const createFileIcon = (ext, size = 44) => {
    const kind = getFileKind(ext);
    const label = getFileLabel(ext, kind);
    const icon = document.createElement('span');
    icon.className = 'ui-file-icon';
    icon.dataset.ui = 'file-icon';
    icon.dataset.kind = kind;
    icon.setAttribute('aria-hidden', 'true');
    icon.style.setProperty('--ui-file-icon-size', `${Math.max(size, 16)}px`);

    const labelElement = document.createElement('span');
    labelElement.className = 'ui-file-icon__label';
    labelElement.textContent = label;
    icon.appendChild(labelElement);

    return icon;
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

const formatRelativeTime = (value) => {
    if (!value) {
        return 'Az önce';
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return 'Az önce';
    }

    const diffSeconds = Math.round((Date.now() - date.getTime()) / 1000);
    if (diffSeconds < 60) {
        return 'Az önce';
    }
    if (diffSeconds < 3600) {
        const minutes = Math.max(1, Math.floor(diffSeconds / 60));
        return `${minutes} dk önce`;
    }
    if (diffSeconds < 86400) {
        const hours = Math.max(1, Math.floor(diffSeconds / 3600));
        return `${hours} sa önce`;
    }
    const days = Math.max(1, Math.floor(diffSeconds / 86400));
    return `${days} gün önce`;
};

const formatNumber = (value) => numberFormatter.format(Math.max(0, Number(value) || 0));

const formatPercentage = (value) => {
    const numeric = Math.max(0, Math.min(100, Number(value) || 0));
    if (numeric === 0 || numeric >= 10) {
        return `${numeric.toFixed(0)}%`;
    }

    return `${numeric.toFixed(1)}%`;
};

const createActionButton = ({ tag = 'button', href = '#', icon, label, variant = 'ghost', size = 'sm', extraClass = '', attributes = {} }) => {
    const element = document.createElement(tag === 'a' ? 'a' : 'button');
    const classes = ['ui-button', `ui-button--${variant}`, `ui-button--${size}`, 'drive-card__action'];
    if (extraClass) {
        classes.push(extraClass);
    }
    element.className = classes.join(' ');
    element.dataset.ui = 'button';

    if (tag === 'a') {
        element.href = href;
        element.setAttribute('role', 'button');
    } else {
        element.type = 'button';
    }

    const iconSpan = document.createElement('span');
    iconSpan.className = 'ui-button__icon';
    iconSpan.setAttribute('aria-hidden', 'true');
    const iconElement = document.createElement('i');
    iconElement.className = icon;
    iconElement.setAttribute('aria-hidden', 'true');
    iconSpan.appendChild(iconElement);

    const labelSpan = document.createElement('span');
    labelSpan.className = 'ui-button__label';
    const hiddenLabel = document.createElement('span');
    hiddenLabel.className = 'visually-hidden';
    hiddenLabel.textContent = label;
    labelSpan.appendChild(hiddenLabel);

    element.append(iconSpan, labelSpan);

    Object.entries(attributes).forEach(([key, value]) => {
        if (value === undefined || value === null || value === '') return;
        element.setAttribute(key, String(value));
    });

    return element;
};

const renderMediaCard = (root, media) => {
    const isPicker = root.dataset.pickerMode === '1';
    const card = document.createElement('section');
    card.className = 'ui-card drive-card';
    card.dataset.ui = 'card';
    card.setAttribute('data-drive-row', '');
    card.dataset.id = String(media.id);
    const moduleSlug = (media.module || '').toString().trim();
    const moduleLabel = normalizeTerm(media.module_label || moduleSlug);
    card.dataset.search = normalizeTerm(`${media.original_name || ''} ${media.mime || ''} ${media.ext || ''} ${moduleSlug} ${moduleLabel}`);
    card.dataset.name = normalizeTerm(media.original_name);
    card.dataset.originalName = media.original_name || '';
    card.dataset.ext = normalizeExt(media.ext);
    card.dataset.mime = normalizeTerm(media.mime);
    card.dataset.size = String(media.size ?? 0);
    card.dataset.category = media.category || '';
    card.dataset.module = moduleSlug;
    card.dataset.moduleLabel = moduleLabel;
    card.dataset.important = media.is_important ? '1' : '0';

    const downloadTemplate = root.dataset.downloadUrlTemplate || '';
    const deleteTemplate = root.dataset.deleteUrlTemplate || '';
    const toggleTemplate = root.dataset.toggleImportantTemplate || '';

    const downloadUrl = downloadTemplate.replace('__ID__', media.id);
    const deleteUrl = deleteTemplate.replace('__ID__', media.id);
    const toggleUrl = toggleTemplate.replace('__ID__', media.id);

    card.dataset.downloadUrl = downloadUrl;
    card.dataset.deleteUrl = deleteUrl;
    card.dataset.toggleImportantUrl = toggleUrl;
    card.dataset.path = media.path || '';

    const bodyWrapper = document.createElement('div');
    bodyWrapper.className = 'ui-card__body';
    card.appendChild(bodyWrapper);

    const body = document.createElement('div');
    body.className = 'drive-card__body';
    bodyWrapper.appendChild(body);

    const iconContainer = document.createElement('div');
    iconContainer.className = 'drive-card__icon';
    iconContainer.appendChild(createFileIcon(media.ext));
    body.appendChild(iconContainer);

    const info = document.createElement('div');
    info.className = 'drive-card__info';
    body.appendChild(info);

    const title = document.createElement('h3');
    title.className = 'drive-card__title';
    title.textContent = media.original_name || 'Yeni dosya';
    title.title = media.original_name || '';
    info.appendChild(title);

    const metaLine = [
        (media.ext || '').toString().toUpperCase(),
        media.mime,
        media.size_human || formatBytes(media.size),
    ]
        .filter(Boolean)
        .join(' · ');
    const meta = document.createElement('p');
    meta.className = 'drive-card__meta';
    meta.textContent = metaLine;
    info.appendChild(meta);

    const uploaderName = media.uploader?.name || 'Siz';
    const relative = formatRelativeTime(media.uploaded_at);
    const detail = document.createElement('p');
    detail.className = 'drive-card__meta drive-card__meta--muted';
    const detailParts = [uploaderName, relative];
    if (media.module_label) {
        detailParts.push(media.module_label);
    }
    detail.textContent = detailParts.filter(Boolean).join(' · ');
    info.appendChild(detail);

    const badge = document.createElement('span');
    badge.className = 'drive-card__badge';
    badge.dataset.driveImportantFlag = '';
    if (!media.is_important) {
        badge.setAttribute('hidden', '');
    }
    badge.innerHTML = '<i class="bi bi-star-fill" aria-hidden="true"></i><span class="visually-hidden">Önemli dosya</span>';
    bodyWrapper.appendChild(badge);

    const actions = document.createElement('div');
    actions.className = 'drive-card__actions';
    actions.setAttribute('role', 'group');
    actions.setAttribute('aria-label', `${media.original_name || 'Dosya'} aksiyonları`);
    bodyWrapper.appendChild(actions);

    if (isPicker) {
        const selectButton = document.createElement('button');
        selectButton.type = 'button';
        selectButton.className = 'ui-button ui-button--primary ui-button--sm drive-card__action';
        selectButton.dataset.ui = 'button';
        selectButton.dataset.action = 'drive-picker-select';
        selectButton.dataset.id = String(media.id);
        selectButton.dataset.name = media.original_name || 'Seçilen dosya';
        selectButton.dataset.ext = media.ext || '';
        selectButton.dataset.mime = media.mime || '';
        selectButton.dataset.size = String(media.size ?? 0);
        selectButton.dataset.path = media.path || '';
        selectButton.dataset.url = downloadUrl;

        const labelSpan = document.createElement('span');
        labelSpan.className = 'ui-button__label';
        labelSpan.textContent = 'Seç';
        selectButton.appendChild(labelSpan);

        actions.appendChild(selectButton);
    } else {
        const downloadButton = createActionButton({
            tag: 'a',
            href: downloadUrl,
            icon: 'bi bi-download',
            label: 'İndir',
            attributes: {
                'data-bs-toggle': 'tooltip',
                title: 'İndir',
                'aria-label': 'İndir',
            },
        });
        actions.appendChild(downloadButton);

        const replaceButton = createActionButton({
            icon: 'bi bi-arrow-repeat',
            label: 'Değiştir',
            attributes: {
                'data-action': 'drive-open-replace',
                'data-id': media.id,
                'data-name': media.original_name || 'Seçilen dosya',
                'data-bs-toggle': 'tooltip',
                title: 'Dosyayı değiştir',
                'aria-label': 'Dosyayı değiştir',
            },
        });
        actions.appendChild(replaceButton);

        const importantButton = createActionButton({
            icon: media.is_important ? 'bi bi-star-fill' : 'bi bi-star',
            label: 'Önemli olarak işaretle',
            extraClass: `drive-card__action--important${media.is_important ? ' is-active' : ''}`,
            attributes: {
                'data-action': 'drive-toggle-important',
                'data-url': toggleUrl,
                'aria-pressed': media.is_important ? 'true' : 'false',
                'data-title-on': 'Önemli işaretini kaldır',
                'data-title-off': 'Önemli olarak işaretle',
                'data-bs-toggle': 'tooltip',
                title: media.is_important ? 'Önemli işaretini kaldır' : 'Önemli olarak işaretle',
                'aria-label': media.is_important ? 'Önemli işaretini kaldır' : 'Önemli olarak işaretle',
            },
        });
        actions.appendChild(importantButton);

        const deleteButton = createActionButton({
            icon: 'bi bi-trash',
            label: 'Sil',
            extraClass: 'drive-card__action--danger',
            attributes: {
                'data-action': 'drive-delete',
                'data-id': media.id,
                'data-name': media.original_name || 'Seçilen dosya',
                'data-url': deleteUrl,
                'data-bs-toggle': 'tooltip',
                title: 'Sil',
                'aria-label': 'Sil',
            },
        });
        actions.appendChild(deleteButton);
    }

    return card;
};

const updateSummaryTotal = (root, delta = 0) => {
    const total = Math.max(0, Number(root.dataset.driveTotal || 0) + delta);
    root.dataset.driveTotal = String(total);
    const display = root.querySelector('[data-drive-total-count]');
    if (display) {
        display.textContent = formatNumber(total);
    }
};

const updateStorageMetrics = (root, override = {}) => {
    if (!root) {
        return;
    }

    const limitValue = override.limit !== undefined
        ? Math.max(0, Number(override.limit) || 0)
        : Math.max(0, Number(root.dataset.driveStorageLimit || 0));
    const usedValue = override.used !== undefined
        ? Math.max(0, Number(override.used) || 0)
        : Math.max(0, Number(root.dataset.driveStorageUsed || 0));

    root.dataset.driveStorageLimit = String(limitValue);
    root.dataset.driveStorageUsed = String(usedValue);

    const remainingValue = Math.max(limitValue - usedValue, 0);
    const percentValue = limitValue > 0 ? Math.min(100, (usedValue / limitValue) * 100) : 0;

    const storageScope = root.querySelector('[data-drive-storage]');
    if (!storageScope) {
        return;
    }

    const usedLabel = storageScope.querySelector('[data-drive-storage-used-label]');
    if (usedLabel) {
        usedLabel.textContent = formatBytes(usedValue);
    }

    const limitLabel = storageScope.querySelector('[data-drive-storage-limit-label]');
    if (limitLabel) {
        limitLabel.textContent = formatBytes(limitValue);
    }

    const remainingLabel = storageScope.querySelector('[data-drive-storage-remaining-label]');
    if (remainingLabel) {
        remainingLabel.textContent = formatBytes(remainingValue);
    }

    const percentLabel = storageScope.querySelector('[data-drive-storage-percent-label]');
    if (percentLabel) {
        percentLabel.textContent = formatPercentage(percentValue);
    }

    const fill = storageScope.querySelector('[data-drive-storage-fill]');
    if (fill) {
        fill.style.width = `${percentValue}%`;
    }

    const bar = storageScope.querySelector('[data-drive-storage-bar]');
    if (bar) {
        bar.setAttribute('aria-valuemin', '0');
        bar.setAttribute('aria-valuemax', '100');
        bar.setAttribute('aria-valuenow', Math.round(percentValue).toString());
    }
};

const adjustStorageUsage = (root, delta = 0) => {
    if (!root) {
        return;
    }

    const current = Math.max(0, Number(root.dataset.driveStorageUsed || 0));
    const next = Math.max(0, current + Number(delta || 0));
    updateStorageMetrics(root, { used: next });
};

const updateImportantState = (root, id, isImportant) => {
    const rows = root.querySelectorAll(`[data-drive-row][data-id="${id}"]`);
    rows.forEach((row) => {
        row.dataset.important = isImportant ? '1' : '0';
        row.classList.toggle('drive-card--important', Boolean(isImportant));
        const badge = row.querySelector('[data-drive-important-flag]');
        if (badge) {
            toggleHidden(badge, !isImportant);
        }
        const button = row.querySelector('[data-action="drive-toggle-important"]');
        if (button) {
            button.classList.toggle('is-active', Boolean(isImportant));
            button.setAttribute('aria-pressed', isImportant ? 'true' : 'false');
            const icon = button.querySelector('i');
            if (icon) {
                icon.classList.toggle('bi-star-fill', Boolean(isImportant));
                icon.classList.toggle('bi-star', !isImportant);
            }
            const nextTitle = isImportant ? button.dataset.titleOn : button.dataset.titleOff;
            if (nextTitle) {
                button.setAttribute('title', nextTitle);
                button.setAttribute('aria-label', nextTitle);
                button.dataset.bsOriginalTitle = nextTitle;
                const tooltip = Tooltip.getInstance(button);
                tooltip?.setContent?.({ '.tooltip-inner': nextTitle });
            }
        }
    });
};

const insertMedia = (root, media, currentTerm) => {
    const grid = root.querySelector('[data-drive-grid]');
    if (!grid) {
        return;
    }

    const card = renderMediaCard(root, media);
    const body = card.querySelector('.drive-card__body');
    if (!media.is_important && body) {
        const badge = card.querySelector('[data-drive-important-flag]');
        if (badge) {
            badge.setAttribute('hidden', '');
        }
    }

    grid.prepend(card);
    refreshTooltips(card);
    updateSummaryTotal(root, 1);
    adjustStorageUsage(root, Number(media.size) || 0);
    if (currentTerm !== undefined) {
        const normalized = normalizeTerm(currentTerm);
        const matches = normalized.length === 0 || (card.dataset.search || '').includes(normalized);
        card.toggleAttribute('hidden', !matches);
    }
    const emptyState = root.querySelector('[data-drive-empty]');
    toggleHidden(emptyState, true);
};

const replaceMedia = (root, media, currentTerm) => {
    const grid = root.querySelector('[data-drive-grid]');
    if (!grid) {
        return;
    }

    const existing = grid.querySelector(`[data-drive-row][data-id="${media.id}"]`);
    const previousSize = existing ? Number(existing.dataset.size || 0) : 0;
    const card = renderMediaCard(root, media);

    if (existing) {
        existing.replaceWith(card);
    } else {
        grid.prepend(card);
        updateSummaryTotal(root, 1);
    }

    adjustStorageUsage(root, (Number(media.size) || 0) - previousSize);

    refreshTooltips(card);

    const emptyState = root.querySelector('[data-drive-empty]');
    toggleHidden(emptyState, true);

    if (currentTerm !== undefined) {
        const normalized = normalizeTerm(currentTerm);
        const matches = normalized.length === 0 || (card.dataset.search || '').includes(normalized);
        card.toggleAttribute('hidden', !matches);
    }
};

const removeMedia = (root, id) => {
    const grid = root.querySelector('[data-drive-grid]');
    if (!grid) {
        return;
    }
    const row = grid.querySelector(`[data-drive-row][data-id="${id}"]`);
    const size = row ? Number(row.dataset.size || 0) : 0;
    if (row) {
        row.remove();
    }

    updateSummaryTotal(root, -1);
    adjustStorageUsage(root, -size);

    const hasRows = grid.querySelectorAll('[data-drive-row]').length > 0;
    const emptyState = root.querySelector('[data-drive-empty]');
    toggleHidden(emptyState, hasRows);
};

const bootDrive = () => {
    const root = document.querySelector('[data-drive-root]');
    if (!root) {
        return;
    }

    const isPickerMode = root.dataset.pickerMode === '1';
    const csrfToken = getCsrfToken();
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    let grid = null;
    let emptyState = null;
    let pagination = null;
    let summary = null;
    let currentSearchTerm = normalizeTerm(root.querySelector('[data-drive-search-input]')?.value || '');

    const refreshRefs = () => {
        grid = root.querySelector('[data-drive-grid]');
        emptyState = root.querySelector('[data-drive-empty]');
        pagination = root.querySelector('[data-drive-pagination]');
        summary = root.querySelector('[data-drive-summary]');
    };

    refreshRefs();
    refreshTooltips(root);
    updateStorageMetrics(root);

    root.querySelectorAll('[data-drive-tree-toggle]').forEach((toggle) => {
        const targetId = toggle.getAttribute('aria-controls');
        if (!targetId) return;
        const panel = document.getElementById(targetId);
        if (!panel) return;

        toggle.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') !== 'false';
            const nextExpanded = !expanded;
            toggle.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
            if (nextExpanded) {
                panel.removeAttribute('hidden');
                panel.setAttribute('aria-hidden', 'false');
            } else {
                panel.setAttribute('hidden', '');
                panel.setAttribute('aria-hidden', 'true');
            }
        });
    });

    if (isPickerMode) {
        postToParent('picker:ready', {
            total: Number(root.dataset.driveTotal || 0),
            pageSize: Number(root.dataset.drivePageSize || 0),
        });
    }

    const computeShouldUseRemote = () => {
        const total = Number(root.dataset.driveTotal || '0');
        const pageSize = Number(root.dataset.drivePageSize || '0');
        return Boolean(root.dataset.driveSearchUrl) && pageSize > 0 && total > pageSize;
    };

    const updateUrl = (term) => {
        const params = new URLSearchParams(window.location.search);
        if (term) {
            params.set('q', term);
        } else {
            params.delete('q');
        }
        const query = params.toString();
        const nextUrl = `${window.location.pathname}${query ? `?${query}` : ''}`;
        window.history.replaceState({}, '', nextUrl);
    };

    const filterLocal = (term) => {
        if (!grid) return;
        const normalized = normalizeTerm(term);
        let visibleCount = 0;

        grid.querySelectorAll('[data-drive-row]').forEach((row) => {
            const haystack = row.dataset.search || '';
            const matches = normalized.length === 0 || haystack.includes(normalized);
            row.toggleAttribute('hidden', !matches);
            if (matches) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            toggleHidden(emptyState, visibleCount !== 0);
        }
    };

    const replaceContentFrom = (docRoot) => {
        const newGrid = docRoot.querySelector('[data-drive-grid]');
        if (grid && newGrid) {
            grid.replaceChildren(...newGrid.children);
        }

        const newEmpty = docRoot.querySelector('[data-drive-empty]');
        if (emptyState && newEmpty) {
            emptyState.replaceWith(newEmpty);
        } else if (!emptyState && newEmpty) {
            grid?.insertAdjacentElement('afterend', newEmpty);
        }

        const newPagination = docRoot.querySelector('[data-drive-pagination]');
        if (pagination) {
            if (newPagination) {
                pagination.replaceWith(newPagination);
            } else {
                pagination.remove();
            }
        } else if (newPagination) {
            root.appendChild(newPagination);
        }

        const newSummary = docRoot.querySelector('[data-drive-summary]');
        if (summary && newSummary) {
            summary.replaceWith(newSummary);
        } else if (!summary && newSummary) {
            root.insertBefore(newSummary, root.querySelector('[data-drive-grid]'));
        }

        const newStorage = docRoot.querySelector('[data-drive-storage]');
        const currentStorage = root.querySelector('[data-drive-storage]');
        if (newStorage) {
            if (currentStorage) {
                currentStorage.replaceWith(newStorage);
            } else {
                root.querySelector('[data-drive-tree]')?.appendChild(newStorage);
            }
        }

        if (docRoot.dataset.driveStorageLimit) {
            root.dataset.driveStorageLimit = docRoot.dataset.driveStorageLimit;
        }

        if (docRoot.dataset.driveStorageUsed) {
            root.dataset.driveStorageUsed = docRoot.dataset.driveStorageUsed;
        }

        refreshRefs();
        refreshTooltips(root);
        updateStorageMetrics(root);
    };

    const performRemoteSearch = async (term) => {
        const searchUrl = root.dataset.driveSearchUrl;
        if (!searchUrl) {
            filterLocal(term);
            return;
        }

        const params = new URLSearchParams(window.location.search);
        if (term) {
            params.set('q', term);
        } else {
            params.delete('q');
        }
        params.set('ajax', '1');

        root.classList.add('is-searching');

        try {
            const response = await fetch(`${searchUrl}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('İstek başarısız oldu');
            }

            const text = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newRoot = doc.querySelector('[data-drive-root]');
            if (!newRoot) {
                throw new Error('Geçersiz yanıt');
            }

            replaceContentFrom(newRoot);
            root.dataset.driveTotal = newRoot.dataset.driveTotal || root.dataset.driveTotal;
            root.dataset.drivePageSize = newRoot.dataset.drivePageSize || root.dataset.drivePageSize;
            root.dataset.driveActiveTab = newRoot.dataset.driveActiveTab || root.dataset.driveActiveTab;
            filterLocal(term);
        } catch (error) {
            console.error('Arama isteği başarısız oldu.', error);
            filterLocal(term);
        } finally {
            root.classList.remove('is-searching');
        }
    };

    const searchScope = root.querySelector('[data-drive-search]');
    initLiveSearch(searchScope, {
        onTermChange: (term) => {
            currentSearchTerm = term;
            updateUrl(term);
        },
        shouldUseRemote: (term) => computeShouldUseRemote() && term.length > 0,
        onLocal: (term) => filterLocal(term),
        onRemote: (term) => performRemoteSearch(term),
    });

    filterLocal(currentSearchTerm);

    const uploadPanel = root.querySelector('[data-drive-upload-panel]');
    const dropzone = root.querySelector('[data-drive-dropzone]');
    const fileInput = root.querySelector('[data-drive-file-input]');
    const categorySelect = root.querySelector('[data-drive-category-select] select, [data-drive-category-select]');
    const moduleSelect = root.querySelector('[data-drive-module-select] select, [data-drive-module-select]');
    const categoryNote = root.querySelector('[data-drive-category-note]');
    const progressHost = root.querySelector('[data-drive-progress-items]');
    const progressWrapper = root.querySelector('[data-drive-progress]');
    const moduleDefault = root.dataset.moduleActive || root.dataset.moduleDefault || 'cms';

    const limits = (() => {
        try {
            return JSON.parse(root.dataset.categoryLimits || '{}');
        } catch (error) {
            return {};
        }
    })();

    const applyCategoryNote = () => {
        if (!categoryNote) return;
        const key = categorySelect?.value ?? root.dataset.categoryDefault;
        const limit = limits[key];
        if (!limit) {
            categoryNote.textContent = '';
            toggleHidden(categoryNote, true);
            return;
        }

        const parts = [limit.mimes ? `Kabul edilen uzantılar: ${limit.mimes}` : '', limit.max ? `Maks ${limit.max}` : '']
            .filter(Boolean)
            .join(' · ');
        categoryNote.textContent = parts;
        toggleHidden(categoryNote, parts.length === 0);
    };

    applyCategoryNote();
    categorySelect?.addEventListener('change', applyCategoryNote);
    moduleSelect?.addEventListener('change', () => {
        root.dataset.moduleActive = moduleSelect.value || moduleDefault;
    });

    const ensureProgress = () => {
        if (progressWrapper) {
            toggleHidden(progressWrapper, false);
        }
    };

    const clearProgress = () => {
        if (progressHost) {
            progressHost.innerHTML = '';
        }
        if (progressWrapper) {
            toggleHidden(progressWrapper, true);
        }
    };

    root.querySelectorAll('[data-action="drive-clear-progress"]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            clearProgress();
        });
    });

    const uploadFile = async (file) => {
        if (!file || !progressHost) return;
        const item = createProgressItem(file);
        ensureProgress();
        progressHost.appendChild(item.element);

        try {
            const formData = new FormData();
            const categoryValue = categorySelect?.value ?? root.dataset.categoryDefault;
            const moduleValue = moduleSelect?.value || moduleDefault;
            formData.append('category', categoryValue);
            formData.append('module', moduleValue);
            formData.append('files[]', file);

            const { data } = await axios.post(root.dataset.uploadManyUrl, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (event) => {
                    if (!event.total) return;
                    item.setProgress((event.loaded / event.total) * 100);
                    item.setStatus('Yükleniyor…');
                },
            });

            item.markDone();
            if (data?.uploaded?.length) {
                data.uploaded.forEach((media) => insertMedia(root, media, currentSearchTerm));
                filterLocal(currentSearchTerm);
            }
        } catch (error) {
            const message = error?.response?.data?.message || 'Yükleme başarısız oldu.';
            item.markError(message);
        }
    };

    const handleFiles = (files) => {
        const items = Array.from(files).slice(0, 10);
        if (!items.length) return;
        items.forEach(uploadFile);
    };

    fileInput?.addEventListener('change', (event) => {
        handleFiles(event.target.files ?? []);
        event.target.value = '';
    });

    if (dropzone) {
        ['dragenter', 'dragover'].forEach((type) => {
            dropzone.addEventListener(type, (event) => {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });
        });

        ['dragleave', 'dragend'].forEach((type) => {
            dropzone.addEventListener(type, () => {
                dropzone.classList.remove('is-dragover');
            });
        });

        dropzone.addEventListener('drop', (event) => {
            event.preventDefault();
            dropzone.classList.remove('is-dragover');
            if (event.dataTransfer?.files) {
                handleFiles(event.dataTransfer.files);
            }
        });

        dropzone.addEventListener('click', () => {
            fileInput?.click();
        });
    }

    const openUpload = () => {
        if (!uploadPanel) return;
        uploadPanel.removeAttribute('hidden');
        uploadPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    root.querySelectorAll('[data-action="drive-open-upload"]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            openUpload();
        });
    });

    root.querySelectorAll('[data-action="drive-close-upload"]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            uploadPanel?.setAttribute('hidden', '');
        });
    });

    const replaceModal = document.getElementById('driveReplaceModal');
    const replaceInput = replaceModal?.querySelector('[data-drive-replace-input]');
    const replaceError = replaceModal?.querySelector('[data-drive-replace-error]');
    const replaceSummary = replaceModal?.querySelector('[data-drive-replace-name]');
    let replacingId = null;

    const openReplace = (id, name) => {
        replacingId = id;
        if (replaceSummary) {
            replaceSummary.textContent = `${name} dosyasını değiştirin.`;
        }
        if (replaceError) {
            toggleHidden(replaceError, true);
            replaceError.textContent = '';
        }
        if (replaceInput) {
            replaceInput.value = '';
        }
        bus.emit('ui:modal:open', { id: 'driveReplaceModal', source: root });
    };

    const submitReplace = async () => {
        if (!replacingId || !replaceInput?.files?.length) {
            if (replaceError) {
                replaceError.textContent = 'Lütfen yüklemek için bir dosya seçin.';
                toggleHidden(replaceError, false);
            }
            return;
        }

        const file = replaceInput.files[0];
        const formData = new FormData();
        formData.append('file', file);

        try {
            const { data } = await axios.post(
                root.dataset.replaceUrlTemplate.replace('__ID__', replacingId),
                formData,
                {
                    headers: { 'Content-Type': 'multipart/form-data' },
                },
            );

            if (data?.media) {
                replaceMedia(root, data.media, currentSearchTerm);
                filterLocal(currentSearchTerm);
            }

            bus.emit('ui:modal:close', { id: 'driveReplaceModal' });
        } catch (error) {
            const response = error?.response?.data;
            const message = response?.message || 'Dosya değiştirme sırasında bir hata oluştu.';
            if (replaceError) {
                replaceError.textContent = message;
                toggleHidden(replaceError, false);
            }
        }
    };

    replaceModal?.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        if (button.dataset.action === 'drive-submit-replace') {
            event.preventDefault();
            await submitReplace();
        }
    });

    let pendingDelete = null;
    const confirmModal = document.getElementById('driveDeleteConfirm');
    confirmModal?.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-intent]');
        if (!button) return;
        if (button.dataset.intent === 'confirm' && pendingDelete) {
            const { id, url } = pendingDelete;
            try {
                await axios.delete(url, {
                    headers: { Accept: 'application/json' },
                });
                removeMedia(root, id);
                filterLocal(currentSearchTerm);
            } catch (error) {
                console.error('Dosya silinemedi.', error);
            } finally {
                pendingDelete = null;
            }
        }
        if (button.dataset.intent === 'cancel') {
            pendingDelete = null;
        }
    });

    root.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) return;

        const action = button.dataset.action;

        if (action === 'drive-toggle-important') {
            event.preventDefault();
            const url = button.dataset.url;
            const row = button.closest('[data-drive-row]');
            if (!url || !row) return;
            const id = row.dataset.id;

            button.disabled = true;
            try {
                const { data } = await axios.post(url, null, {
                    headers: { Accept: 'application/json' },
                });
                if (data?.ok) {
                    updateImportantState(root, id, data.is_important);
                }
            } catch (error) {
                console.error('Önemli durum güncellenemedi.', error);
            } finally {
                button.disabled = false;
            }
        }

        if (action === 'drive-open-replace') {
            event.preventDefault();
            if (isPickerMode) return;
            const id = button.dataset.id;
            const name = button.dataset.name || 'Seçilen dosya';
            if (!id) return;
            openReplace(id, name);
        }

        if (action === 'drive-picker-select') {
            if (!isPickerMode) {
                return;
            }

            event.preventDefault();

            const row = button.closest('[data-drive-row]');
            if (!row) {
                return;
            }

            const payload = {
                id: Number(button.dataset.id || row.dataset.id || 0),
                name: button.dataset.name || row.dataset.originalName || '',
                original_name: button.dataset.name || row.dataset.originalName || '',
                ext: normalizeExt(button.dataset.ext || row.dataset.ext || ''),
                mime: button.dataset.mime || row.dataset.mime || '',
                size: Number(button.dataset.size || row.dataset.size || 0),
                path: button.dataset.path || row.dataset.path || '',
                url: button.dataset.url || row.dataset.downloadUrl || '',
                download_url: button.dataset.url || row.dataset.downloadUrl || '',
                category: row.dataset.category || '',
                module: row.dataset.module || '',
            };

            postToParent('picker:selected', { file: payload });
        }

        if (action === 'drive-delete') {
            event.preventDefault();
            const id = button.dataset.id;
            const url = button.dataset.url;
            if (!id || !url) return;
            pendingDelete = { id, url };
            bus.emit('ui:modal:open', { id: 'driveDeleteConfirm', source: button });
        }
    });
};

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            bootDrive();
        }, { once: true });
    } else {
        bootDrive();
    }
}

export default bootDrive;
