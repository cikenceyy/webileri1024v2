/**
 * Amaç: Layout üzerindeki veri öznitelikleri ile modül bootstrap bağını kurmak.
 * İlişkiler: PROMPT-4 — Layout & Runtime Bağı.
 * Notlar: Veri sağlanmadığında modül yüklemesi atlanır.
 */
import './bootstrap';
import './admin-runtime.js';

function bootModuleAssets() {
    const host =
        document.querySelector('.layout-main[data-ui="page-shell"]') || document.querySelector('.layout-main');

    if (!host) {
        return;
    }

    const moduleHandle = (host.dataset.moduleHandle || '').trim();
    const moduleSlug = (host.dataset.moduleSlug || moduleHandle).trim().toLowerCase();
    const pageName = (host.dataset.page || '').trim();

    if (!moduleHandle || !moduleSlug) {
        return;
    }

    const moduleEntryPath = `@modules/${moduleHandle}/Resources/js/${moduleSlug}.js`;

    import(/* @vite-ignore */ moduleEntryPath)
        .then((mod) => {
            if (typeof mod?.default === 'function') {
                mod.default({ page: pageName, host });
            }
        })
        .catch(() => {
            // Module entry is optional; eksik dosya sessizce yoksayılır.
        });
}

document.addEventListener('DOMContentLoaded', () => {
    window.dispatchEvent(new CustomEvent('admin:ready'));
    bootModuleAssets();
});
