import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { readdirSync, statSync } from 'node:fs';
const projectRoot = path.resolve();

const cmsEntries = [
    'app/Cms/Resources/assets/scss/site/home.scss',
    'app/Cms/Resources/assets/scss/site/corporate.scss',
    'app/Cms/Resources/assets/scss/site/contact.scss',
    'app/Cms/Resources/assets/scss/site/kvkk.scss',
    'app/Cms/Resources/assets/scss/site/catalogs.scss',
    'app/Cms/Resources/assets/scss/site/products.scss',
    'app/Cms/Resources/assets/scss/site/product-show.scss',
    'app/Cms/Resources/assets/scss/admin/editor.scss',
    'app/Cms/Resources/assets/js/site/home.js',
    'app/Cms/Resources/assets/js/site/corporate.js',
    'app/Cms/Resources/assets/js/site/contact.js',
    'app/Cms/Resources/assets/js/site/kvkk.js',
    'app/Cms/Resources/assets/js/site/catalogs.js',
    'app/Cms/Resources/assets/js/site/products.js',
    'app/Cms/Resources/assets/js/site/product-show.js',
    'app/Cms/Resources/assets/js/admin/editor/index.js',
];

function moduleEntries(base = 'app/Modules') {
    const entries = [];
    const root = path.resolve(projectRoot, base);

    const walk = (current) => {
        readdirSync(current, { withFileTypes: true }).forEach((item) => {
            const fullPath = path.join(current, item.name);

            if (item.isDirectory()) {
                walk(fullPath);
                return;
            }

            if (fullPath.endsWith('.js') || fullPath.endsWith('.scss')) {
                entries.push(path.relative(projectRoot, fullPath).split(path.sep).join('/'));
            }
        });
    };

    try {
        if (statSync(root).isDirectory()) {
            walk(root);
        }
    } catch (error) {
        // modules directory optional
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
                ...cmsEntries,
                ...moduleEntries(),
            ],
            refresh: true,
            buildDirectory: 'build/cms',
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(projectRoot, 'resources'),
            '@modules': path.resolve(projectRoot, 'app/Modules'),
            '@cms': path.resolve(projectRoot, 'app/Cms/Resources/assets'),
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

                    if (normalizedId.includes('app/Cms/Resources/')) {
                        return 'cms';
                    }
                },
            },
        },
    },
    server: {
        watch: { usePolling: true, interval: 100 },
    },
});
