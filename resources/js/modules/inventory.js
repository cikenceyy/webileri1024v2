import bus from '../lib/bus.js';

const formatBytes = (value) => {
    const bytes = Number(value) || 0;
    if (bytes >= 1024 ** 3) return `${(bytes / 1024 ** 3).toFixed(2)} GB`;
    if (bytes >= 1024 ** 2) return `${(bytes / 1024 ** 2).toFixed(1)} MB`;
    if (bytes >= 1024) return `${(bytes / 1024).toFixed(0)} KB`;
    return `${bytes} B`;
};

const initProductMediaPicker = () => {
    const form = document.querySelector('[data-inventory-product-form]');
    if (!form) return;

    const modalId = form.dataset.driveModalId || 'drivePickerModal';
    const pickerButtons = form.querySelectorAll('[data-action="open-drive-picker"]');
    const clearButton = form.querySelector('[data-action="clear-media"]');
    const input = form.querySelector('[data-product-media-input]');
    const preview = form.querySelector('[data-product-media-preview]');
    const emptyMessage = preview?.dataset.emptyMessage ?? preview?.textContent?.trim() ?? '';

    const renderPreview = (payload) => {
        if (!preview) return;
        if (!payload) {
            preview.dataset.state = 'empty';
            preview.innerHTML = `<div class="inventory-media-empty">${emptyMessage}</div>`;
            return;
        }

        const name = payload.name ?? '—';
        const ext = (payload.ext ?? '').toString().toUpperCase();
        const mime = payload.mime ?? '';
        const size = formatBytes(payload.size);

        preview.dataset.state = 'filled';
        preview.innerHTML = `
            <div class="inventory-media-preview">
                <div class="inventory-media-preview__icon" aria-hidden="true">${ext || 'FILE'}</div>
                <div class="inventory-media-preview__meta">
                    <div class="inventory-media-preview__name" title="${name}">${name}</div>
                    <div class="inventory-media-preview__desc">${[mime, size].filter(Boolean).join(' · ')}</div>
                </div>
            </div>
        `;
    };

    pickerButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            bus.emit('ui:modal:open', { id: modalId, source: button });
        });
    });

    clearButton?.addEventListener('click', (event) => {
        event.preventDefault();
        if (input) {
            input.value = '';
        }
        renderPreview(null);
    });

    window.addEventListener('message', (event) => {
        if (!preview || !input) return;
        if (event.origin !== window.location.origin) return;
        if (!event.data || event.data.type !== 'drive:select') return;

        const payload = event.data.payload ?? {};
        if (payload.id) {
            input.value = payload.id;
        }
        renderPreview(payload);
        bus.emit('ui:modal:close', { id: modalId });
    });
};

const initVariantOptions = () => {
    const container = document.querySelector('[data-variant-options]');
    if (!container) return;

    const template = container.querySelector('[data-variant-option-template]');
    const list = container.querySelector('[data-variant-option-list]') ?? container;

    const createRow = () => {
        if (template?.content) {
            return template.content.firstElementChild?.cloneNode(true);
        }

        const lastRow = list.querySelector('[data-variant-option-row]:last-child');
        return lastRow ? lastRow.cloneNode(true) : null;
    };

    const insertRow = (row) => {
        if (!row) return;
        if (template && list) {
            list.appendChild(row);
            return;
        }
        const actions = container.querySelector('[data-variant-option-actions]');
        if (actions?.parentElement === container) {
            container.insertBefore(row, actions);
            return;
        }
        list.appendChild(row);
    };

    container.addEventListener('click', (event) => {
        const addTrigger = event.target.closest('[data-action="add-variant-option"]');
        if (addTrigger) {
            event.preventDefault();
            const row = createRow();
            if (!row) return;
            row.querySelectorAll('input').forEach((input) => {
                input.value = '';
            });
            insertRow(row);
            return;
        }

        const removeTrigger = event.target.closest('[data-action="remove-variant-option"]');
        if (removeTrigger) {
            event.preventDefault();
            const rows = list.querySelectorAll('[data-variant-option-row]');
            if (rows.length <= 1) {
                rows[0].querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
                return;
            }
            removeTrigger.closest('[data-variant-option-row]')?.remove();
        }
    });
};

const markActiveFilters = () => {
    const filterBlocks = document.querySelectorAll('[data-inventory-filters]');
    filterBlocks.forEach((card) => {
        const form = card.matches('form') ? card : card.querySelector('form');
        if (!form) return;

        const update = () => {
            const hasValue = Array.from(form.elements).some((element) => {
                if (!(element instanceof HTMLInputElement || element instanceof HTMLSelectElement || element instanceof HTMLTextAreaElement)) {
                    return false;
                }
                if (element.type === 'checkbox' || element.type === 'radio') {
                    return element.checked;
                }
                return element.value && element.value.trim() !== '';
            });
            card.dataset.state = hasValue ? 'filtered' : 'idle';
        };

        form.addEventListener('input', () => update());
        form.addEventListener('change', () => update());
        update();
    });
};

export const initInventoryModule = () => {
    if (document.body.dataset.module !== 'inventory') return;

    initProductMediaPicker();
    initVariantOptions();
    markActiveFilters();
};
