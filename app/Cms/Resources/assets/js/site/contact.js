import { bootPage } from '../common/page-boot';
import { emitBeacon } from '../common/beacon';

bootPage('contact', () => {
    const form = document.querySelector('[data-module="contact-form"]');
    if (!form) return;

    const submittedAt = form.querySelector('input[name="submitted_at"]');
    if (submittedAt) {
        submittedAt.value = String(Math.floor(Date.now() / 1000));
    }

    form.addEventListener('submit', () => {
        emitBeacon('form_submit', { form: 'contact' }, form.getAttribute('action'));
    });
});
