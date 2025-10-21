import { $$, portal } from '../lib/dom.js';
import bus from '../lib/bus.js';

const FILTER_DELAY = 250;

const setFilterState = (toolbar, active) => {
    toolbar.classList.toggle('has-filter', active);
    const badge = toolbar.querySelector('[data-ui="toolbar-filter-badge"]');
    if (!badge) return;
    badge.hidden = !active;
};

export const initToolbar = () => {
    $$("[data-ui='toolbar']").forEach((toolbar) => {
        $$('[data-portal="true"]', toolbar).forEach((element) => portal(element));

        let filterTimer = null;

        toolbar.addEventListener('click', (event) => {
            const button = event.target.closest('[data-action]');
            if (!button) return;
            event.preventDefault();
            const action = button.dataset.action;

            if (action === 'filter') {
                if (filterTimer) {
                    window.clearTimeout(filterTimer);
                }
                toolbar.classList.add('is-busy');
                filterTimer = window.setTimeout(() => {
                    toolbar.classList.remove('is-busy');
                    setFilterState(toolbar, true);
                    bus.emit('ui:toolbar:filter', { toolbar, button });
                    filterTimer = null;
                }, FILTER_DELAY);
                return;
            }

            if (action === 'filter-clear') {
                if (filterTimer) {
                    window.clearTimeout(filterTimer);
                    filterTimer = null;
                }
                toolbar.classList.remove('is-busy');
                setFilterState(toolbar, false);
                bus.emit('ui:toolbar:filter-clear', { toolbar, button });
                return;
            }

            bus.emit(`ui:toolbar:${action}`, { toolbar, button });
        });
    });
};
