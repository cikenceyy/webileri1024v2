import bus from '../lib/bus.js';
import { initGalleryShowcase } from '../components/runtime.js';
import { isMotionReduced, onMotionChange } from '../components/motion-runtime.js';

const READABILITY_CLASS = 'ui-table--readable';

const initTableShowcaseControls = () => {
    const table = document.querySelector('#gallery-table');
    if (!table) return;

    const toggleButtons = document.querySelectorAll('[data-action="table-readability"][data-target="#gallery-table"]');
    toggleButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const next = !table.classList.contains(READABILITY_CLASS);
            table.classList.toggle(READABILITY_CLASS, next);
            button.setAttribute('aria-pressed', next ? 'true' : 'false');
            bus.emit('ui:table:scrollState', {
                element: table,
                tableId: table.dataset.tableId,
                readability: next ? 'enhanced' : 'base',
            });
        });
    });

    const resetButtons = document.querySelectorAll('[data-action="table-reset"][data-target="#gallery-table"]');
    resetButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const routeKey = table.dataset.routeKey || 'default';
            const tableId = table.dataset.tableId || 'default';
            try {
                window.localStorage.removeItem(`ui:table:density:${routeKey}:${tableId}`);
                window.localStorage.removeItem(`ui:table:v1:density:${routeKey}:${tableId}`);
                window.localStorage.removeItem(`ui:table:v1:density:${tableId}`);
            } catch (error) {
                // ignore
            }
            table.classList.remove(READABILITY_CLASS);
            table.classList.add('ui-table--dense');
            table.dataset.density = 'compact';
            const state = table.querySelector('[data-ui="density-state"]');
            if (state) state.textContent = 'Compact';
            const toggle = table.querySelector('[data-action="table-density"]');
            if (toggle) toggle.setAttribute('aria-pressed', 'true');
            window.location.reload();
        });
    });
};

const initSpeedDemo = () => {
    const demo = document.querySelector('[data-ui="speed-demo"]');
    if (!demo) return;

    const controls = document.querySelectorAll('[data-action="speed"]');
    const skeletonSurface = demo.querySelector('[data-variant="skeleton"] .ui-gallery__speed-surface');
    const contentSurface = demo.querySelector('[data-variant="content"] .ui-gallery__speed-surface');
    if (!skeletonSurface || !contentSurface) return;

    let timer = null;

    const activate = (mode) => {
        controls.forEach((button) => {
            const active = button.dataset.target === mode;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (timer) {
            window.clearTimeout(timer);
            timer = null;
        }

        const reduced = isMotionReduced();

        if (mode === 'skeleton' && !reduced) {
            skeletonSurface.dataset.state = 'skeleton';
            timer = window.setTimeout(() => {
                skeletonSurface.dataset.state = 'content';
            }, 420);
        } else {
            skeletonSurface.dataset.state = 'content';
        }

        contentSurface.dataset.state = 'content';
    };

    controls.forEach((button) => {
        button.addEventListener('click', () => activate(button.dataset.target));
    });

    const syncWithMotion = () => {
        activate(isMotionReduced() ? 'content' : 'skeleton');
    };

    syncWithMotion();
    onMotionChange(syncWithMotion);
};

document.addEventListener('DOMContentLoaded', () => {
    initGalleryShowcase();
    initTableShowcaseControls();
    initSpeedDemo();

    bus.on('ui:toolbar:filter', () => {
        bus.emit('ui:toast:show', {
            message: 'Filtre uygulandı',
            variant: 'info',
            timeout: 3200,
            progress: true,
        });
    });

    bus.on('ui:toolbar:filter-clear', () => {
        bus.emit('ui:toast:show', {
            message: 'Filtre temizlendi',
            variant: 'info',
            timeout: 2200,
        });
    });
});
