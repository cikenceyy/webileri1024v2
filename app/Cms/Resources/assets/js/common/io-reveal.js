const observers = new WeakMap();

export function ioReveal(element) {
    if (!element) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!('IntersectionObserver' in window) || prefersReducedMotion) {
        element.classList.add('is-visible');
        return;
    }

    if (observers.has(element)) {
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.18,
        rootMargin: '0px 0px -24px 0px',
    });

    observer.observe(element);
    observers.set(element, observer);
}
