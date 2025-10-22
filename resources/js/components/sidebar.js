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

const ensurePanelAccessibility = (item, trigger, panel) => {
    if (!panel.id) {
        const baseId = item.dataset.sidebarId || `sidebar-node-${Math.random().toString(16).slice(2)}`;
        panel.id = `${baseId}-panel`;
    }

    if (!trigger.id) {
        const baseId = panel.id.replace(/-panel$/, '');
        trigger.id = `${baseId}-trigger`;
    }

    if (trigger.getAttribute('aria-controls') !== panel.id) {
        trigger.setAttribute('aria-controls', panel.id);
    }

    if (panel.getAttribute('aria-labelledby') !== trigger.id) {
        panel.setAttribute('aria-labelledby', trigger.id);
    }

    if (!panel.hasAttribute('role')) {
        panel.setAttribute('role', 'region');
    }
};

const applySectionState = (item, expanded) => {
    const trigger = item.querySelector('.ui-sidebar__trigger');
    const panel = item.querySelector('.ui-sidebar__panel');

    if (!trigger || !panel) {
        return;
    }

    ensurePanelAccessibility(item, trigger, panel);

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

    const storedState = readStoredState();
    const collapsibleItems = Array.from(sidebar.querySelectorAll('.ui-sidebar__item.has-children'));

    const persistState = (id, value) => {
        if (!id) return;
        storedState[id] = value;
        writeStoredState(storedState);
    };

    const findPanelState = (item) => {
        const id = item.dataset.sidebarId;
        const hasActiveChild = Boolean(item.querySelector('.ui-sidebar__subitem.is-active, .ui-sidebar__link[aria-current="page"]'));
        if (hasActiveChild) {
            if (id) {
                persistState(id, true);
            }
            return true;
        }

        if (id && Object.prototype.hasOwnProperty.call(storedState, id)) {
            return Boolean(storedState[id]);
        }

        return item.classList.contains('is-open');
    };

    const setExpanded = (item, expanded) => {
        applySectionState(item, expanded);
        persistState(item.dataset.sidebarId, expanded);
    };

    collapsibleItems.forEach((item, index) => {
        if (!item.dataset.sidebarId) {
            item.dataset.sidebarId = item.dataset.sidebarId || item.id || `sidebar-node-${index}`;
        }

        const expanded = findPanelState(item);
        applySectionState(item, expanded);
    });

    const collapseSiblings = (current) => {
        collapsibleItems.forEach((item) => {
            if (item !== current) {
                setExpanded(item, false);
            }
        });
    };

    const toggleItem = (item) => {
        const expanded = item.classList.contains('is-open');
        const next = !expanded;
        if (next) {
            collapseSiblings(item);
        }
        setExpanded(item, next);
    };

    const handleTrigger = (trigger) => {
        const item = trigger.closest('.ui-sidebar__item.has-children');
        if (!item) {
            return;
        }

        toggleItem(item);
    };

    sidebar.addEventListener('click', (event) => {
        const trigger = event.target.closest('.ui-sidebar__trigger');
        if (!trigger || !sidebar.contains(trigger)) {
            return;
        }

        event.preventDefault();
        handleTrigger(trigger);
    });

    sidebar.addEventListener('keydown', (event) => {
        if (event.key !== ' ' && event.key !== 'Enter') {
            return;
        }

        const trigger = event.target.closest('.ui-sidebar__trigger');
        if (!trigger || !sidebar.contains(trigger)) {
            return;
        }

        event.preventDefault();
        handleTrigger(trigger);
    });
};
