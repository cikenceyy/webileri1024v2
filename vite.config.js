import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { readdirSync, statSync } from 'node:fs';
import { createRequire } from 'module';

const require = createRequire(import.meta.url);
const projectRoot = path.resolve();

function moduleEntries() {
    const base = 'app/Modules';
    let globber;

    try {
        // Optional dependency; falls back to manual walker when unavailable.
        globber = require('fast-glob');
    } catch (error) {
        globber = null;
    }

    if (globber?.sync) {
        const js = globber.sync(`${base}/**/Resources/js/*.js`, { dot: false });
        const scss = globber.sync(`${base}/**/Resources/scss/*.scss`, { dot: false });
        return [...js, ...scss];
    }

    const entries = [];
    const basePath = path.resolve(projectRoot, base);

    const walk = (currentDir) => {
        const items = readdirSync(currentDir, { withFileTypes: true });

        items.forEach((item) => {
            const fullPath = path.join(currentDir, item.name);

            if (item.isDirectory()) {
                walk(fullPath);
                return;
            }

            if (fullPath.endsWith('.js') || fullPath.endsWith('.scss')) {
                const relativePath = path.relative(projectRoot, fullPath).split(path.sep).join('/');
                entries.push(relativePath);
            }
        });
    };

    try {
        if (statSync(basePath).isDirectory()) {
            walk(basePath);
        }
    } catch (error) {
        // Modules directory may not exist yet during early setups.
    }

    return entries;
}

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/scss/app.scss',
                'resources/scss/admin.scss',
                'resources/js/admin.js',
                'resources/scss/pages/ui-gallery.scss',
                'resources/js/pages/ui-gallery.js',
                // Inventory module assets
                'resources/scss/inventory/home.scss',
                'resources/scss/inventory/stock_console.scss',
                'resources/scss/inventory/products_index.scss',
                'resources/scss/inventory/products_show.scss',
                'resources/scss/inventory/warehouses.scss',
                'resources/scss/inventory/pricelists.scss',
                'resources/scss/inventory/bom.scss',
                'resources/scss/inventory/components.scss',
                'resources/scss/inventory/settings.scss',
                'resources/js/inventory/home.js',
                'resources/js/inventory/stock_console.js',
                'resources/js/inventory/products_index.js',
                'resources/js/inventory/products_show.js',
                'resources/js/inventory/warehouses.js',
                'resources/js/inventory/pricelists.js',
                'resources/js/inventory/bom.js',
                'resources/js/inventory/components.js',
                'resources/js/inventory/settings.js',
                ...moduleEntries(),
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(projectRoot, 'resources'),
            '@modules': path.resolve(projectRoot, 'app/Modules'),
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    const normalizedId = id.split(path.sep).join('/');

                    if (normalizedId.includes('node_modules')) {
                        if (normalizedId.includes('bootstrap') || normalizedId.includes('@popperjs/core')) {
                            return 'vendor-bootstrap';
                        }
                        if (normalizedId.includes('axios')) {
                            return 'vendor-axios';
                        }
                        return 'vendor';
                    }

                    const moduleMatch = normalizedId.match(/app\/Modules\/([^/]+)\/Resources\//);
                    if (moduleMatch) {
                        return `mod-${moduleMatch[1].toLowerCase()}`;
                    }
                },
            },
        },
    },
    server: {
        watch: { usePolling: true, interval: 100 },
    },
});
