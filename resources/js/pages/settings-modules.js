// Admin > Ayarlar > Modül Ayarları: form gönderimini AJAX ile yönetir.
import bus from '../lib/bus.js';

const normalizePayload = (form) => {
    const data = new FormData(form);
    return {
        drive_enable_versioning: data.get('drive_enable_versioning') ? 1 : 0,
        inventory_low_stock_threshold: data.get('inventory_low_stock_threshold') ?? 0,
        finance_default_currency: (data.get('finance_default_currency') ?? '').toString().toUpperCase(),
        cms_feature_flags: data.get('cms_feature_flags') ?? '',
    };
};

const initModuleSettings = () => {
    const root = document.querySelector('[data-settings-modules]');
    if (!root) return;

    const form = root.querySelector('[data-modules-form]');
    const submit = root.querySelector('[data-modules-submit]');
    const spinner = submit?.querySelector('.spinner-border');

    const toggle = (state) => {
        if (!submit) return;
        submit.toggleAttribute('disabled', state);
        if (spinner) {
            spinner.classList.toggle('d-none', !state);
        }
    };

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        toggle(true);

        try {
            const payload = normalizePayload(form);
            const response = await fetch(root.dataset.updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': root.dataset.csrf,
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({ message: 'Beklenmedik hata' }));
                throw new Error(error.message ?? 'Beklenmedik hata');
            }

            const data = await response.json();
            bus.emit('ui:toast:show', {
                variant: 'success',
                message: data.message ?? 'Modül ayarları güncellendi.',
            });
        } catch (error) {
            bus.emit('ui:toast:show', {
                variant: 'danger',
                message: error.message ?? 'İşlem sırasında hata oluştu.',
            });
        } finally {
            toggle(false);
        }
    });
};

document.addEventListener('app:ready', initModuleSettings);
