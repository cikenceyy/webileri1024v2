import './bootstrap';
import './admin-runtime.js';

function bootModuleAssets() {
    const host = document.querySelector('.layout-main');
    if (!host) {
        return;
    }

    const moduleHint = (host.dataset.module || host.dataset.moduleSlug || '').trim();
    const moduleSlug = (host.dataset.moduleSlug || moduleHint).toLowerCase();
    const moduleName = host.dataset.module || (moduleSlug ? moduleSlug.charAt(0).toUpperCase() + moduleSlug.slice(1) : '');
    const pageName = (host.dataset.page || '').trim();

    if (!moduleSlug) {
        return;
    }

    const normalizedModule = moduleName.charAt(0).toUpperCase() + moduleName.slice(1);
    const moduleEntryPath = `@modules/${normalizedModule}/Resources/js/${moduleSlug}.js`;

    import(/* @vite-ignore */ moduleEntryPath)
        .then((mod) => {
            if (typeof mod?.default === 'function') {
                mod.default({ page: pageName });
            }
        })
        .catch(() => {
            // Module entry is optional; fail silently for modules without frontend assets.
        });
}

document.addEventListener('DOMContentLoaded', () => {
    window.dispatchEvent(new CustomEvent('admin:ready'));
    bootModuleAssets();
});
