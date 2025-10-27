import { bootPage } from '../common/page-boot';
import { emitBeacon } from '../common/beacon';

bootPage('products', () => {
    const container = document.querySelector('[data-filter-target]');
    if (!container) {
        return;
    }

    const cards = Array.from(container.querySelectorAll('.c-card[data-category]'));
    const buttons = Array.from(document.querySelectorAll('.c-chip[data-filter]'));
    const emptyState = document.querySelector('[data-empty-state]');

    const applyFilter = (slug) => {
        const activeSlug = slug && slug !== 'all' ? slug : 'all';
        let visibleCount = 0;

        cards.forEach((card) => {
            const raw = card.dataset.category || 'all';
            const categories = raw
                .split(',')
                .map((value) => value.trim().toLowerCase())
                .filter(Boolean);
            const match = activeSlug === 'all' || categories.includes(activeSlug);
            card.hidden = !match;
            if (match) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            emptyState.hidden = visibleCount > 0;
        }
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            buttons.forEach((chip) => chip.classList.toggle('is-active', chip === button));
            const slug = button.dataset.filter || 'all';
            applyFilter(slug);

            if (slug && slug !== 'all') {
                emitBeacon('product_filter', { slug });
            }
        });
    });

    applyFilter('all');
});
