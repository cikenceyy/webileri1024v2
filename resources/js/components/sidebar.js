const STORAGE_KEY = 'ui:sidebar:collapsible:v1';

const readStoredState = () => {
    if (typeof window === 'undefined') {
        return {};
    }

    try {
        const raw = window.localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : {};
    } catch (error) {
        return {};
    }
};

const writeStoredState = (value) => {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
    } catch (error) {
        // Storage might be unavailable (private mode, etc.). Fail silently.
    }
};

const applySectionState = (item, expanded) => {
    const trigger = item.querySelector('[data-role="sidebar-trigger"]');
    const panel = item.querySelector('[data-role="sidebar-panel"]');

    if (!trigger || !panel) {
        return;
    }

    if (panel.id && trigger.getAttribute('aria-controls') !== panel.id) {
        trigger.setAttribute('aria-controls', panel.id);
    }

    if (trigger.id && panel.getAttribute('aria-labelledby') !== trigger.id) {
        panel.setAttribute('aria-labelledby', trigger.id);
    }

    if (!panel.hasAttribute('role')) {
        panel.setAttribute('role', 'region');
    }

    item.classList.toggle('is-open', expanded);
    trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    panel.hidden = !expanded;
    panel.setAttribute('aria-hidden', expanded ? 'false' : 'true');
};

export const initSidebarNavigation = () => {
    const sidebar = document.querySelector('#sidebar');
    if (!sidebar) {
        return;
    }

    const collapsibleItems = sidebar.querySelectorAll('[data-sidebar-collapsible]');
    const storedState = readStoredState();

    collapsibleItems.forEach((item) => {
        const trigger = item.querySelector('[data-role="sidebar-trigger"]');
        const panel = item.querySelector('[data-role="sidebar-panel"]');

        if (!trigger || !panel) {
            return;
        }

        const id = item.dataset.sidebarId;

        if (!trigger.id && id) {
            trigger.id = `${id}-trigger`;
        }

        if (!panel.id && id) {
            panel.id = `${id}-panel`;
        }

        const hasStoredValue = id ? Object.prototype.hasOwnProperty.call(storedState, id) : false;
        const expanded = hasStoredValue ? Boolean(storedState[id]) : item.classList.contains('is-open');
        applySectionState(item, expanded);

        const persist = (next) => {
            if (!id) {
                return;
            }
            storedState[id] = next;
            writeStoredState(storedState);
        };

        const toggle = () => {
            const next = !item.classList.contains('is-open');
            applySectionState(item, next);
            persist(next);
        };

        trigger.addEventListener('click', toggle);
        trigger.addEventListener('keydown', (event) => {
            if (event.key === ' ' || event.key === 'Enter') {
                event.preventDefault();
                toggle();
            }
        });
    });
};
