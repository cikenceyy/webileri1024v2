import bus from '../lib/bus.js';

const parseJSON = (value, fallback = {}) => {
    try {
        return value ? JSON.parse(value) : fallback;
    } catch (error) {
        console.warn('Drive: JSON parse hatası', error);
        return fallback;
    }
};

const formatBytes = (bytes) => {
    if (!bytes || Number.isNaN(bytes)) {
        return '0 B';
    }

    if (bytes >= 1024 * 1024 * 1024) {
        return `${(bytes / (1024 * 1024 * 1024)).toFixed(2)} GB`;
    }

    if (bytes >= 1024 * 1024) {
        return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    }

    if (bytes >= 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${bytes} B`;
};

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const createProgressItem = (file, container) => {
    const element = document.createElement('div');
    element.className = 'drive-progress-item';
    element.innerHTML = `
        <div class="drive-progress-item__meta">
            <span class="drive-progress-item__name" title="${file.name}">${file.name}</span>
            <span class="drive-progress-item__size">${formatBytes(file.size)}</span>
        </div>
        <div class="drive-progress-item__bar">
            <div class="drive-progress-item__bar-fill" style="width:0%"></div>
        </div>
        <div class="drive-progress-item__status">Hazırlanıyor…</div>
    `;

    const fill = element.querySelector('.drive-progress-item__bar-fill');
    const status = element.querySelector('.drive-progress-item__status');

    const updateProgress = (value) => {
        if (!fill) return;
        const width = Math.max(0, Math.min(100, value));
        fill.style.width = `${width}%`;
        if (status) {
            status.textContent = `Yükleniyor… %${width.toFixed(0)}`;
        }
    };

    const markSuccess = (message) => {
        element.classList.remove('is-error');
        element.classList.add('is-success');
        if (status) {
            status.textContent = message ?? 'Yükleme tamamlandı.';
        }
    };

    const markError = (message) => {
        element.classList.remove('is-success');
        element.classList.add('is-error');
        if (status) {
            status.textContent = message ?? 'Yükleme sırasında bir hata oluştu.';
        }
    };

    container.appendChild(element);

    return { element, updateProgress, markSuccess, markError };
};

const updateImportantUI = (mediaId, isImportant) => {
    const rows = document.querySelectorAll(`[data-drive-row][data-id="${mediaId}"]`);
    rows.forEach((row) => {
        row.dataset.important = isImportant ? '1' : '0';
        row.classList.toggle('is-important', Boolean(isImportant));
        row.querySelectorAll('[data-drive-important-badge]').forEach((badge) => {
            badge.hidden = !isImportant;
        });
        row.querySelectorAll('[data-action="drive-toggle-important"]').forEach((button) => {
            button.classList.toggle('is-on', Boolean(isImportant));
            button.setAttribute('aria-pressed', isImportant ? 'true' : 'false');
        });
    });
};

const initDrivePage = () => {
    const root = document.querySelector('[data-drive-root]');
    if (!root) return;

    const csrfToken = getCsrfToken();
    const pickerMode = root.dataset.pickerMode === '1';
    const uploadUrl = root.dataset.uploadUrl;
    const replaceUrlTemplate = root.dataset.replaceUrlTemplate;
    const categoryLimits = parseJSON(root.dataset.categoryLimits, {});
    const maxFiles = 10;

    let currentCategory = root.dataset.categoryDefault;
    let replaceContext = { id: null, name: null };

    const uploadPanel = root.querySelector('[data-drive-upload-panel]');
    const dropContainer = root.querySelector('.drive-upload__drop');
    const fileInput = root.querySelector('[data-drive-file-input]');
    const categorySelect = root.querySelector('[data-drive-category-select] select');
    const categoryNote = root.querySelector('[data-drive-category-note]');
    const progressList = root.querySelector('[data-drive-progress-list]');
    const progressItemsContainer = root.querySelector('[data-drive-progress-items]');
    const replaceModal = document.getElementById('driveReplaceModal');
    const replaceInputField = replaceModal?.querySelector('[data-drive-replace-input]');
    const replaceName = replaceModal?.querySelector('[data-drive-replace-name]');
    const replaceError = replaceModal?.querySelector('[data-drive-replace-error]');
    const replaceButton = replaceModal?.querySelector('[data-action="drive-submit-replace"]');

    const showToast = ({ title, message, variant = 'info' }) => {
        bus.emit('ui:toast:show', { title, message, variant });
    };

    const updateCategoryNote = (category) => {
        if (!categoryNote) return;
        const config = categoryLimits?.[category];
        if (config) {
            categoryNote.textContent = `Kabul edilen uzantılar: ${config.mimes} · Maks ${config.max}`;
            categoryNote.hidden = false;
        } else {
            categoryNote.textContent = '';
            categoryNote.hidden = true;
        }
    };

    if (categorySelect) {
        categorySelect.addEventListener('change', () => {
            currentCategory = categorySelect.value;
            updateCategoryNote(currentCategory);
        });
        updateCategoryNote(currentCategory);
    }

    const clearProgress = () => {
        if (progressItemsContainer) {
            progressItemsContainer.innerHTML = '';
        }
        if (progressList) {
            progressList.hidden = true;
        }
    };

    const handleUploadResponse = (response) => {
        if (!response) {
            return { ok: false, message: 'Beklenmeyen yanıt.' };
        }

        if (response.ok) {
            return { ok: true, message: 'Dosya yüklendi.' };
        }

        if (response.errors) {
            const firstError = Object.values(response.errors)[0];
            if (Array.isArray(firstError)) {
                return { ok: false, message: firstError[0] };
            }
        }

        return { ok: false, message: response.message ?? 'Yükleme sırasında bir hata oluştu.' };
    };

    const uploadFile = (file) => {
        if (!uploadUrl || !csrfToken) {
            return Promise.resolve({ ok: false, message: 'Yükleme yapılandırması eksik.' });
        }

        if (!progressItemsContainer) {
            return Promise.resolve({ ok: false });
        }

        const item = createProgressItem(file, progressItemsContainer);
        progressList?.removeAttribute('hidden');

        return new Promise((resolve) => {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('category', currentCategory ?? 'documents');
            formData.append('file', file);

            xhr.upload.addEventListener('progress', (event) => {
                if (!event.lengthComputable) return;
                const percent = (event.loaded / event.total) * 100;
                item.updateProgress(percent);
            });

            xhr.addEventListener('load', () => {
                let payload = null;
                try {
                    payload = xhr.responseText ? JSON.parse(xhr.responseText) : null;
                } catch (error) {
                    payload = null;
                }

                const { ok, message } = handleUploadResponse(payload);
                if (ok) {
                    item.updateProgress(100);
                    item.markSuccess(message);
                    resolve({ ok: true });
                } else {
                    item.markError(message);
                    resolve({ ok: false, message });
                }
            });

            xhr.addEventListener('error', () => {
                item.markError('Ağ bağlantısı başarısız oldu.');
                resolve({ ok: false, message: 'Ağ bağlantısı başarısız oldu.' });
            });

            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        });
    };

    const handleFiles = (files) => {
        if (!files?.length) {
            return;
        }

        const items = Array.from(files).filter((file) => file instanceof File);
        if (!items.length) {
            return;
        }

        if (items.length > maxFiles) {
            showToast({
                title: 'Dosya sınırı',
                message: `Aynı anda en fazla ${maxFiles} dosya yüklenebilir. İlk ${maxFiles} dosya işlenecek.`,
                variant: 'warning',
            });
        }

        const queue = items.slice(0, maxFiles);
        const uploads = queue.map((file) => uploadFile(file));

        Promise.all(uploads).then((results) => {
            const hasSuccess = results.some((result) => result.ok);
            const hasFailure = results.some((result) => !result.ok);

            if (hasSuccess) {
                showToast({ title: 'Yükleme tamamlandı', message: 'Liste güncelleniyor…', variant: 'success' });
                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } else if (hasFailure) {
                showToast({ title: 'Yükleme başarısız', message: results[0]?.message ?? 'Dosyalar yüklenemedi.', variant: 'danger' });
            }
        });
    };

    if (!pickerMode && dropContainer && fileInput) {
        dropContainer.addEventListener('click', (event) => {
            if (event.target === dropContainer) {
                fileInput.click();
            }
        });

        dropContainer.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                fileInput.click();
            }
        });

        ['dragenter', 'dragover'].forEach((type) => {
            dropContainer.addEventListener(type, (event) => {
                event.preventDefault();
                dropContainer.classList.add('is-active');
            });
        });

        ['dragleave', 'dragend'].forEach((type) => {
            dropContainer.addEventListener(type, (event) => {
                if (event instanceof DragEvent) {
                    const related = event.relatedTarget;
                    if (related && dropContainer.contains(related)) {
                        return;
                    }
                }
                dropContainer.classList.remove('is-active');
            });
        });

        dropContainer.addEventListener('drop', (event) => {
            event.preventDefault();
            dropContainer.classList.remove('is-active');
            handleFiles(event.dataTransfer?.files ?? []);
        });

        fileInput.addEventListener('change', () => {
            handleFiles(fileInput.files ?? []);
            fileInput.value = '';
        });
    }

    root.addEventListener('click', (event) => {
        const control = event.target.closest('[data-action]');
        if (!control) return;

        const action = control.dataset.action;

        if (action === 'drive-open-upload' && uploadPanel && !pickerMode) {
            event.preventDefault();
            uploadPanel.hidden = false;
            uploadPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (action === 'drive-close-upload' && uploadPanel) {
            event.preventDefault();
            uploadPanel.hidden = true;
            clearProgress();
        }

        if (action === 'drive-clear-progress') {
            event.preventDefault();
            clearProgress();
        }

        if (action === 'drive-toggle-important') {
            event.preventDefault();
            if (!csrfToken) {
                showToast({ title: 'Yetki hatası', message: 'Oturum doğrulaması bulunamadı.', variant: 'danger' });
                return;
            }

            const url = control.dataset.url;
            const mediaId = control.closest('[data-drive-row]')?.dataset.id;
            if (!url || !mediaId || control.classList.contains('is-busy')) {
                return;
            }

            control.classList.add('is-busy');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => null);
                    if (!response.ok || !payload?.ok) {
                        throw new Error(payload?.message ?? 'İşaretleme işlemi başarısız oldu.');
                    }

                    updateImportantUI(mediaId, Boolean(payload.is_important));
                    showToast({
                        title: payload.is_important ? 'Favorilere eklendi' : 'Favoriden çıkarıldı',
                        message: payload.is_important
                            ? 'Dosya önemli olarak işaretlendi.'
                            : 'Dosya önemli listesinden çıkarıldı.',
                        variant: 'success',
                    });
                })
                .catch((error) => {
                    console.error(error);
                    showToast({ title: 'İşlem başarısız', message: error.message, variant: 'danger' });
                })
                .finally(() => {
                    control.classList.remove('is-busy');
                });
        }

        if (action === 'drive-open-replace' && !pickerMode && replaceModal) {
            event.preventDefault();
            replaceContext = { id: control.dataset.id ?? null, name: control.dataset.name ?? null };
            if (replaceName) {
                replaceName.textContent = replaceContext.name
                    ? `Seçilen dosya: ${replaceContext.name}`
                    : 'Yeni dosya seçin.';
            }
            if (replaceError) {
                replaceError.textContent = '';
                replaceError.hidden = true;
            }
            const inputElement = replaceInputField?.querySelector('input[type="file"]');
            if (inputElement) {
                inputElement.value = '';
            }
            bus.emit('ui:modal:open', { id: 'driveReplaceModal', source: control });
        }
    });

    if (replaceButton && !pickerMode) {
        replaceButton.addEventListener('click', (event) => {
            event.preventDefault();
            if (!replaceContext?.id) {
                return;
            }

            const fileField = replaceInputField?.querySelector('input[type="file"]');
            const file = fileField?.files?.[0];

            if (!file) {
                if (replaceError) {
                    replaceError.textContent = 'Lütfen yüklemek için bir dosya seçin.';
                    replaceError.hidden = false;
                }
                return;
            }

            if (!csrfToken) {
                showToast({ title: 'Yetki hatası', message: 'Oturum doğrulaması bulunamadı.', variant: 'danger' });
                return;
            }

            const url = replaceUrlTemplate?.replace('__ID__', replaceContext.id);
            if (!url) {
                showToast({ title: 'İşlem başarısız', message: 'Değiştirme adresi bulunamadı.', variant: 'danger' });
                return;
            }

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('file', file);

            const originalLabel = replaceButton.textContent;
            replaceButton.disabled = true;
            replaceButton.textContent = 'Yükleniyor…';
            replaceError && (replaceError.hidden = true);

            fetch(url, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: formData,
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => null);
                    if (!response.ok || !payload?.ok) {
                        const message = payload?.message ?? 'Dosya değiştirme işlemi başarısız oldu.';
                        throw new Error(message);
                    }

                    showToast({ title: 'Dosya güncellendi', message: 'Sayfa yenileniyor…', variant: 'success' });
                    bus.emit('ui:modal:close', { id: 'driveReplaceModal' });
                    setTimeout(() => window.location.reload(), 1200);
                })
                .catch((error) => {
                    if (replaceError) {
                        replaceError.textContent = error.message;
                        replaceError.hidden = false;
                    }
                    showToast({ title: 'İşlem başarısız', message: error.message, variant: 'danger' });
                })
                .finally(() => {
                    replaceButton.disabled = false;
                    replaceButton.textContent = originalLabel ?? 'Değiştir';
                });
        });
    }
};

const initDrivePickerFrame = () => {
    if (!window.parent || window.parent === window) {
        return;
    }

    const pickerButtons = document.querySelectorAll('[data-action="drive-picker-select"]');
    if (!pickerButtons.length) {
        return;
    }

    pickerButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const payload = {
                id: button.dataset.id,
                name: button.dataset.name,
                ext: button.dataset.ext,
                mime: button.dataset.mime,
                size: Number(button.dataset.size ?? 0),
            };

            window.parent.postMessage({ type: 'drive:select', payload }, window.location.origin);
        });
    });
};

export const initDriveModule = () => {
    initDrivePage();
    initDrivePickerFrame();
};
