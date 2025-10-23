export function skeletons(element) {
    if (!element) {
        return;
    }

    const items = element.querySelectorAll('[data-skeleton]');

    if (!items.length) {
        return;
    }

    requestAnimationFrame(() => {
        items.forEach((item) => {
            item.removeAttribute('data-skeleton');
            item.classList.remove('placeholder');
        });
    });
}
