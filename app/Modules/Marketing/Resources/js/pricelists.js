(function () {
    window.Marketing = window.Marketing || {};
    window.Inventory = window.Inventory || {};

    const Pricelists = {
        selectors: {
            host: '.inv-prices',
            addRow: '[data-action="price-add"]',
            removeRow: '[data-action="price-remove"]',
            simSection: '[data-simulation]',
            simForm: '[data-sim-form]',
            simInputs: '[data-sim-input]',
            simOutput: '[data-sim-output]',
            simSummary: '[data-sim-summary]',
            simulateButton: '[data-action="simulate"]',
        },

        init() {
            this.cache();
            if (!this.$host) {
                return;
            }

            this.bind();
            this.runSimulation();
        },

        cache() {
            this.$host = document.querySelector(this.selectors.host);
            if (!this.$host) {
                return;
            }

            this.$items = this.$host.querySelector('[data-price-items]');
            this.$simSection = this.$host.querySelector(this.selectors.simSection);
            this.$simForm = this.$simSection ? this.$simSection.querySelector(this.selectors.simForm) : null;
            this.$simOutput = this.$simSection ? this.$simSection.querySelector(this.selectors.simOutput) : null;
            this.$simSummary = this.$simSection ? this.$simSection.querySelector(this.selectors.simSummary) : null;
            this.$simulateButton = this.$simSection ? this.$simSection.querySelector(this.selectors.simulateButton) : null;
            this.$simInputs = this.$simForm ? Array.from(this.$simForm.querySelectorAll(this.selectors.simInputs)) : [];
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
                    return;
                }

                const simulate = event.target.closest(this.selectors.simulateButton);
                if (simulate) {
                    event.preventDefault();
                    this.runSimulation();
                }
            });

            if (this.$simForm) {
                this.$simInputs.forEach((input) => {
                    input.addEventListener('input', () => this.runSimulation());
                    input.addEventListener('change', () => this.runSimulation());
                });
            }
        },

        addRow() {
            if (!this.template || !this.$items) {
                return;
            }

            const clone = this.template.content.cloneNode(true);
            this.$items.appendChild(clone);
            this.$host.dispatchEvent(new CustomEvent('marketing:pricelists:row-added'));
            this.$host.dispatchEvent(new CustomEvent('inventory:prices:row-added'));
        },

        removeRow(id) {
            if (!id || !this.$items) {
                return;
            }

            const row = this.$items.querySelector(`[data-row-id="${id}"]`);
            if (row) {
                row.remove();
                this.$host.dispatchEvent(new CustomEvent('marketing:pricelists:row-removed', { detail: { id } }));
                this.$host.dispatchEvent(new CustomEvent('inventory:prices:row-removed', { detail: { id } }));
            }
        },

        runSimulation() {
            if (!this.$simForm) {
                return;
            }

            const formData = new FormData(this.$simForm);
            const quantity = Math.max(0, Number(formData.get('quantity') || 0));
            const price = Math.max(0, Number(formData.get('price') || 0));
            const discount = Math.min(100, Math.max(0, Number(formData.get('discount') || 0)));
            const currency = (formData.get('currency') || 'TRY').toString().toUpperCase();

            const subtotal = quantity * price;
            const discountAmount = subtotal * (discount / 100);
            const total = Math.max(0, subtotal - discountAmount);

            if (this.$simOutput) {
                this.$simOutput.textContent = this.formatCurrency(total, currency);
            }

            if (this.$simSummary) {
                const unit = this.formatCurrency(price, currency);
                const parts = [`${quantity} × ${unit}`];

                if (discount > 0) {
                    parts.push(`− ${discount}% (${this.formatCurrency(discountAmount, currency)} indirim)`);
                } else {
                    parts.push('İndirim uygulanmadı');
                }

                parts.push(`= ${this.formatCurrency(total, currency)}`);

                this.$simSummary.textContent = parts.join(' ');
            }
        },

        formatCurrency(value, currency) {
            return new Intl.NumberFormat(undefined, {
                style: 'currency',
                currency,
                maximumFractionDigits: 2,
            }).format(Number.isFinite(value) ? value : 0);
        },
    };

    window.Marketing.Pricelists = Pricelists;
    window.Inventory.Pricelists = Pricelists;

    document.addEventListener('DOMContentLoaded', () => Pricelists.init());
})();
