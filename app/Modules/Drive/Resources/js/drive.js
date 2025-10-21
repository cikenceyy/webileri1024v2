import axios from 'axios';
import { bus } from '@/js/admin-runtime.js';

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const setCookie = (name, value, days = 365) => {
    const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
};

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

const resolveCategoryNote = (limits, category) => {
    if (!category || !limits[category]) {
        return '';
    }

    const { mimes, max } = limits[category];
    const mimeText = mimes ? `Kabul edilen uzantılar: ${mimes}` : '';
    const sizeText = max ? `Maks ${max}` : '';

    return [mimeText, sizeText].filter(Boolean).join(' · ');
};

const updateImportantState = (root, id, isImportant) => {
    const rows = root.querySelectorAll(`[data-drive-row][data-id="${id}"]`);
    rows.forEach((row) => {
        row.dataset.important = isImportant ? '1' : '0';
        row.classList.toggle('is-important', Boolean(isImportant));
        row.querySelectorAll('[data-drive-important-badge]').forEach((badge) => {
            if (isImportant) {
                badge.removeAttribute('hidden');
            } else {
                badge.setAttribute('hidden', '');
            }
        });
        row.querySelectorAll('[data-action="drive-toggle-important"]').forEach((button) => {
            button.classList.toggle('is-on', Boolean(isImportant));
            button.setAttribute('aria-pressed', isImportant ? 'true' : 'false');
        });
    });
};

const bootDrive = () => {
    const root = document.querySelector('[data-drive-root]');
    if (!root) {
        return;
    }

    const csrfToken = getCsrfToken();
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    const pickerMode = root.dataset.pickerMode === '1';
    const uploadPanel = root.querySelector('[data-drive-upload-panel]');
    const dropzone = root.querySelector('[data-drive-dropzone]');
    const fileInput = root.querySelector('[data-drive-file-input]');
    const categorySelect = root.querySelector('[data-drive-category-select]');
    const categoryNote = root.querySelector('[data-drive-category-note]');
    const progressHost = root.querySelector('[data-drive-progress-items]');
    const progressWrapper = root.querySelector('[data-drive-progress]');
    const limits = (() => {
        try {
            return JSON.parse(root.dataset.categoryLimits || '{}');
        } catch (error) {
            return {};
        }
    })();

    const applyCategoryNote = () => {
        if (!categoryNote) return;
        const note = resolveCategoryNote(limits, categorySelect?.value);
        categoryNote.textContent = note;
        toggleHidden(categoryNote, !note);
    };

    applyCategoryNote();
    categorySelect?.addEventListener('change', applyCategoryNote);

    const setView = (next) => {
        const mode = next === 'list' ? 'list' : 'grid';
        root.dataset.driveMode = mode;
        setCookie('drive_view', mode);
        root.querySelectorAll('[data-drive-view-control]').forEach((button) => {
            const current = button.dataset.driveViewControl;
            button.classList.toggle('is-active', current === mode);
            button.setAttribute('aria-pressed', current === mode ? 'true' : 'false');
        });
    };

    setView(root.dataset.driveMode || root.dataset.driveViewPreference || 'grid');

    root.querySelectorAll('[data-drive-view-control]').forEach((button) => {
        button.addEventListener('click', () => {
            setView(button.dataset.driveViewControl);
        });
    });

    const filtersPanel = root.querySelector('[data-drive-filters]');
    const filtersToggle = root.querySelector('[data-drive-filter-toggle]');
    filtersToggle?.addEventListener('click', () => {
        const isHidden = filtersPanel?.hasAttribute('hidden');
        toggleHidden(filtersPanel, !isHidden);
        filtersToggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    });

    const openUpload = () => {
        if (!uploadPanel) return;
        uploadPanel.removeAttribute('hidden');
        uploadPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
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

    const ensureProgress = () => {
        if (!progressWrapper) return;
        toggleHidden(progressWrapper, false);
    };

    const clearProgress = () => {
        if (!progressHost) return;
        progressHost.innerHTML = '';
        toggleHidden(progressWrapper, true);
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
            formData.append('category', categoryValue);
            formData.append('files[]', file);

            await axios.post(root.dataset.uploadManyUrl, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                onUploadProgress: (event) => {
                    if (!event.total) return;
                    item.setProgress((event.loaded / event.total) * 100);
                    item.setStatus('Yükleniyor…');
                },
            });

            item.markDone();
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
            await axios.post(root.dataset.replaceUrlTemplate.replace('__ID__', replacingId), formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
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
                const { data } = await axios.post(url);
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
            if (pickerMode) return;
            const id = button.dataset.id;
            const name = button.dataset.name || 'Seçilen dosya';
            if (!id) return;
            openReplace(id, name);
        }

        if (action === 'drive-submit-replace') {
            event.preventDefault();
            await submitReplace();
        }

        if (action === 'drive-picker-select') {
            if (!pickerMode) return;
            event.preventDefault();
            const detail = {
                id: button.dataset.id,
                name: button.dataset.name,
                ext: button.dataset.ext,
                mime: button.dataset.mime,
                size: Number(button.dataset.size || 0),
            };
            root.dispatchEvent(new CustomEvent('drive:picker-select', { detail }));
            window?.parent?.postMessage({ type: 'drive:picker-select', detail }, '*');
        }
    });
};

export default bootDrive;
