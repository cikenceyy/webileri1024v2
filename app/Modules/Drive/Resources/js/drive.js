import axios from 'axios';
import { bus } from '@/js/admin-runtime.js';

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

const updateImportantState = (root, id, isImportant) => {
    const rows = root.querySelectorAll(`[data-drive-row][data-id="${id}"]`);
    rows.forEach((row) => {
        row.dataset.important = isImportant ? '1' : '0';
        row.classList.toggle('drive-file--important', Boolean(isImportant));
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

const debounce = (fn, delay = 150) => {
    let timer = null;
    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => fn(...args), delay);
    };
};

const normalizeTerm = (value) => (value || '').toString().trim().toLowerCase();

const bootDrive = () => {
    const root = document.querySelector('[data-drive-root]');
    if (!root) {
        return;
    }

    const csrfToken = getCsrfToken();
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }

    let grid = root.querySelector('[data-drive-grid]');
    let emptyState = root.querySelector('[data-drive-empty]');
    let pagination = root.querySelector('[data-drive-pagination]');
    let tree = root.querySelector('[data-drive-tree]');
    const searchForm = root.querySelector('[data-drive-search-form]');
    const searchInput = root.querySelector('[data-drive-search-input]');
    const searchUrl = root.dataset.driveSearchUrl;

    const refreshRefs = () => {
        grid = root.querySelector('[data-drive-grid]');
        emptyState = root.querySelector('[data-drive-empty]');
        pagination = root.querySelector('[data-drive-pagination]');
        tree = root.querySelector('[data-drive-tree]');
    };

    const computeShouldUseRemote = () => {
        const total = Number(root.dataset.driveTotal || '0');
        const pageSize = Number(root.dataset.drivePageSize || '0');
        return Boolean(searchUrl) && pageSize > 0 && total > pageSize;
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

    const initTree = () => {
        if (!tree) return;
        tree.querySelectorAll('[data-drive-tree-item]').forEach((item) => {
            const toggle = item.querySelector('[data-drive-tree-toggle]');
            const panel = item.querySelector('[data-drive-tree-panel]');
            if (!toggle || !panel) {
                return;
            }

            let expanded = toggle.getAttribute('aria-expanded') !== 'false';
            const applyState = (next) => {
                expanded = next;
                toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                panel.hidden = !expanded;
                panel.setAttribute('aria-hidden', expanded ? 'false' : 'true');
                item.classList.toggle('is-collapsed', !expanded);
            };

            applyState(expanded);

            const toggleState = () => applyState(!expanded);

            toggle.addEventListener('click', () => toggleState());
            toggle.addEventListener('keydown', (event) => {
                if (event.key === ' ' || event.key === 'Enter') {
                    event.preventDefault();
                    toggleState();
                }
            });
        });
    };

    initTree();

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
        }

        const newPagination = docRoot.querySelector('[data-drive-pagination]');
        if (pagination) {
            if (newPagination) {
                pagination.replaceWith(newPagination);
            } else {
                pagination.innerHTML = '';
                pagination.setAttribute('hidden', 'true');
            }
        } else if (newPagination) {
            root.querySelector('.drive__content')?.appendChild(newPagination);
        }

        const newTree = docRoot.querySelector('[data-drive-tree]');
        if (tree && newTree) {
            tree.innerHTML = newTree.innerHTML;
        }

        refreshRefs();
        initTree();
    };

    const performRemoteSearch = async (term) => {
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

    const runSearch = (term) => {
        const normalized = normalizeTerm(term);
        updateUrl(normalized);
        if (computeShouldUseRemote() && normalized.length > 0) {
            performRemoteSearch(normalized);
        } else {
            filterLocal(normalized);
        }
    };

    if (searchForm) {
        searchForm.addEventListener('submit', (event) => {
            event.preventDefault();
            runSearch(searchInput?.value ?? '');
        });
    }

    if (searchInput) {
        const debounced = debounce(() => runSearch(searchInput.value), 150);
        searchInput.addEventListener('input', () => {
            debounced();
        });
    }

    const uploadPanel = root.querySelector('[data-drive-upload-panel]');
    const dropzone = root.querySelector('[data-drive-dropzone]');
    const fileInput = root.querySelector('[data-drive-file-input]');
    const categorySelect = root.querySelector('[data-drive-category-select] select, [data-drive-category-select]');
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
            if (root.dataset.pickerMode === '1') return;
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
            if (root.dataset.pickerMode !== '1') return;
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
