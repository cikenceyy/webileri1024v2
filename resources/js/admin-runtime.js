import 'bootstrap/js/dist/dropdown';
import { $$, $ } from './lib/dom.js';
import bus from './lib/bus.js';
import { initDrawers } from './components/drawer.js';
import { initModals } from './components/modal.js';
import { initToasts } from './components/toast.js';
import { initInlineEdit } from './components/inline-edit.js';
import { initDensityToggle } from './components/density-toggle.js';
import { initToolbar } from './components/toolbar.js';
import { initScrollShadow } from './components/scroll-shadow.js';
import { bootstrapRuntime, initRuntimeControls, toggleSidebarMode } from './components/runtime.js';
import { initSidebarNavigation } from './components/sidebar.js';
import { initTableCore } from './components/table-core.js';


const initActions = () => {
    document.addEventListener('click', (event) => {
        const control = event.target.closest('[data-action]');
        if (!control) return;

        const action = control.dataset.action;
        const target = control.dataset.target;

        if (action === 'open' && target) {
            event.preventDefault();
            if (target.startsWith('#')) {
                const element = document.querySelector(target);
                if (element?.dataset.ui === 'drawer') {
                    bus.emit('ui:drawer:open', { id: element.id, source: control });
                } else if (element?.dataset.ui === 'modal') {
                    bus.emit('ui:modal:open', { id: element.id, source: control });
                }
            }
        }

        if (action === 'toggle' && target === '#sidebar') {
            event.preventDefault();
            const next = toggleSidebarMode();
            control?.setAttribute('aria-expanded', next === 'compact' ? 'false' : 'true');
        }

        if (action === 'history-back') {
            event.preventDefault();
            window.history.back();
        }

        if (action === 'toast') {
            bus.emit('ui:toast:show', {
                title: control.dataset.title ?? 'Bildirim',
                message: control.dataset.message ?? 'Aksiyon tamamlandı.',
            });
        }

        if (action === 'show-scroll') {
            const container = control.closest('.ui-table')?.querySelector("[data-ui='scroll-container']");
            container?.scrollBy({ left: container.scrollWidth, behavior: 'smooth' });
        }

        if (action === 'dismiss-alert') {
            event.preventDefault();
            control.closest('[data-ui="alert"]')?.remove();
        }
    });
};

const initHeaderEffects = () => {
    const header = document.querySelector('[data-ui="header"]');
    if (!header) return;

    const update = () => {
        const isScrolled = window.scrollY > 8;
        header.setAttribute('data-scrolled', isScrolled ? 'true' : 'false');
    };

    update();
    window.addEventListener('scroll', () => window.requestAnimationFrame(update));
};

bootstrapRuntime();

document.addEventListener('DOMContentLoaded', () => {
    initRuntimeControls();
    initActions();
    initDrawers();
    initModals();
    initToasts();
    initInlineEdit();
    initDensityToggle();
    initToolbar();
    initScrollShadow();
    initTableCore();
    initHeaderEffects();
    initSidebarNavigation();
});

export { bus };
