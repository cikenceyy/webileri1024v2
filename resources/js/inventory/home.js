(function () {
    const POLL_INTERVAL = 60_000;

    window.Inventory = window.Inventory || {};

    const Home = {
        selectors: {
            host: '.inv-home',
            kpiRegion: '[data-kpi-region]',
            timelineRegion: '[data-timeline-region]',
            lowStockRegion: '[data-lowstock-region]',
            quickAction: '[data-action="inventory-quick"]',
        },
        pollTimer: null,

        init() {
            this.cache();
            if (!this.$host) {
                return;
            }

            this.bind();
            this.refreshKpis();
            this.refreshTimeline();
            this.refreshLowStock();
            this.startPolling();
        },

        cache() {
            this.$host = document.querySelector(this.selectors.host);
            if (!this.$host) {
                return;
            }

            this.$kpis = this.$host.querySelector(this.selectors.kpiRegion);
            this.$timeline = this.$host.querySelector(this.selectors.timelineRegion);
            this.$lowstock = this.$host.querySelector(this.selectors.lowStockRegion);
        },

        bind() {
            this.$host.addEventListener('click', (event) => {
                const target = event.target.closest(this.selectors.quickAction);
                if (target) {
                    event.preventDefault();
                    this.handleQuickAction(target.dataset.mode || 'in');
                }

                const lowStockCard = event.target.closest('[data-action="inventory-lowstock"]');
                if (lowStockCard) {
                    event.preventDefault();
                    this.openLowStockSheet(lowStockCard.dataset.productId);
                }
            });

            const sheet = this.$host.querySelector('[data-sheet="lowstock"]');
            if (sheet) {
                sheet.addEventListener('click', (event) => {
                    const dismiss = event.target.closest('[data-action="sheet-dismiss"]');
                    if (dismiss) {
                        event.preventDefault();
                        sheet.classList.remove('is-open');
                    }
                });
            }
        },

        startPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
            }

            this.pollTimer = window.setInterval(() => {
                this.refreshKpis();
                this.refreshTimeline();
                this.refreshLowStock();
            }, POLL_INTERVAL);
        },

        refreshKpis() {
            if (!this.$kpis) {
                return;
            }

            const endpoint = this.$kpis.dataset.endpoint;
            if (!endpoint) {
                return;
            }

            fetch(endpoint)
                .then((response) => response.ok ? response.json() : null)
                .then((payload) => {
                    if (!payload) {
                        return;
                    }
                    this.renderKpis(payload);
                })
                .catch(() => {});
        },

        refreshTimeline() {
            if (!this.$timeline) {
                return;
            }

            const endpoint = this.$timeline.dataset.endpoint;
            if (!endpoint) {
                return;
            }

            fetch(endpoint)
                .then((response) => response.ok ? response.json() : null)
                .then((payload) => {
                    if (!payload) {
                        return;
                    }
                    this.renderTimeline(payload);
                })
                .catch(() => {});
        },

        refreshLowStock() {
            if (!this.$lowstock) {
                return;
            }

            const endpoint = this.$lowstock.dataset.endpoint;
            if (!endpoint) {
                return;
            }

            fetch(endpoint)
                .then((response) => response.ok ? response.json() : null)
                .then((payload) => {
                    if (!payload) {
                        return;
                    }
                    this.renderLowStock(payload);
                })
                .catch(() => {});
        },

        renderKpis(payload) {
            this.$kpis.innerHTML = payload
                .map((item) => `
                    <article class="inv-card--kpi" aria-live="polite">
                        <span class="inv-card--kpi__label">${item.label}</span>
                        <strong class="inv-card--kpi__value">${item.value}</strong>
                        <span class="inv-card--kpi__trend">${item.trend || ''}</span>
                    </article>
                `)
                .join('');
        },

        renderTimeline(payload) {
            this.$timeline.innerHTML = payload
                .map((item) => `
                    <div class="inv-timeline__item">
                        <div>
                            <div class="fw-medium">${item.title}</div>
                            <div class="text-muted small">${item.subtitle || ''}</div>
                        </div>
                        <time class="text-muted small" datetime="${item.timestamp}">${item.timeLabel}</time>
                    </div>
                `)
                .join('');
        },

        renderLowStock(payload) {
            this.$lowstock.innerHTML = payload
                .map((item) => `
                    <article class="inv-card--lowstock ${item.isCritical ? 'inv-card--lowstock--critical' : ''}" data-action="inventory-lowstock" data-product-id="${item.id}">
                        <div class="inv-card--lowstock__meta">
                            <span class="fw-medium">${item.name}</span>
                            <span class="text-muted small">${item.sku}</span>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-danger-subtle text-danger">${item.available}</span>
                        </div>
                        <div class="inv-card--lowstock__actions">
                            <button class="btn btn-outline-primary btn-sm" type="button" data-action="trigger-transfer" data-product-id="${item.id}">Transfer</button>
                            <button class="btn btn-primary btn-sm" type="button" data-action="trigger-purchase" data-product-id="${item.id}">Tedarik</button>
                        </div>
                    </article>
                `)
                .join('');
        },

        handleQuickAction(mode) {
            const targetUrl = this.$host.dataset.consoleUrl;
            if (!targetUrl) {
                return;
            }

            const url = new URL(targetUrl, window.location.origin);
            url.searchParams.set('mode', mode);
            window.location.href = url.toString();
        },

        openLowStockSheet(productId) {
            const sheet = this.$host.querySelector('[data-sheet="lowstock"]');
            if (!sheet) {
                return;
            }

            sheet.classList.add('is-open');
            sheet.dispatchEvent(new CustomEvent('inventory:lowstock:open', { detail: { productId } }));
        },
    };

    window.Inventory.Home = Home;

    document.addEventListener('DOMContentLoaded', () => Home.init());
})();
