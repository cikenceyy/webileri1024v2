const debounce = (fn, delay = 0) => {
    let timer;

    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => {
            fn.apply(null, args);
        }, delay);
    };
};

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-cms-editor]');
    const stateEl = document.getElementById('cms-editor-state');
    if (!root || !stateEl) {
        return;
    }

    const config = JSON.parse(stateEl.textContent || '{}');
    const form = root.querySelector('[data-editor-form]');
    const iframe = root.querySelector('[data-editor-canvas]');
    const dirtyBadge = root.querySelector('[data-editor-dirty]');
    const saveButton = root.querySelector('[data-editor-save]');
    const discardButton = root.querySelector('[data-editor-discard]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    let activeLocale = config.activeLocale || 'tr';
    let isDirty = false;
    const previewValues = new Map();

    const translate = (text) => text;

    const setDirty = (dirty) => {
        isDirty = dirty;
        if (dirtyBadge) {
            dirtyBadge.classList.toggle('d-none', !dirty);
        }
    };

    const assignValue = (target, key, value) => {
        const parts = key.replace(/\]/g, '').split('[');
        let pointer = target;

        parts.forEach((segment, index) => {
            if (segment === '') {
                return;
            }

            const isLast = index === parts.length - 1;
            const nextSegment = parts[index + 1];
            const useArray = nextSegment !== undefined && nextSegment !== '' && !Number.isNaN(Number(nextSegment));

            if (isLast) {
                if (Array.isArray(pointer)) {
                    pointer[segment] = value;
                } else {
                    pointer[segment] = value;
                }
                return;
            }

            if (!(segment in pointer)) {
                pointer[segment] = useArray ? [] : {};
            }

            pointer = pointer[segment];
        });
    };

    const getFormDataObject = () => {
        const formData = new FormData(form);
        const result = {};

        for (const [key, value] of formData.entries()) {
            if (value instanceof File) {
                if (previewValues.has(key)) {
                    assignValue(result, key, previewValues.get(key));
                }
                continue;
            }

            assignValue(result, key, value);
        }

        return result;
    };

    const queuePreview = debounce((locale) => {
        sendPreview(locale);
    }, 300);

    const sendPreview = (locale) => {
        if (!config.routes?.preview_apply) {
            return;
        }

        const nested = getFormDataObject();
        const payload = {
            blocks: nested?.content?.[locale] || {},
            seo: nested?.seo?.[locale] || {},
            scripts: nested?.scripts?.[locale] || {},
        };

        fetch(config.routes.preview_apply, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-CMS-Preview-Token': config.previewToken,
            },
            body: JSON.stringify({
                page: config.page,
                locale,
                payload,
            }),
        }).then(() => {
            refreshIframe();
        }).catch(() => {
            // preview failures are non-blocking
        });
    };

    const uploadFile = async (input, locale) => {
        if (!config.routes?.preview_upload) {
            return null;
        }

        const files = input.files;
        if (!files || !files.length) {
            return null;
        }

        const file = files[0];
        const formData = new FormData();
        formData.append('file', file);
        formData.append('page', config.page);
        formData.append('field', input.name);
        formData.append('locale', locale);
        formData.append('preview_token', config.previewToken);

        const fieldType = input.getAttribute('data-field-type');
        if (fieldType) {
            formData.append('type', fieldType);
        }

        const response = await fetch(config.routes.preview_upload, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });

        if (!response.ok) {
            throw new Error('upload_failed');
        }

        const data = await response.json();
        if (!data?.url) {
            throw new Error('upload_failed');
        }

        previewValues.set(input.name, data.url);

        return data.url;
    };

    const refreshIframe = () => {
        if (!iframe) {
            return;
        }

        try {
            const url = new URL(iframe.getAttribute('src'), window.location.origin);
            url.searchParams.set('ts', Date.now().toString());
            iframe.setAttribute('src', url.toString());
        } catch (error) {
            const fallback = `${config.previewUrl}${config.previewUrl.includes('?') ? '&' : '?'}ts=${Date.now()}`;
            iframe.setAttribute('src', fallback);
        }
    };

    const showStatus = (message, type = 'success') => {
        let status = root.querySelector('[data-editor-status]');
        if (!status) {
            status = document.createElement('div');
            status.dataset.editorStatus = 'true';
            status.className = 'alert mt-3';
            root.querySelector('.cms-editor__header')?.appendChild(status);
        }
        status.className = `alert mt-3 alert-${type === 'error' ? 'danger' : 'success'}`;
        status.textContent = message;
        setTimeout(() => status.remove(), 5000);
    };

    const onFieldChange = (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement)) {
            return;
        }

        const localeContainer = target.closest('[data-locale-panel]');
        const locale = localeContainer?.getAttribute('data-locale-panel') || activeLocale;

        if (target instanceof HTMLInputElement && target.type === 'file') {
            setDirty(true);
            if (target.files?.length) {
                uploadFile(target, locale)
                    .then(() => {
                        queuePreview(locale);
                    })
                    .catch(() => {
                        showStatus(translate('Unable to upload preview file.'), 'error');
                    });
            } else {
                previewValues.delete(target.name);
                queuePreview(locale);
            }

            return;
        }

        if (target instanceof HTMLInputElement && target.type === 'checkbox' && target.name.endsWith('_remove')) {
            previewValues.delete(target.name.replace(/_remove$/, ''));
        }

        setDirty(true);
        queuePreview(locale);
    };

    const onTabClick = (event) => {
        const button = event.target.closest('[data-editor-tab]');
        if (!button) {
            return;
        }

        const tabId = button.getAttribute('data-editor-tab');
        root.querySelectorAll('[data-editor-tab]').forEach((tab) => {
            tab.classList.toggle('is-active', tab === button);
        });
        root.querySelectorAll('[data-editor-panel]').forEach((panel) => {
            panel.toggleAttribute('hidden', panel.getAttribute('data-editor-panel') !== tabId);
        });
    };

    const onLocaleTabClick = (event) => {
        const button = event.target.closest('[data-locale-tab]');
        if (!button) {
            return;
        }

        const locale = button.getAttribute('data-locale-tab');
        activeLocale = locale;
        root.querySelectorAll('[data-locale-tab]').forEach((tab) => {
            tab.classList.toggle('is-active', tab === button);
        });
        root.querySelectorAll('[data-locale-panel]').forEach((panel) => {
            panel.classList.toggle('is-active', panel.getAttribute('data-locale-panel') === locale);
        });
    };

    const onRepeaterClick = (event) => {
        const addButton = event.target.closest('[data-repeater-add]');
        if (addButton) {
            const wrapper = addButton.closest('[data-repeater]');
            const container = wrapper.querySelector('[data-repeater-items]');
            const template = wrapper.querySelector('template[data-repeater-template]');
            if (!container || !template) {
                return;
            }
            const index = container.querySelectorAll('[data-repeater-item]').length;
            const clone = template.content.cloneNode(true);
            clone.querySelectorAll('[name]').forEach((input) => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/__INDEX__/g, index));
                }
            });
            container.appendChild(clone);
            setDirty(true);
            queuePreview(wrapper.getAttribute('data-locale'));
            return;
        }

        const removeButton = event.target.closest('[data-repeater-remove]');
        if (removeButton) {
            const item = removeButton.closest('[data-repeater-item]');
            const wrapper = removeButton.closest('[data-repeater]');
            const container = wrapper.querySelector('[data-repeater-items]');
            if (item && container) {
                item.remove();
                renumberRepeater(container);
                setDirty(true);
                queuePreview(wrapper.getAttribute('data-locale'));
            }
            return;
        }

        const upButton = event.target.closest('[data-repeater-up]');
        if (upButton) {
            const item = upButton.closest('[data-repeater-item]');
            if (item?.previousElementSibling) {
                item.parentElement.insertBefore(item, item.previousElementSibling);
                renumberRepeater(item.parentElement);
                setDirty(true);
                queuePreview(item.closest('[data-repeater]')?.getAttribute('data-locale'));
            }
            return;
        }

        const downButton = event.target.closest('[data-repeater-down]');
        if (downButton) {
            const item = downButton.closest('[data-repeater-item]');
            if (item?.nextElementSibling) {
                item.parentElement.insertBefore(item.nextElementSibling, item);
                renumberRepeater(item.parentElement);
                setDirty(true);
                queuePreview(item.closest('[data-repeater]')?.getAttribute('data-locale'));
            }
        }
    };

    const renumberRepeater = (container) => {
        const items = container.querySelectorAll('[data-repeater-item]');
        items.forEach((item, index) => {
            item.querySelectorAll('[name]').forEach((input) => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', replaceIndexAtDepth(name, determineRepeaterDepth(container), index));
                }
            });
            const label = item.querySelector('strong');
            if (label) {
                const base = label.dataset.baseLabel || label.textContent.replace(/#\d+/, '').trim();
                label.dataset.baseLabel = base;
                label.textContent = `${base} #${index + 1}`;
            }
        });
    };

    const determineRepeaterDepth = (container) => {
        const wrapper = container.closest('[data-repeater]');
        if (!wrapper) {
            return 0;
        }

        let depth = 0;
        let parent = wrapper.parentElement?.closest('[data-repeater]');
        while (parent) {
            depth += 1;
            parent = parent.parentElement?.closest('[data-repeater]');
        }

        return depth;
    };

    const replaceIndexAtDepth = (name, depth, index) => {
        const matches = [...name.matchAll(/\[(\d+)\]/g)];
        if (!matches.length) {
            return name;
        }

        const target = matches[Math.min(depth, matches.length - 1)];
        if (!target || target.index === undefined) {
            return name;
        }

        const before = name.slice(0, target.index);
        const after = name.slice(target.index + target[0].length);

        return `${before}[${index}]${after}`;
    };

    const handleSave = () => {
        if (!config.routes?.save) {
            return;
        }

        const formData = new FormData(form);
        formData.set('preview_token', config.previewToken);

        fetch(config.routes.save, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('save_failed');
                }
                return response.json();
            })
            .then(() => {
                setDirty(false);
                showStatus(translate('Changes saved successfully.'));
                refreshIframe();
            })
            .catch(() => {
                showStatus(translate('Unable to save changes.'), 'error');
            });
    };

    const handleDiscard = () => {
        if (!config.routes?.preview_discard) {
            return;
        }

        fetch(config.routes.preview_discard, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-CMS-Preview-Token': config.previewToken,
            },
            body: JSON.stringify({
                page: config.page,
            }),
        }).finally(() => {
            setDirty(false);
            refreshIframe();
        });
    };

    root.addEventListener('input', onFieldChange);
    root.addEventListener('change', onFieldChange);
    root.addEventListener('click', onTabClick);
    root.addEventListener('click', onLocaleTabClick);
    root.addEventListener('click', onRepeaterClick);

    saveButton?.addEventListener('click', handleSave);
    discardButton?.addEventListener('click', handleDiscard);
});
