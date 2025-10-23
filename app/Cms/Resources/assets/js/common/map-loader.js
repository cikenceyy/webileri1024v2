export function mapLoader(element) {
    if (!element) {
        return;
    }

    const trigger = element.querySelector('[data-map-trigger]');
    const placeholder = element.querySelector('.map-placeholder');
    const embed = element.dataset.mapSrc;

    if (!trigger || !placeholder || !embed) {
        return;
    }

    const loadMap = () => {
        placeholder.innerHTML = embed;
        placeholder.classList.add('is-loaded');
        element.removeAttribute('data-skeleton');
        trigger.removeEventListener('click', loadMap);
        trigger.remove();
    };

    trigger.addEventListener('click', loadMap, { once: true });
}
