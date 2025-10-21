const FOCUSABLE = [
    'a[href]',
    'button:not([disabled])',
    'textarea:not([disabled])',
    'input:not([type="hidden"]):not([disabled])',
    'select:not([disabled])',
    '[tabindex]:not([tabindex="-1"])'
];

const getFocusable = (container) =>
    Array.from(container.querySelectorAll(FOCUSABLE.join(','))).filter((element) =>
        !element.hasAttribute('disabled') && element.getAttribute('aria-hidden') !== 'true'
    );

export const focusTrap = (container, { initialFocus } = {}) => {
    if (!container) return () => {};

    const focusFirst = () => {
        const items = getFocusable(container);
        const target = initialFocus && container.contains(initialFocus) ? initialFocus : items[0];
        (target ?? container).focus({ preventScroll: true });
    };

    const handleKeydown = (event) => {
        if (event.key !== 'Tab') return;
        const items = getFocusable(container);
        if (items.length === 0) {
            event.preventDefault();
            container.focus({ preventScroll: true });
            return;
        }

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus({ preventScroll: true });
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus({ preventScroll: true });
        }
    };

    container.addEventListener('keydown', handleKeydown);
    requestAnimationFrame(focusFirst);

    return () => container.removeEventListener('keydown', handleKeydown);
};

export const restoreFocus = (element) => {
    if (element && typeof element.focus === 'function') {
        element.focus({ preventScroll: true });
    }
};
