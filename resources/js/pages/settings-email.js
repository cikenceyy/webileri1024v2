// Admin > Ayarlar > E-posta Merkezi: form ve test gönderimini yönetir.
import bus from '../lib/bus.js';

const collectFormData = (form) => {
    const data = new FormData(form);
    return Object.fromEntries(data.entries());
};

const initEmailSettings = () => {
    const root = document.querySelector('[data-settings-email]');
    if (!root) return;

    const form = root.querySelector('[data-email-form]');
    const submitButton = root.querySelector('[data-email-submit]');
    const submitSpinner = submitButton?.querySelector('.spinner-border');
    const testButton = root.querySelector('[data-email-test]');
    const testSpinner = testButton?.querySelector('.spinner-border');

    const toggleState = (button, spinner, state) => {
        if (!button) return;
        button.toggleAttribute('disabled', state);
        if (spinner) {
            spinner.classList.toggle('d-none', !state);
        }
    };

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        toggleState(submitButton, submitSpinner, true);

        try {
            const payload = collectFormData(form);
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
                message: data.message ?? 'E-posta ayarları güncellendi.',
            });
        } catch (error) {
            bus.emit('ui:toast:show', {
                variant: 'danger',
                message: error.message ?? 'İşlem sırasında hata oluştu.',
            });
        } finally {
            toggleState(submitButton, submitSpinner, false);
        }
    });

    testButton?.addEventListener('click', async () => {
        toggleState(testButton, testSpinner, true);

        try {
            const response = await fetch(root.dataset.testUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': root.dataset.csrf,
                },
                body: JSON.stringify({}),
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({ message: 'Beklenmedik hata' }));
                throw new Error(error.message ?? 'Beklenmedik hata');
            }

            const data = await response.json();
            bus.emit('ui:toast:show', {
                variant: 'success',
                message: data.message ?? 'Deneme e-postası kuyruğa alındı.',
            });
        } catch (error) {
            bus.emit('ui:toast:show', {
                variant: 'danger',
                message: error.message ?? 'Test gönderimi başarısız.',
            });
        } finally {
            toggleState(testButton, testSpinner, false);
        }
    });
};

document.addEventListener('app:ready', initEmailSettings);
