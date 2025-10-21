import { $$, portal, lockScroll, unlockScroll } from '../lib/dom.js';
import { focusTrap, restoreFocus } from '../lib/a11y.js';
import bus from '../lib/bus.js';
import { registerOverlay } from './overlay-registry.js';

const modals = new Map();

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

const createModalController = (modal) => {
    portal(modal);
    modal.hidden = !modal.classList.contains('is-open');
    setInertState(modal, modal.hidden);
    const dialog = modal.querySelector('.ui-modal__dialog');
    if (dialog && !dialog.hasAttribute('tabindex')) {
        dialog.setAttribute('tabindex', '-1');
    }

    let releaseTrap = () => {};
    let trigger = null;
    let tableContext = null;
    let openedAt = 0;
    let releaseOverlay = () => {};
    let openAnnounced = true;
    let closeAnnounced = true;

    const escClosable = modal.dataset.escClosable !== 'false';

    const announceOpenComplete = () => {
        if (!openAnnounced) {
            openAnnounced = true;
            bus.emit('ui:overlay:opened', { type: 'modal', id: modal.id });
            modal.hidden = false;
            setInertState(modal, false);
        }
    };

    const announceCloseComplete = () => {
        if (!closeAnnounced) {
            closeAnnounced = true;
            bus.emit('ui:overlay:closed', { type: 'modal', id: modal.id });
            modal.hidden = true;
            setInertState(modal, true);
        }
    };

    const close = ({ restore = true } = {}) => {
        if (!modal.classList.contains('is-open')) return;
        closeAnnounced = false;
        bus.emit('ui:overlay:close', { type: 'modal', id: modal.id });
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        setInertState(modal, true);
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
        if (modal.classList.contains('is-open')) return;
        portal(modal);
        trigger = source ?? document.activeElement;
        openAnnounced = false;
        bus.emit('ui:overlay:open', { type: 'modal', id: modal.id, source: trigger });
        modal.hidden = false;
        setInertState(modal, false);
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        lockScroll();
        tableContext = freezeTable(source, true);
        releaseTrap = focusTrap(dialog, {
            initialFocus: dialog?.querySelector('[data-autofocus]') ?? dialog?.querySelector('[autofocus]'),
        });
        openedAt = Date.now();
        releaseOverlay = registerOverlay({ id: modal.id, close, escClosable });
        if (isMotionReduced()) {
            announceOpenComplete();
        }
    };

    modal.addEventListener('click', (event) => {
        const target = event.target.closest('[data-action]');
        if (!target) return;
        const action = target.dataset.action;
        const isDialogControl = Boolean(target.closest('.ui-modal__dialog'));
        if (action === 'close' && (escClosable || target.dataset.force === 'true' || isDialogControl)) {
            event.preventDefault();
            close({ restore: target.dataset.restore !== 'false' });
        }
    });

    if (dialog) {
        dialog.addEventListener('transitionend', (event) => {
            if (event.target !== dialog) return;
            if (event.propertyName !== 'transform' && event.propertyName !== 'opacity') return;
            if (modal.classList.contains('is-open')) {
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
        isOpen: () => modal.classList.contains('is-open'),
        get openedAt() {
            return openedAt;
        },
    };
};

export const initModals = () => {
    $$("[data-ui='modal']").forEach((modal) => {
        if (modals.has(modal.id)) return;
        const controller = createModalController(modal);
        modals.set(modal.id, controller);
    });

    bus.on('ui:modal:open', ({ id, source }) => {
        const controller = modals.get(id);
        controller?.open({ source });
    });

    bus.on('ui:modal:close', ({ id, restore = true } = {}) => {
        const controller = modals.get(id);
        controller?.close({ restore });
    });
};
