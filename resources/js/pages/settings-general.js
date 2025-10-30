// Admin > Ayarlar > Genel sayfası: form gönderimini AJAX ile yönetir ve görsel geri bildirim sağlar.
import bus from '../lib/bus.js';

const initGeneralSettings = () => {
    const root = document.querySelector('[data-settings-general]');
    if (!root) return;

    const form = root.querySelector('[data-general-form]');
    const submitButton = root.querySelector('[data-general-submit]');
    const spinner = submitButton?.querySelector('.spinner-border');
    const logoPreview = root.querySelector('[data-logo-preview]');
    const updatedBy = root.querySelector('[data-general-updated-by]');
    const updatedAt = root.querySelector('[data-general-updated-at]');

    const setBusy = (state) => {
        if (!submitButton) return;
        submitButton.toggleAttribute('disabled', state);
        if (spinner) {
            spinner.classList.toggle('d-none', !state);
        }
    };

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        setBusy(true);

        try {
            const action = root.dataset.updateUrl;
            const csrf = root.dataset.csrf;
            const payload = new FormData(form);

            const response = await fetch(action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: payload,
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({ message: 'Beklenmedik hata' }));
                throw new Error(error.message ?? 'Beklenmedik hata');
            }

            const data = await response.json();

            if (logoPreview) {
                logoPreview.innerHTML = '';
                if (data.logo_url) {
                    const img = document.createElement('img');
                    img.src = data.logo_url;
                    img.alt = 'Firma logosu';
                    img.className = 'img-fluid';
                    logoPreview.appendChild(img);
                } else {
                    const span = document.createElement('span');
                    span.className = 'text-muted';
                    span.textContent = 'Logo yüklenmemiş.';
                    logoPreview.appendChild(span);
                }
            }

            if (updatedBy) {
                updatedBy.textContent = 'Siz';
            }

            if (updatedAt) {
                const now = new Intl.DateTimeFormat('tr-TR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                }).format(new Date());
                updatedAt.textContent = now;
            }

            bus.emit('ui:toast:show', {
                variant: 'success',
                message: data.message ?? 'Genel ayarlar güncellendi.',
            });
        } catch (error) {
            bus.emit('ui:toast:show', {
                variant: 'danger',
                message: error.message ?? 'İşlem sırasında hata oluştu.',
            });
        } finally {
            setBusy(false);
        }
    });
};

document.addEventListener('app:ready', initGeneralSettings);
