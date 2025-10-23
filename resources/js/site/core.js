import { domReady } from './utilities/domReady';
import { initModules } from './modules/registry';

export function bootPage(pageId, callback = () => {}) {
    domReady(() => {
        initModules();
        const current = document.body.dataset.page;
        if (!pageId || current === pageId) {
            callback();
        }
    });
}
