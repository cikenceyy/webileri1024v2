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

    if (trigger.dataset.sidebarTarget !== panel.id) {
        trigger.dataset.sidebarTarget = panel.id;
    }

    if (panel.getAttribute('aria-labelledby') !== trigger.id) {
        panel.setAttribute('aria-labelledby', trigger.id);
    }

    if (!panel.hasAttribute('role')) {
        panel.setAttribute('role', 'region');
    }
};

const findTrigger = (item) =>
    item.querySelector('[data-role="sidebar-trigger"]') || item.querySelector('.ui-sidebar__trigger');

const resolvePanel = (item, trigger, ownerDocument) => {
    if (trigger) {
        const targetId = trigger.dataset.sidebarTarget || trigger.getAttribute('aria-controls');
        if (targetId) {
            const byId = ownerDocument.getElementById(targetId);
            if (byId) {
                return byId;
            }
        }
    }

    return (
        item.querySelector('[data-role="sidebar-panel"]') ||
        item.querySelector('.ui-sidebar__panel')
    );
};

const applySectionState = (item, trigger, panel, expanded) => {
    if (!trigger || !panel) {
        return;
    }

    ensurePanelAccessibility(item, trigger, panel);

    item.classList.toggle('is-open', expanded);
    trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');

    if (expanded) {
        panel.removeAttribute('hidden');
        panel.setAttribute('aria-hidden', 'false');
    } else {
        panel.setAttribute('hidden', '');
        panel.setAttribute('aria-hidden', 'true');
    }
};

export const initSidebarNavigation = () => {
    const sidebar = document.querySelector('#sidebar');
    if (!sidebar) {
        return;
    }

    const storedState = readStoredState();
    const ownerDocument = sidebar.ownerDocument || document;
    const collapsibleItems = Array.from(
        sidebar.querySelectorAll('[data-sidebar-collapsible], .ui-sidebar__item.has-children')
    );
    const itemParts = new Map();

    const persistState = (id, value) => {
        if (!id) return;
        storedState[id] = value;
        writeStoredState(storedState);
    };

    const findPanelState = (item, trigger) => {
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

        if (trigger) {
            return trigger.getAttribute('aria-expanded') === 'true';
        }

        return item.classList.contains('is-open');
    };

    const setExpanded = (item, parts, expanded) => {
        if (!parts) {
            return;
        }

        applySectionState(item, parts.trigger, parts.panel, expanded);
        persistState(item.dataset.sidebarId, expanded);
    };

    collapsibleItems.forEach((item, index) => {
        if (!item.dataset.sidebarId) {
            item.dataset.sidebarId = item.dataset.sidebarId || item.id || `sidebar-node-${index}`;
        }

        const trigger = findTrigger(item);
        const panel = resolvePanel(item, trigger, ownerDocument);
        if (!trigger || !panel) {
            return;
        }

        itemParts.set(item, { trigger, panel });

        const expanded = findPanelState(item, trigger);
        applySectionState(item, trigger, panel, expanded);

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const next = trigger.getAttribute('aria-expanded') !== 'true';
            if (next) {
                itemParts.forEach((parts, other) => {
                    if (other !== item) {
                        setExpanded(other, parts, false);
                    }
                });
            }
            setExpanded(item, itemParts.get(item), next);
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key !== ' ' && event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            trigger.click();
        });
    });
};
