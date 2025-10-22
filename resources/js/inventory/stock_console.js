(function () {
    window.Inventory = window.Inventory || {};

    const DEFAULT_STATE = () => ({
        mode: 'in',
        cart: [],
        activeItemId: null,
    });

    const StockConsole = {
        selectors: {
            host: '.inv-console',
            tab: '[data-console-tab]',
            productSearch: '[data-action="product-search"]',
            keypad: '.inv-console__keypad',
            keypadKey: '.inv-keypad__key',
            cartContainer: '[data-cart-region]',
            cartItem: '[data-cart-item]',
            submit: '[data-action="console-submit"]',
            reset: '[data-action="console-reset"]',
        },
        state: DEFAULT_STATE(),

        init() {
            this.cache();
            if (!this.$host) {
                return;
            }

            this.state.mode = this.$host.dataset.mode || 'in';
            this.bind();
            this.render();
            this.bindShortcuts();
        },

        cache() {
            this.$host = document.querySelector(this.selectors.host);
            if (!this.$host) {
                return;
            }

            this.$cartRegion = this.$host.querySelector(this.selectors.cartContainer);
            this.$keypad = this.$host.querySelector(this.selectors.keypad);
            this.$search = this.$host.querySelector(this.selectors.productSearch);
        },

        bind() {
            this.$host.addEventListener('click', (event) => {
                const tab = event.target.closest(this.selectors.tab);
                if (tab) {
                    event.preventDefault();
                    this.switchMode(tab.dataset.consoleTab);
                    return;
                }

                const key = event.target.closest(this.selectors.keypadKey);
                if (key) {
                    event.preventDefault();
                    this.applyKeypadInput(key.dataset.key);
                    return;
                }

                const cartItem = event.target.closest('[data-action="cart-select"]');
                if (cartItem) {
                    event.preventDefault();
                    this.setActiveItem(cartItem.dataset.itemId);
                    return;
                }

                const quantityAction = event.target.closest('[data-action="qty-adjust"]');
                if (quantityAction) {
                    event.preventDefault();
                    this.adjustQuantity(quantityAction.dataset.itemId, Number(quantityAction.dataset.delta));
                    return;
                }

                const remove = event.target.closest('[data-action="cart-remove"]');
                if (remove) {
                    event.preventDefault();
                    this.removeItem(remove.dataset.itemId);
                }
            });

            this.$host.addEventListener('submit', (event) => {
                if (event.target.matches('[data-console-form]')) {
                    event.preventDefault();
                    this.submit();
                }
            });

            if (this.$search) {
                this.$search.addEventListener('input', (event) => {
                    const query = event.target.value.trim();
                    this.lookupProduct(query);
                });

                this.$search.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        const query = event.target.value.trim();
                        this.applyScan(query);
                    }
                });
            }
        },

        bindShortcuts() {
            document.addEventListener('keydown', (event) => {
                if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
                    return;
                }

                const key = event.key.toLowerCase();
                if (event.ctrlKey) {
                    if (key === 'i') {
                        this.switchMode('in');
                    } else if (key === 'o') {
                        this.switchMode('out');
                    } else if (key === 't') {
                        this.switchMode('transfer');
                    } else if (key === 'd') {
                        this.switchMode('adjust');
                    }
                }

                if (event.key === 'Enter') {
                    this.confirmQuantity();
                } else if (event.key === 'Escape') {
                    this.clearActiveItem();
                }
            });
        },

        switchMode(mode) {
            if (!mode || this.state.mode === mode) {
                return;
            }

            this.state = DEFAULT_STATE();
            this.state.mode = mode;
            this.$host.dataset.mode = mode;
            this.render();
            this.$host.dispatchEvent(new CustomEvent('inventory:console:mode', { detail: { mode } }));
        },

        lookupProduct(query) {
            if (!query) {
                return;
            }

            const endpoint = this.$search?.dataset.endpoint;
            if (!endpoint) {
                return;
            }

            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('q', query);

            fetch(url)
                .then((response) => response.ok ? response.json() : null)
                .then((payload) => {
                    if (!payload?.id) {
                        return;
                    }

                    this.addOrIncrement(payload);
                })
                .catch(() => {});
        },

        applyScan(code) {
            if (!code) {
                return;
            }

            this.$host.dispatchEvent(new CustomEvent('inventory:console:scan', { detail: { code } }));
            this.lookupProduct(code);
        },

        addOrIncrement(product) {
            const existing = this.state.cart.find((item) => item.id === product.id);
            if (existing) {
                existing.qty += 1;
                this.state.activeItemId = existing.id;
            } else {
                this.state.cart.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    image: product.image,
                    qty: 1,
                    onHand: product.onHand,
                });
                this.state.activeItemId = product.id;
            }

            this.render();
        },

        setActiveItem(id) {
            this.state.activeItemId = id;
            this.render();
        },

        clearActiveItem() {
            this.state.activeItemId = null;
            this.render();
        },

        adjustQuantity(id, delta) {
            const item = this.state.cart.find((entry) => entry.id === id);
            if (!item) {
                return;
            }

            item.qty = Math.max(0, item.qty + delta);
            if (item.qty === 0) {
                this.removeItem(id);
            } else {
                this.validateItem(item);
                this.render();
            }
        },

        confirmQuantity() {
            const active = this.state.cart.find((entry) => entry.id === this.state.activeItemId);
            if (!active) {
                return;
            }

            active.confirmed = true;
            this.render();
        },

        removeItem(id) {
            this.state.cart = this.state.cart.filter((entry) => entry.id !== id);
            if (this.state.activeItemId === id) {
                this.state.activeItemId = null;
            }
            this.render();
        },

        applyKeypadInput(key) {
            const active = this.state.cart.find((entry) => entry.id === this.state.activeItemId);
            if (!active) {
                return;
            }

            if (key === 'del') {
                active.qty = 0;
            } else if (key === 'plus') {
                active.qty += 1;
            } else if (key === 'minus') {
                active.qty = Math.max(0, active.qty - 1);
            } else {
                const next = `${active.qty || ''}${key}`;
                active.qty = Number(next);
            }

            if (active.qty <= 0) {
                this.removeItem(active.id);
            } else {
                this.validateItem(active);
                this.render();
            }
        },

        validateItem(item) {
            const allowNegative = this.$host.dataset.allowNegative === 'true';
            item.hasError = !allowNegative && this.state.mode !== 'in' && item.qty > item.onHand;
        },

        submit() {
            if (!this.state.cart.length) {
                return;
            }

            const endpoint = this.$host.dataset.endpoint;
            if (!endpoint) {
                return;
            }

            const payload = {
                mode: this.state.mode,
                items: this.state.cart.map(({ id, qty }) => ({ id, qty })),
            };

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(payload),
            })
                .then((response) => response.ok ? response.json() : Promise.reject())
                .then(() => {
                    this.$host.classList.add('is-success');
                    window.setTimeout(() => this.$host.classList.remove('is-success'), 2_000);
                    this.reset();
                })
                .catch(() => {
                    this.$host.classList.add('is-error');
                    window.setTimeout(() => this.$host.classList.remove('is-error'), 2_000);
                });
        },

        reset() {
            this.state = DEFAULT_STATE();
            this.state.mode = this.$host.dataset.mode || 'in';
            this.render();
        },

        render() {
            if (!this.$cartRegion) {
                return;
            }

            this.$cartRegion.innerHTML = this.state.cart
                .map((item) => `
                    <article class="inv-card--product ${item.hasError ? 'inv-card--product--error' : ''}" data-cart-item data-item-id="${item.id}">
                        <div class="ratio ratio-1x1 bg-light rounded"></div>
                        <div class="inv-card--product__meta">
                            <span class="fw-medium">${item.name}</span>
                            <span class="text-muted small">${item.sku}</span>
                            <div class="small text-muted">Stok: ${item.onHand}</div>
                        </div>
                        <div class="inv-card--product__qty" data-action="cart-select" data-item-id="${item.id}">
                            <button class="btn btn-outline-secondary btn-sm" data-action="qty-adjust" data-item-id="${item.id}" data-delta="-1" type="button">-</button>
                            <input type="number" class="form-control form-control-sm text-end" value="${item.qty}" aria-label="Quantity" />
                            <button class="btn btn-outline-secondary btn-sm" data-action="qty-adjust" data-item-id="${item.id}" data-delta="1" type="button">+</button>
                            <button class="btn btn-link text-danger small" data-action="cart-remove" data-item-id="${item.id}" type="button">Sil</button>
                        </div>
                    </article>
                `)
                .join('');

            const totals = this.state.cart.reduce((acc, item) => {
                acc.lines += 1;
                acc.qty += item.qty;
                return acc;
            }, { lines: 0, qty: 0 });

            const summary = this.$host.querySelector('[data-summary-region]');
            if (summary) {
                summary.innerHTML = `
                    <div class="inv-console__totals">
                        <span>Kalem: <strong>${totals.lines}</strong></span>
                        <span>Miktar: <strong>${totals.qty}</strong></span>
                    </div>
                `;
            }
        },
    };

    window.Inventory.StockConsole = StockConsole;

    document.addEventListener('DOMContentLoaded', () => StockConsole.init());
})();
