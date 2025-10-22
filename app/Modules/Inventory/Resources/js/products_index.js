(function () {
    window.Inventory = window.Inventory || {};

    const ProductsIndex = {
        selectors: {
            host: '.inv-products-list',
            viewToggle: '[data-action="toggle-view"]',
            chips: '[data-chip-action]',
            pagination: '[data-pagination] a',
            printLabel: '[data-action="print-label"]',
        },

        init() {
            this.cache();
            if (!this.$host) {
                return;
            }

            this.bind();
            this.syncPanels();
        },

        cache() {
            this.$host = document.querySelector(this.selectors.host);
            if (!this.$host) {
                return;
            }

            this.$panels = this.$host.querySelectorAll('[data-view-panel]');
        },

        bind() {
            this.$host.addEventListener('click', (event) => {
                const toggle = event.target.closest(this.selectors.viewToggle);
                if (toggle) {
                    event.preventDefault();
                    this.switchView(toggle.dataset.view);
                    return;
                }

                const chip = event.target.closest('[data-chip-action]');
                if (chip) {
                    event.preventDefault();
                    this.applyChip(chip);
                    return;
                }

                const pagination = event.target.closest('[data-pagination] a');
                if (pagination) {
                    this.navigate(pagination.href);
                    return;
                }

                const print = event.target.closest(this.selectors.printLabel);
                if (print) {
                    event.preventDefault();
                    this.printLabel(print.dataset.labelUrl);
                }
            });
        },

        switchView(view) {
            if (!view) {
                return;
            }

            const url = new URL(window.location.href);
            url.searchParams.set('view', view);
            window.location.href = url.toString();
        },

        applyChip(chip) {
            const url = new URL(window.location.href);
            const param = chip.dataset.filter;
            const value = chip.dataset.value;

            if (!param) {
                return;
            }

            if (chip.dataset.chipAction === 'toggle-filter') {
                const isActive = chip.classList.contains('is-active');
                this.$host.querySelectorAll(`[data-chip-action="toggle-filter"][data-filter="${param}"]`).forEach((node) => node.classList.remove('is-active'));

                if (isActive) {
                    url.searchParams.delete(param);
                } else {
                    chip.classList.add('is-active');
                    url.searchParams.set(param, value);
                }
            }

            window.location.href = url.toString();
        },

        navigate(url) {
            if (!url) {
                return;
            }

            window.location.href = url;
        },

        syncPanels() {
            if (!this.$panels) {
                return;
            }

            const activeView = this.$host.dataset.view;
            this.$panels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.dataset.viewPanel === activeView);
                panel.hidden = panel.dataset.viewPanel !== activeView;
            });
        },

        printLabel(url) {
            if (!url) {
                return;
            }

            window.open(url, '_blank', 'noopener');
        },
    };

    window.Inventory.ProductsIndex = ProductsIndex;

    document.addEventListener('DOMContentLoaded', () => ProductsIndex.init());
})();
