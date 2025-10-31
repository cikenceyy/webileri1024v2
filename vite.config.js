import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

/**
 * Amaç: Vite girişlerini sadeleştirip yalnız kullanılan varlıkları derlemek.
 * İlişkiler: PROMPT-6 — Vite Girişlerinin Sadeleştirilmesi.
 * Notlar: Gereksiz tarama kaldırıldı, manifest manuel listelerle yönetiliyor.
 */

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

const moduleEntries = [
  'app/Modules/Drive/Resources/scss/drive.scss',
  'app/Modules/Drive/Resources/js/drive.js',
  'app/Modules/Inventory/Resources/scss/home.scss',
  'app/Modules/Inventory/Resources/js/home.js',
  'app/Modules/Inventory/Resources/scss/settings.scss',
  'app/Modules/Inventory/Resources/js/settings.js',
  'app/Modules/Inventory/Resources/scss/components.scss',
  'app/Modules/Inventory/Resources/js/components.js',
  'app/Modules/Inventory/Resources/scss/products_index.scss',
  'app/Modules/Inventory/Resources/js/products_index.js',
  'app/Modules/Inventory/Resources/scss/products_show.scss',
  'app/Modules/Inventory/Resources/js/products_show.js',
  'app/Modules/Inventory/Resources/scss/console.scss',
  'app/Modules/Inventory/Resources/js/console.js',
  'app/Modules/Marketing/Resources/scss/pricelists.scss',
  'app/Modules/Marketing/Resources/js/pricelists.js',
  'app/Modules/Marketing/Resources/scss/marketing.scss',
  'app/Modules/Marketing/Resources/js/marketing.js',
];

const pageEntries = [
  'resources/js/pages/settings-cache.js',
  'resources/js/pages/settings-email.js',
  'resources/js/pages/settings-general.js',
  'resources/js/pages/settings-modules.js',
];

export default defineConfig({
  plugins: [
    laravel({
      buildDirectory: 'cms',
      input: [
        'resources/js/app.js',
        'resources/scss/app.scss',
        'resources/scss/admin.scss',
        'resources/js/admin.js',
        'resources/scss/pages/ui-gallery.scss',
        'resources/js/pages/ui-gallery.js',
        'resources/js/pages/reports-index.js',
        'resources/css/tablekit.css',
        'resources/js/tablekit/index.js',
        ...pageEntries,
        ...cmsEntries,
        ...moduleEntries,
      ],
      refresh: true,
    }),
  ],
  css: {
    preprocessorOptions: {
      scss: {
        // Silence node_modules warnings from Dart Sass
        quietDeps: true,
      },
    },
  },
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
