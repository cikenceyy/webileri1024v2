import { $$ } from '../lib/dom.js';
import bus from '../lib/bus.js';
import { applyDensity, readDensityPreference } from './density-toggle.js';

const initSearch = (table) => {
    const form = table.querySelector('[data-ui="table-search"]');
    if (!form) return;

    form.addEventListener('submit', () => {
        table.classList.add('is-searching');
        form.setAttribute('aria-busy', 'true');
    });
};

const initTotals = (table) => {
    const viewport = table.querySelector('[data-ui="scroll-container"]');
    if (!viewport) return;

    const update = () => {
        const needsSticky = viewport.scrollHeight - viewport.clientHeight > 4;
        table.classList.toggle('has-sticky-totals', needsSticky);
    };

    update();

    viewport.addEventListener(
        'scroll',
        () => window.requestAnimationFrame(update),
        { passive: true },
    );

    if (typeof ResizeObserver !== 'undefined') {
        const observer = new ResizeObserver(update);
        observer.observe(viewport);
    } else {
        window.addEventListener('resize', () => window.requestAnimationFrame(update));
    }
};

const initFreezeSync = (table) => {
    const handler = ({ element, frozen }) => {
        if (!element || element !== table) return;
        table.classList.toggle('is-frozen', Boolean(frozen));
    };

    bus.on('ui:table:freeze', handler);
};

const initDensityState = (table) => {
    const routeKey = table.dataset.routeKey || 'default';
    const tableId = table.dataset.tableId || 'default';
    const stored = readDensityPreference(routeKey, tableId);
    const initial = stored || table.dataset.density || 'compact';
    applyDensity(table, initial);
};

export const initTableCore = () => {
    $$("[data-ui='table']").forEach((table) => {
        initDensityState(table);
        initSearch(table);
        initTotals(table);
        initFreezeSync(table);
    });
};
