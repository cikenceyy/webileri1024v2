const toggleSection = (item, expanded) => {
    const trigger = item.querySelector('[data-role="sidebar-trigger"]');
    const panel = item.querySelector('[data-role="sidebar-panel"]');

    if (!trigger || !panel) {
        return;
    }

    item.classList.toggle('is-open', expanded);
    trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    panel.toggleAttribute('hidden', !expanded);
};

export const initSidebarNavigation = () => {
    const sidebar = document.querySelector('#sidebar');
    if (!sidebar) {
        return;
    }

    const collapsibleItems = sidebar.querySelectorAll('[data-sidebar-collapsible]');
    collapsibleItems.forEach((item) => {
        const trigger = item.querySelector('[data-role="sidebar-trigger"]');
        const panel = item.querySelector('[data-role="sidebar-panel"]');

        if (!trigger || !panel) {
            return;
        }

        const expanded = item.classList.contains('is-open');
        toggleSection(item, expanded);

        trigger.addEventListener('click', () => {
            const next = !item.classList.contains('is-open');
            toggleSection(item, next);
        });
    });
};
