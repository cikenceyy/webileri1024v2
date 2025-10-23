export function navToggle(button) {
    if (!button) {
        return;
    }

    const targetId = button.getAttribute('aria-controls');
    const target = targetId ? document.getElementById(targetId) : null;

    if (!target) {
        return;
    }

    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!expanded));
        target.hidden = expanded;
    });

    target.hidden = button.getAttribute('aria-expanded') !== 'true';
}
