(function () {
    window.Inventory = window.Inventory || {};

    const Warehouses = {
        selectors: {
            host: '.inv-warehouse',
            grid: '.inv-warehouse__grid',
            cell: '.inv-heat__cell',
            panel: '[data-panel-region]',
        },

        init() {
            this.cache();
            if (!this.$host) {
                return;
            }

            this.bind();
            this.calculateGrid();
        },

        cache() {
            this.$host = document.querySelector(this.selectors.host);
            if (!this.$host) {
                return;
            }

            this.$grid = this.$host.querySelector(this.selectors.grid);
            this.$panel = this.$host.querySelector(this.selectors.panel);
        },

        bind() {
            window.addEventListener('resize', () => this.calculateGrid());

            this.$host.addEventListener('click', (event) => {
                const cell = event.target.closest(this.selectors.cell);
                if (!cell) {
                    return;
                }

                event.preventDefault();
                this.selectCell(cell);
            });
        },

        calculateGrid() {
            if (!this.$grid) {
                return;
            }

            const columns = Math.floor(this.$grid.clientWidth / 140);
            if (columns > 0) {
                this.$grid.style.gridTemplateColumns = `repeat(${columns}, minmax(120px, 1fr))`;
            }
        },

        selectCell(cell) {
            this.$grid.querySelectorAll(this.selectors.cell).forEach((node) => node.dataset.selected = 'false');
            cell.dataset.selected = 'true';

            const payload = {
                rack: cell.dataset.rack,
                level: cell.dataset.level,
                items: JSON.parse(cell.dataset.items || '[]'),
            };

            this.renderPanel(payload);
            this.$host.dispatchEvent(new CustomEvent('inventory:warehouse:select', { detail: payload }));
        },

        renderPanel(payload) {
            if (!this.$panel) {
                return;
            }

            const items = payload.items.map((item) => `
                <article class="inv-card inv-card--product" data-product-id="${item.id}">
                    <div class="inv-card__header justify-content-between">
                        <div>
                            <div class="inv-card__title">${item.name || 'Ürün'}</div>
                            <div class="inv-card__subtitle">${item.sku || ''}</div>
                        </div>
                        <span class="badge bg-light text-dark">${item.qty}</span>
                    </div>
                    <div class="inv-card__body small text-muted">Rezerve: ${item.reserved}</div>
                    <div class="inv-warehouse__actions mt-3 d-flex gap-2 flex-wrap">
                        <button class="btn btn-outline-primary btn-sm" data-action="transfer" data-product-id="${item.id}">Transfer</button>
                        <button class="btn btn-outline-secondary btn-sm" data-action="adjust" data-product-id="${item.id}">Düzelt</button>
                        <button class="btn btn-outline-secondary btn-sm" data-action="label" data-product-id="${item.id}">Etiket</button>
                    </div>
                </article>
            `).join('');

            this.$panel.innerHTML = `
                <header class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Raf ${payload.rack}</h6>
                        <small class="text-muted">Seviye ${payload.level}</small>
                    </div>
                </header>
                <div class="inv-warehouse__item-list mt-3">${items}</div>
            `;
        },
    };

    window.Inventory.Warehouses = Warehouses;

    document.addEventListener('DOMContentLoaded', () => Warehouses.init());
})();
