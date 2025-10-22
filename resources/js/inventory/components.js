(function () {
    window.Inventory = window.Inventory || {};

    const Components = {
        selectors: {
            host: '.inv-components',
            lotForm: '[data-components-lot]',
            resolve: '[data-action="components-resolve"]',
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

            this.$lotForm = this.$host.querySelector(this.selectors.lotForm);
            this.$cards = Array.from(this.$host.querySelectorAll('[data-component-card]'));
        },

        bind() {
            if (this.$lotForm) {
                this.$lotForm.addEventListener('change', () => this.recalculate());
            }

            this.$host.addEventListener('click', (event) => {
                const action = event.target.closest(this.selectors.resolve);
                if (action) {
                    event.preventDefault();
                    this.dispatchResolve(action.dataset.componentId, action.dataset.actionType);
                }
            });
        },

        recalculate() {
            const lot = Number(new FormData(this.$lotForm).get('lot') || 1);
            if (!Number.isFinite(lot) || lot <= 0) {
                return;
            }

            this.$cards.forEach((card) => {
                const base = Number(card.dataset.baseQty || 0);
                const onHand = Number(card.dataset.onHand || 0);
                const required = base * lot;
                const shortage = Math.max(0, required - onHand);

                card.querySelector('[data-field="required"]').textContent = required.toLocaleString();
                card.querySelector('[data-field="shortage"]').textContent = shortage.toLocaleString();
                card.classList.toggle('inv-components__card--insufficient', shortage > 0);
            });

            this.$host.dispatchEvent(new CustomEvent('inventory:components:lot', { detail: { lot } }));
        },

        dispatchResolve(componentId, actionType) {
            this.$host.dispatchEvent(new CustomEvent('inventory:components:resolve', { detail: { componentId, actionType } }));
        },
    };

    window.Inventory.Components = Components;

    document.addEventListener('DOMContentLoaded', () => Components.init());
})();
