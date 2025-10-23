import { bootPage } from '../common/page-boot';

bootPage('corporate', () => {
    const timeline = document.querySelector('.timeline');
    if (!timeline) {
        return;
    }

    const items = timeline.querySelectorAll('.timeline-item');
    if (!items.length || !('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-active'));
        return;
    }

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-active');
            } else if (!prefersReduced) {
                entry.target.classList.remove('is-active');
            }
        });
    }, { rootMargin: '-20% 0px -20% 0px' });

    items.forEach((item) => observer.observe(item));
});
