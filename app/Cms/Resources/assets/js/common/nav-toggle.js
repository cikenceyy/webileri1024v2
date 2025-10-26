export function navToggle(button) {
    if (!button || button.dataset.navToggleInitialized === 'true') {
        return;
    }

    button.dataset.navToggleInitialized = 'true';

    const targetId = button.getAttribute('aria-controls');
    const target = targetId ? document.getElementById(targetId) : null;

    if (!target) {
        return;
    }

    const close = () => {
        button.setAttribute('aria-expanded', 'false');
        target.classList.remove('is-open');
        target.hidden = true;
    };

    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') === 'true';
        const nextState = !expanded;
        button.setAttribute('aria-expanded', String(nextState));
        target.classList.toggle('is-open', nextState);
        target.hidden = !nextState;
    });

    target.hidden = button.getAttribute('aria-expanded') !== 'true';

    if (!document.body.dataset.navToggleEsc) {
        document.body.dataset.navToggleEsc = 'true';

        document.addEventListener(
            'keydown',
            (event) => {
                if (event.key === 'Escape') {
                    close();
                }
            },
            { passive: true }
        );
    }
}
