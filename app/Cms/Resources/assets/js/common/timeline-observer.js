export function timelineObserver(element) {
    if (!element) {
        return;
    }

    const items = element.querySelectorAll('.timeline-item');

    if (!items.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
        : false;

    if (!('IntersectionObserver' in window) || prefersReducedMotion) {
        items.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, {
        threshold: 0.35,
        rootMargin: '-20% 0px -10% 0px',
    });

    items.forEach((item) => observer.observe(item));
}
