(function () {
    window.Inventory = window.Inventory || {};

    const ProductsIndex = {
        selectors: {
            host: '.inv-products-list',
            viewToggle: '[data-action="toggle-view"]',
            chips: '[data-chip-action]',
            pagination: '[data-pagination] a',
        },

        init() {
            this.cache();
            if (!this.$host) {
                return;
            }

            this.bind();
        },

        cache() {
            this.$host = document.querySelector(this.selectors.host);
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
                }
            });
        },

        switchView(view) {
            if (!view) {
                return;
            }

            this.$host.dataset.view = view;
            this.$host.classList.toggle('inv-products-list--grid', view === 'grid');
            this.$host.classList.toggle('inv-products-list--table', view === 'table');
            this.$host.dispatchEvent(new CustomEvent('inventory:products:view', { detail: { view } }));
        },

        applyChip(chip) {
            const url = new URL(window.location.href);
            const param = chip.dataset.filter;
            const value = chip.dataset.value;

            if (!param) {
                return;
            }

            if (chip.dataset.chipAction === 'toggle-filter') {
                if (chip.classList.contains('is-active')) {
                    chip.classList.remove('is-active');
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
    };

    window.Inventory.ProductsIndex = ProductsIndex;

    document.addEventListener('DOMContentLoaded', () => ProductsIndex.init());
})();
