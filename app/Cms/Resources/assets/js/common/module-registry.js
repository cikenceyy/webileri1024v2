import { ioReveal } from './io-reveal';
import { lazyMedia } from './lazy-media';
import { mapLoader } from './map-loader';
import { formValidate } from './form-validate';
import { beacon } from './beacon';
import { skeletons } from './skeletons';
import { navToggle } from './nav-toggle';
import { stickyHeader } from './sticky-header';
import { lightGallery } from './light-gallery';

const registry = {
    reveal: ioReveal,
    'lazy-media': lazyMedia,
    'map-on-demand': mapLoader,
    'contact-form': formValidate,
    beacon,
    skeletons,
    'nav-toggle': navToggle,
    'sticky-header': stickyHeader,
    'light-gallery': lightGallery,
};

export function initModules(root = document) {
    const nodes = root.querySelectorAll('[data-module]');

    nodes.forEach((node) => {
        const modules = node.getAttribute('data-module');
        if (!modules) {
            return;
        }

        modules.split(/\s+/).forEach((name) => {
            const initializer = registry[name];
            if (typeof initializer !== 'function') {
                return;
            }

            const dataKey = `module${name.replace(/-([a-z])/g, (_, char) => char.toUpperCase()).replace(/[^a-zA-Z0-9_]/g, '')}`;
            if (node.dataset[dataKey]) {
                return;
            }

            initializer(node);
            node.dataset[dataKey] = 'ready';
        });
    });
}
