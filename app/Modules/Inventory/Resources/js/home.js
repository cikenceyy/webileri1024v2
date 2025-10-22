(function () {
    const POLL_INTERVAL = 60_000;

    window.Inventory = window.Inventory || {};

    const requestJson = (endpoint, onSuccess) => {
        if (!endpoint) {
            return;
        }

        fetch(endpoint)
            .then((response) => (response.ok ? response.json() : null))
            .then((payload) => {
                if (!payload) {
                    return;
                }

                onSuccess(payload);
            })
            .catch(() => {});
    };

    const Home = {
        selectors: {
            host: '.inv-home',
            kpiRegion: '[data-kpi-region]',
            timelineRegion: '[data-timeline-region]',
            timelineList: '[data-timeline-list]',
            lowStockRegion: '[data-lowstock-region]',
            lowStockList: '[data-lowstock-list]',
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
            this.$timelineSection = this.$host.querySelector(this.selectors.timelineRegion);
            this.$timelineList = this.$host.querySelector(this.selectors.timelineList);
            this.$lowstockSection = this.$host.querySelector(this.selectors.lowStockRegion);
            this.$lowstockList = this.$host.querySelector(this.selectors.lowStockList);
            this.$sheet = this.$host.querySelector('[data-sheet="lowstock"]');
        },

        bind() {
            this.$host.addEventListener('click', (event) => {
                const quickAction = event.target.closest(this.selectors.quickAction);
                if (quickAction) {
                    event.preventDefault();
                    this.handleQuickAction(quickAction.dataset.mode || 'in', quickAction.getAttribute('href'));
                    return;
                }

                const lowStockCard = event.target.closest('[data-action="inventory-lowstock"]');
                if (lowStockCard) {
                    event.preventDefault();
                    this.openLowStockSheet(lowStockCard);
                }
            });

            if (this.$sheet) {
                this.$sheet.addEventListener('click', (event) => {
                    const dismiss = event.target.closest('[data-action="sheet-dismiss"]');
                    if (dismiss) {
                        event.preventDefault();
                        this.closeLowStockSheet();
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

            const endpoint = this.$kpis.dataset.endpoint || this.$host.dataset.kpiEndpoint;
            requestJson(endpoint, (payload) => this.renderKpis(payload));
        },

        refreshTimeline() {
            if (!this.$timelineList) {
                return;
            }

            const endpoint = (this.$timelineSection?.dataset.endpoint) || this.$host.dataset.timelineEndpoint;
            requestJson(endpoint, (payload) => this.renderTimeline(payload));
        },

        refreshLowStock() {
            if (!this.$lowstockList) {
                return;
            }

            const endpoint = (this.$lowstockSection?.dataset.endpoint) || this.$lowstockList.dataset.endpoint || this.$host.dataset.lowstockEndpoint;
            requestJson(endpoint, (payload) => this.renderLowStock(payload));
        },

        renderKpis(payload) {
            this.$kpis.innerHTML = payload
                .map((item) => `
                    <article class="inv-card inv-card--kpi" aria-live="polite">
                        <div class="inv-card__meta">
                            <span class="inv-card__icon"><i class="bi ${item.icon || 'bi-circle'}"></i></span>
                            <span class="inv-card__label">${item.label}</span>
                        </div>
                        <strong class="inv-card__value">${item.value}</strong>
                        ${item.trend ? `<span class="inv-card__trend">${item.trend}</span>` : ''}
                    </article>
                `)
                .join('');
        },

        renderTimeline(payload) {
            this.$timelineList.innerHTML = payload
                .map((item) => `
                    <li class="inv-timeline__item">
                        <div class="inv-timeline__time">${item.timeLabel}</div>
                        <div class="inv-timeline__body">
                            <div class="inv-timeline__title">${item.title}</div>
                            <div class="inv-timeline__subtitle">${item.subtitle || ''}</div>
                            ${item.link ? `<a class="inv-timeline__link" href="${item.link}">Detayı aç</a>` : ''}
                        </div>
                    </li>
                `)
                .join('');
        },

        renderLowStock(payload) {
            this.$lowstockList.innerHTML = payload
                .map((item) => `
                    <article class="inv-card inv-card--lowstock ${item.isCritical ? 'inv-card--lowstock--critical' : ''}"
                             data-action="inventory-lowstock"
                             data-product-id="${item.productId || ''}"
                             data-warehouse-id="${item.warehouseId || ''}"
                             data-recommendation="${item.recommendation}">
                        <header class="inv-card__header">
                            <span class="inv-card__title">${item.name}</span>
                            <span class="inv-card__subtitle">${item.warehouse}</span>
                        </header>
                        <dl class="inv-card__stats">
                            <div class="inv-card__stat"><dt>SKU</dt><dd>${item.sku}</dd></div>
                            <div class="inv-card__stat"><dt>Stok</dt><dd>${item.available}</dd></div>
                            <div class="inv-card__stat"><dt>Hedef</dt><dd>${item.threshold}</dd></div>
                            <div class="inv-card__stat"><dt>Öneri</dt><dd>${item.recommendation}</dd></div>
                        </dl>
                        <footer class="inv-card__footer">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-action="inventory-lowstock-transfer">Transfer öner</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-action="inventory-lowstock-procure">Tedarik planla</button>
                        </footer>
                    </article>
                `)
                .join('');
        },

        handleQuickAction(mode, href) {
            if (mode === 'create-product' && href) {
                window.location.href = href;
                return;
            }

            const targetUrl = this.$host.dataset.consoleUrl || href;
            if (!targetUrl) {
                return;
            }

            const url = new URL(targetUrl, window.location.origin);
            url.searchParams.set('mode', mode);
            window.location.href = url.toString();
        },

        openLowStockSheet(card) {
            if (!this.$sheet) {
                return;
            }

            const recommendation = Number(card.dataset.recommendation || 0);
            const qtyInput = this.$sheet.querySelector('#lowstock-qty');
            const targetSelect = this.$sheet.querySelector('#lowstock-target');

            if (qtyInput) {
                qtyInput.value = recommendation.toFixed(2);
            }

            if (targetSelect && card.dataset.warehouseId) {
                targetSelect.value = card.dataset.warehouseId;
            }

            this.$sheet.dataset.productId = card.dataset.productId || '';
            this.$sheet.setAttribute('aria-hidden', 'false');
            this.$sheet.classList.add('is-open');
        },

        closeLowStockSheet() {
            if (!this.$sheet) {
                return;
            }

            this.$sheet.classList.remove('is-open');
            this.$sheet.setAttribute('aria-hidden', 'true');
        },
    };

    window.Inventory.Home = Home;

    document.addEventListener('DOMContentLoaded', () => Home.init());
})();
