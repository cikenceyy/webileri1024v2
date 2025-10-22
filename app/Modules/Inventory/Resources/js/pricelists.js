(function () {
    window.Inventory = window.Inventory || {};

    const Pricelists = {
        selectors: {
            host: '.inv-prices',
            addRow: '[data-action="price-add"]',
            removeRow: '[data-action="price-remove"]',
            simForm: '[data-sim-form]',
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

            this.$items = this.$host.querySelector('[data-price-items]');
            this.$simForm = this.$host.querySelector(this.selectors.simForm);
            this.template = document.getElementById('tpl-price-row');
        },

        bind() {
            this.$host.addEventListener('click', (event) => {
                const add = event.target.closest(this.selectors.addRow);
                if (add) {
                    event.preventDefault();
                    this.addRow();
                    return;
                }

                const remove = event.target.closest(this.selectors.removeRow);
                if (remove) {
                    event.preventDefault();
                    this.removeRow(remove.dataset.rowId);
                }
            });

            if (this.$simForm) {
                this.$simForm.addEventListener('input', () => this.runSimulation());
            }
        },

        addRow() {
            if (!this.template || !this.$items) {
                return;
            }

            const clone = this.template.content.cloneNode(true);
            this.$items.appendChild(clone);
            this.$host.dispatchEvent(new CustomEvent('inventory:prices:row-added'));
        },

        removeRow(id) {
            if (!id || !this.$items) {
                return;
            }

            const row = this.$items.querySelector(`[data-row-id="${id}"]`);
            if (row) {
                row.remove();
                this.$host.dispatchEvent(new CustomEvent('inventory:prices:row-removed', { detail: { id } }));
            }
        },

        runSimulation() {
            if (!this.$simForm) {
                return;
            }

            const formData = new FormData(this.$simForm);
            const quantity = Number(formData.get('quantity') || 1);
            const price = Number(formData.get('price') || 0);
            const discount = Number(formData.get('discount') || 0);

            const total = Math.max(0, quantity * price * (1 - discount / 100));
            const target = this.$host.querySelector('[data-sim-output]');
            if (target) {
                target.textContent = new Intl.NumberFormat(undefined, { style: 'currency', currency: formData.get('currency') || 'TRY' }).format(total);
            }
        },
    };

    window.Inventory.Pricelists = Pricelists;

    document.addEventListener('DOMContentLoaded', () => Pricelists.init());
})();
