import { $$ } from '../lib/dom.js';
import bus from '../lib/bus.js';

const observers = new Map();
const trackedContainers = new Set();
let overlaySyncBound = false;

const updateShadowState = (container) => {
    const root = container.closest("[data-ui='table']") ?? container;
    const hasOverflow = container.scrollWidth > container.clientWidth + 1;
    const atStart = container.scrollLeft <= 0;
    const atEnd = Math.ceil(container.scrollLeft + container.clientWidth) >= container.scrollWidth - 1;

    container.classList.toggle('has-scroll-shadow-start', hasOverflow && !atStart);
    container.classList.toggle('has-scroll-shadow-end', hasOverflow && !atEnd);
    root.classList.toggle('has-shadow', hasOverflow && (!atStart || !atEnd));
};

const bindResizeObserver = (container) => {
    if (typeof ResizeObserver === 'undefined') {
        window.addEventListener('resize', () => window.requestAnimationFrame(() => updateShadowState(container)));
        return;
    }

    const observer = new ResizeObserver(() => updateShadowState(container));
    observer.observe(container);
    observers.set(container, observer);
};

export const initScrollShadow = () => {
    $$("[data-ui='scroll-container']").forEach((container) => {
        trackedContainers.add(container);
        const root = container.closest("[data-ui='table']") ?? container;

        const handleScroll = () => {
            root.classList.add('is-scrolling');
            window.requestAnimationFrame(() => {
                updateShadowState(container);
                if (container.scrollLeft <= 0 || Math.ceil(container.scrollLeft + container.clientWidth) >= container.scrollWidth) {
                    root.classList.remove('is-scrolling');
                }
            });
        };

        container.addEventListener('scroll', handleScroll, { passive: true });
        bindResizeObserver(container);
        updateShadowState(container);
    });

    if (!overlaySyncBound) {
        overlaySyncBound = true;
        const sync = () => trackedContainers.forEach((container) => updateShadowState(container));
        bus.on('ui:overlay:open', sync);
        bus.on('ui:overlay:close', sync);
        bus.on('ui:overlay:closed', sync);
    }
};

export const disconnectScrollShadow = () => {
    observers.forEach((observer, container) => {
        observer.unobserve(container);
    });
    observers.clear();
};
