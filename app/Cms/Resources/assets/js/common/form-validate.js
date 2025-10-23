export function formValidate(element) {
    const form = element instanceof HTMLFormElement ? element : element.closest('form');

    if (!form) {
        return;
    }

    form.setAttribute('novalidate', 'novalidate');

    form.addEventListener('submit', (event) => {
        const isValid = form.checkValidity();

        if (!isValid) {
            event.preventDefault();
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.setAttribute('aria-invalid', 'true');
            }
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.setAttribute('disabled', 'disabled');
            submitButton.setAttribute('aria-busy', 'true');
        }
    });

    form.querySelectorAll('input, textarea').forEach((field) => {
        field.addEventListener('input', () => {
            field.removeAttribute('aria-invalid');
        });
    });
}
