import { bootPage } from '../common/page-boot';

bootPage('corporate', () => {
    const counters = document.querySelectorAll('[data-counter]');
    counters.forEach((counter) => {
        const target = Number(counter.dataset.counter ?? 0);
        if (!target) return;
        let current = 0;
        const step = Math.max(1, Math.round(target / 40));
        const tick = () => {
            current = Math.min(target, current + step);
            counter.textContent = current.toString();
            if (current < target) {
                requestAnimationFrame(tick);
            }
        };
        requestAnimationFrame(tick);
    });
});
