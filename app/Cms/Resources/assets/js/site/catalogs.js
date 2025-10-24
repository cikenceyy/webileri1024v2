import { bootPage } from '../common/page-boot';
import { emitBeacon } from '../common/beacon';

bootPage('catalogs', () => {
    const container = document.querySelector('[data-filter-target]');
    const buttons = Array.from(document.querySelectorAll('.filter-chip[data-filter]'));
    const emptyState = document.querySelector('[data-empty-state]');

    if (!container) {
        return;
    }

    const cards = Array.from(container.querySelectorAll('.catalog-card[data-year]'));

    const applyFilter = (slug) => {
        const active = slug && slug !== 'all' ? slug : 'all';
        let visible = 0;

        cards.forEach((card) => {
            const year = (card.dataset.year || 'all').toLowerCase();
            const match = active === 'all' || year === active;
            card.hidden = !match;
            if (match) {
                visible += 1;
            }
        });

        if (emptyState) {
            emptyState.hidden = visible > 0;
        }
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            buttons.forEach((chip) => chip.classList.toggle('is-active', chip === button));
            const slug = (button.dataset.filter || 'all').toLowerCase();
            applyFilter(slug);

            if (slug && slug !== 'all') {
                emitBeacon('catalog_filter', { slug });
            }
        });
    });

    applyFilter('all');
});
