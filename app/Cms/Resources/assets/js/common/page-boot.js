import { domReady } from './dom-ready';
import { initModules } from './module-registry';

export function bootPage(pageId, callback = () => {}) {
    domReady(() => {
        initModules();
        const current = document.body.dataset.page;
        if (!pageId || current === pageId) {
            callback();
        }
    });
}
