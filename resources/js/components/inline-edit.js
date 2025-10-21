import { $$ } from '../lib/dom.js';
import bus from '../lib/bus.js';
import { isMotionReduced } from './motion-runtime.js';

const HIGHLIGHT_TIMEOUT = 1800;
const UNDO_TIMEOUT = 5000;

const states = new WeakMap();

const getState = (container) => {
    if (!states.has(container)) {
        states.set(container, {
            undoTimer: null,
            lastValue: container.querySelector('.ui-inline-edit__display')?.textContent?.trim() ?? '',
        });
    }
    return states.get(container);
};

const setStatus = (container, message) => {
    const region = container.querySelector('[data-ui="inline-status"]');
    if (!region) return;
    region.textContent = message;
};

const showUndo = (container, previousValue, onUndo) => {
    const state = getState(container);
    const undo = container.querySelector('[data-ui="inline-undo"]');
    if (!undo) return;

    undo.hidden = false;
    const button = undo.querySelector('[data-action="undo"]');
    if (!button) return;

    if (state.undoTimer) {
        window.clearTimeout(state.undoTimer);
    }

    const dispose = () => {
        undo.hidden = true;
        button.removeEventListener('click', handleUndo);
        state.undoTimer = null;
    };

    const handleUndo = () => {
        dispose();
        onUndo(previousValue);
    };

    button.addEventListener('click', handleUndo);
    state.undoTimer = window.setTimeout(dispose, UNDO_TIMEOUT);
};

const highlightChange = (container) => {
    container.classList.add('has-change');
    if (isMotionReduced()) {
        container.classList.remove('has-change');
        return;
    }
    window.setTimeout(() => {
        container.classList.remove('has-change');
    }, HIGHLIGHT_TIMEOUT);
};

const closeEditor = (container, { restoreFocus = true } = {}) => {
    const display = container.querySelector('.ui-inline-edit__display');
    const form = container.querySelector('.ui-inline-edit__form');
    if (!display || !form) return;
    container.classList.remove('is-active');
    container.classList.remove('is-entering');
    display.setAttribute('aria-expanded', 'false');
    form.setAttribute('aria-hidden', 'true');
    if (restoreFocus) {
        display.focus();
    }
};

const openEditor = (container) => {
    const display = container.querySelector('.ui-inline-edit__display');
    const input = container.querySelector('.ui-inline-edit__input');
    const form = container.querySelector('.ui-inline-edit__form');
    if (!display || !input || !form) return;

    container.classList.add('is-active');
    container.classList.add('is-entering');
    display.setAttribute('aria-expanded', 'true');
    form.removeAttribute('aria-hidden');

    requestAnimationFrame(() => {
        container.classList.remove('is-entering');
        input.focus();
        input.select?.();
    });
};

export const initInlineEdit = () => {
    $$("[data-ui='inline-edit']").forEach((container) => {
        const display = container.querySelector('.ui-inline-edit__display');
        const form = container.querySelector('.ui-inline-edit__form');
        const input = container.querySelector('.ui-inline-edit__input');
        const cancelButton = container.querySelector('[data-action="cancel"]');
        if (!display || !form || !input || !cancelButton) return;

        form.setAttribute('aria-hidden', 'true');

        const state = getState(container);

        display.addEventListener('click', () => {
            if (container.classList.contains('is-active')) return;
            openEditor(container);
        });

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const previousValue = state.lastValue;
            const nextValue = input.value.trim();
            state.lastValue = nextValue;

            container.classList.add('is-busy');
            highlightChange(container);
            display.textContent = nextValue || display.dataset.placeholder || '';
            closeEditor(container, { restoreFocus: true });
            container.classList.remove('is-busy');
            setStatus(container, 'Alan güncellendi.');
            showUndo(container, previousValue, (value) => {
                input.value = value;
                state.lastValue = value;
                display.textContent = value || display.dataset.placeholder || '';
                setStatus(container, 'Değişiklik geri alındı.');
                bus.emit('ui:inline-edit:cancel', {
                    value,
                    element: container,
                    reason: 'undo',
                });
            });

            bus.emit('ui:inline-edit:save', {
                value: nextValue,
                previous: previousValue,
                element: container,
                field: container.dataset.field || null,
            });

            bus.emit('ui:toast:show', {
                message: `${container.dataset.field || 'Alan'} güncellendi`,
                variant: 'success',
                timeout: 2400,
            });
        });

        cancelButton.addEventListener('click', (event) => {
            event.preventDefault();
            input.value = state.lastValue;
            setStatus(container, 'Düzenleme iptal edildi.');
            closeEditor(container, { restoreFocus: true });
            bus.emit('ui:inline-edit:cancel', {
                value: state.lastValue,
                element: container,
                reason: 'cancel',
            });
        });

        container.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && container.classList.contains('is-active')) {
                event.preventDefault();
                input.value = state.lastValue;
                setStatus(container, 'Düzenleme iptal edildi.');
                closeEditor(container, { restoreFocus: true });
                bus.emit('ui:inline-edit:cancel', {
                    value: state.lastValue,
                    element: container,
                    reason: 'escape',
                });
            }
        });
    });
};
