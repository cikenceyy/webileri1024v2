import { $$, portal, lockScroll, unlockScroll } from '../lib/dom.js';
import { focusTrap, restoreFocus } from '../lib/a11y.js';
import bus from '../lib/bus.js';
import { registerOverlay } from './overlay-registry.js';

const drawers = new Map();

const freezeTable = (source, frozen) => {
    const table = source?.closest("[data-ui='table']");
    if (!table) return null;
    bus.emit('ui:table:freeze', { element: table, frozen, trigger: source });
    return table;
};

const isMotionReduced = () => document.documentElement.getAttribute('data-motion') === 'reduced';

const setInertState = (element, inert) => {
    if (!element) return;
    if (inert) {
        element.setAttribute('inert', '');
        if ('inert' in element) {
            element.inert = true;
        }
    } else {
        element.removeAttribute('inert');
        if ('inert' in element) {
            element.inert = false;
        }
    }
};

const createDrawerController = (drawer) => {
    portal(drawer);
    drawer.hidden = !drawer.classList.contains('is-open');
    setInertState(drawer, drawer.hidden);
    const panel = drawer.querySelector('.ui-drawer__panel');
    if (panel && !panel.hasAttribute('tabindex')) {
        panel.setAttribute('tabindex', '-1');
    }

    let releaseTrap = () => {};
    let trigger = null;
    let tableContext = null;
    let openedAt = 0;
    let releaseOverlay = () => {};
    let openAnnounced = true;
    let closeAnnounced = true;

    const escClosable = drawer.dataset.escClosable !== 'false';

    const announceOpenComplete = () => {
        if (!openAnnounced) {
            openAnnounced = true;
            bus.emit('ui:overlay:opened', { type: 'drawer', id: drawer.id });
            drawer.hidden = false;
            setInertState(drawer, false);
        }
    };

    const announceCloseComplete = () => {
        if (!closeAnnounced) {
            closeAnnounced = true;
            bus.emit('ui:overlay:closed', { type: 'drawer', id: drawer.id });
            drawer.hidden = true;
            setInertState(drawer, true);
        }
    };

    const close = ({ restore = true } = {}) => {
        if (!drawer.classList.contains('is-open')) return;
        closeAnnounced = false;
        bus.emit('ui:overlay:close', { type: 'drawer', id: drawer.id });
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        setInertState(drawer, true);
        releaseTrap();
        releaseTrap = () => {};
        releaseOverlay();
        releaseOverlay = () => {};
        unlockScroll();
        if (tableContext) {
            bus.emit('ui:table:freeze', { element: tableContext, frozen: false, trigger });
            tableContext = null;
        }
        openedAt = 0;
        if (restore) {
            restoreFocus(trigger);
        }
        trigger = null;
        if (isMotionReduced()) {
            announceCloseComplete();
        }
    };

    const open = ({ source } = {}) => {
        if (drawer.classList.contains('is-open')) return;
        portal(drawer);
        trigger = source ?? document.activeElement;
        openAnnounced = false;
        bus.emit('ui:overlay:open', { type: 'drawer', id: drawer.id, source: trigger });
        drawer.hidden = false;
        setInertState(drawer, false);
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        lockScroll();
        tableContext = freezeTable(source, true);
        releaseTrap = focusTrap(panel, {
            initialFocus: panel?.querySelector('[data-autofocus]') ?? panel?.querySelector('[autofocus]'),
        });
        openedAt = Date.now();
        releaseOverlay = registerOverlay({ id: drawer.id, close, escClosable });
        if (isMotionReduced()) {
            announceOpenComplete();
        }
    };

    drawer.addEventListener('click', (event) => {
        const target = event.target.closest('[data-action]');
        if (!target) return;
        const action = target.dataset.action;
        const isPanelControl = Boolean(target.closest('.ui-drawer__panel'));
        if (action === 'close' && (escClosable || target.dataset.force === 'true' || isPanelControl)) {
            event.preventDefault();
            close({ restore: target.dataset.restore !== 'false' });
        }
    });

    if (panel) {
        panel.addEventListener('transitionend', (event) => {
            if (event.target !== panel) return;
            if (event.propertyName !== 'transform' && event.propertyName !== 'opacity') return;
            if (drawer.classList.contains('is-open')) {
                announceOpenComplete();
            } else {
                announceCloseComplete();
            }
        });
    }

    return {
        open,
        close,
        escClosable,
        isOpen: () => drawer.classList.contains('is-open'),
        get openedAt() {
            return openedAt;
        },
    };
};

export const initDrawers = () => {
    $$("[data-ui='drawer']").forEach((drawer) => {
        if (drawers.has(drawer.id)) return;
        const controller = createDrawerController(drawer);
        drawers.set(drawer.id, controller);
    });

    bus.on('ui:drawer:open', ({ id, source }) => {
        const controller = drawers.get(id);
        controller?.open({ source });
    });

    bus.on('ui:drawer:close', ({ id, restore = true } = {}) => {
        const controller = drawers.get(id);
        controller?.close({ restore });
    });
};
