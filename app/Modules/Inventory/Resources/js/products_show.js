(function () {
    window.Inventory = window.Inventory || {};

    const ProductsShow = {
        selectors: {
            host: '.inv-product',
            variant: '[data-action="select-variant"]',
            matrixCell: '.inv-matrix__cell',
            quickAction: '[data-action^="product-"]',
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
            if (!this.$host) {
                return;
            }

            this.$matrixTooltip = this.$host.querySelector('[data-matrix-tooltip]');
        },

        bind() {
            this.$host.addEventListener('click', (event) => {
                const variant = event.target.closest(this.selectors.variant);
                if (variant) {
                    event.preventDefault();
                    this.selectVariant(variant.dataset.variantId);
                    return;
                }

                const action = event.target.closest(this.selectors.quickAction);
                if (action) {
                    event.preventDefault();
                    this.dispatchAction(action.dataset.action, action.dataset);
                }
            });

            this.$host.addEventListener('mouseenter', (event) => {
                const cell = event.target.closest(this.selectors.matrixCell);
                if (cell) {
                    this.showTooltip(cell);
                }
            }, true);

            this.$host.addEventListener('mouseleave', (event) => {
                const cell = event.target.closest(this.selectors.matrixCell);
                if (cell) {
                    this.hideTooltip();
                }
            }, true);
        },

        selectVariant(variantId) {
            if (!variantId) {
                return;
            }

            const params = new URLSearchParams(window.location.search);
            params.set('variant', variantId);
            window.location.search = params.toString();
        },

        dispatchAction(action, dataset) {
            this.$host.dispatchEvent(new CustomEvent(`inventory:product:${action}`, { detail: { dataset } }));
        },

        showTooltip(cell) {
            if (!this.$matrixTooltip) {
                return;
            }

            const value = cell.dataset.value;
            const reserved = cell.dataset.reserved;
            const available = cell.dataset.available;

            this.$matrixTooltip.innerHTML = `
                <strong>${cell.dataset.warehouse}</strong>
                <div class="small text-muted">Mevcut: ${value}</div>
                <div class="small text-muted">Rezerve: ${reserved}</div>
                <div class="small text-muted">Kullanılabilir: ${available}</div>
            `;
            this.$matrixTooltip.hidden = false;
        },

        hideTooltip() {
            if (this.$matrixTooltip) {
                this.$matrixTooltip.hidden = true;
            }
        },
    };

    window.Inventory.ProductsShow = ProductsShow;

    document.addEventListener('DOMContentLoaded', () => ProductsShow.init());
})();
