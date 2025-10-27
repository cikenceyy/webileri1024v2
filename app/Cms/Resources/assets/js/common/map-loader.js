export function mapLoader(element) {
    if (!element || element.dataset.mapInitialized === 'true') {
        return;
    }

    element.dataset.mapInitialized = 'true';

    const trigger = element.querySelector('[data-map-trigger]');
    const placeholder = element.querySelector('.c-map-placeholder');
    const embed = element.dataset.mapSrc;

    if (!trigger || !placeholder || !embed) {
        return;
    }

    const loadMap = () => {
        if (element.dataset.mapLoaded === 'true') {
            return;
        }

        placeholder.innerHTML = embed;
        placeholder.classList.add('is-loaded');
        element.removeAttribute('data-skeleton');
        element.dataset.mapLoaded = 'true';
    };

    trigger.addEventListener('click', () => {
        loadMap();
        trigger.remove();
    }, { once: true });
}
