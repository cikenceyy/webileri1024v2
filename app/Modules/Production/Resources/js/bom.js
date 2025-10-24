(function () {
    window.Production = window.Production || {};
    window.Inventory = window.Inventory || {};

    const Bom = {
        selectors: {
            host: '.inv-bom',
            lotToggle: '[data-action="bom-lot"]',
            row: '.inv-bom__row',
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

            this.$rows = Array.from(this.$host.querySelectorAll(this.selectors.row));
        },

        bind() {
            this.$host.addEventListener('click', (event) => {
                const toggle = event.target.closest(this.selectors.lotToggle);
                if (toggle) {
                    event.preventDefault();
                    this.changeLot(Number(toggle.dataset.value));
                }

                const action = event.target.closest('[data-action="bom-resolve"]');
                if (action) {
                    event.preventDefault();
                    this.dispatchResolve(action.dataset.materialId, action.dataset.actionType);
                }
            });
        },

        changeLot(lotSize) {
            if (!lotSize || !Number.isFinite(lotSize)) {
                return;
            }

            this.$rows.forEach((row) => {
                const base = Number(row.dataset.baseQty || 0);
                const onHand = Number(row.dataset.onHand || 0);
                const required = base * lotSize;
                const shortage = Math.max(0, required - onHand);
                const status = shortage > 0 ? 'insufficient' : 'ok';

                row.dataset.required = required;
                row.dataset.shortage = shortage;
                row.classList.toggle('inv-bom__row--insufficient', status === 'insufficient');

                const requiredTarget = row.querySelector('[data-field="required"]');
                const shortageTarget = row.querySelector('[data-field="shortage"]');

                if (requiredTarget) {
                    requiredTarget.textContent = required.toLocaleString();
                }

                if (shortageTarget) {
                    shortageTarget.textContent = shortage.toLocaleString();
                }
            });

            this.$host.dispatchEvent(new CustomEvent('production:bom:lot-changed', { detail: { lotSize } }));
            this.$host.dispatchEvent(new CustomEvent('inventory:bom:lot-changed', { detail: { lotSize } }));
        },

        dispatchResolve(materialId, actionType) {
            this.$host.dispatchEvent(new CustomEvent('production:bom:resolve', { detail: { materialId, actionType } }));
            this.$host.dispatchEvent(new CustomEvent('inventory:bom:resolve', { detail: { materialId, actionType } }));
        },
    };

    window.Production.Bom = Bom;
    window.Inventory.Bom = Bom;

    document.addEventListener('DOMContentLoaded', () => Bom.init());
})();
