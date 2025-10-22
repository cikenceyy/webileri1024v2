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
            summaryRegion: '[data-summary-region]',
            summaryLines: '[data-summary-lines]',
            summaryQty: '[data-summary-qty]',
            summaryValue: '[data-summary-value]',
            form: '[data-console-form]',
            feedback: '[data-console-feedback]',
        },
        state: DEFAULT_STATE(),
        lookupTimer: null,

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
            this.$summaryRegion = this.$host.querySelector(this.selectors.summaryRegion);
            this.$summaryLines = this.$host.querySelector(this.selectors.summaryLines);
            this.$summaryQty = this.$host.querySelector(this.selectors.summaryQty);
            this.$summaryValue = this.$host.querySelector(this.selectors.summaryValue);
            this.$form = this.$host.querySelector(this.selectors.form);
            this.$feedback = this.$host.querySelector(this.selectors.feedback);
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

                    if (cartItem.dataset.item) {
                        try {
                            const payload = JSON.parse(cartItem.dataset.item);
                            this.addOrIncrement(payload);
                        } catch (error) {
                            // noop
                        }
                    } else {
                        this.setActiveItem(cartItem.dataset.itemId);
                    }

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
                    return;
                }

                const submit = event.target.closest('[data-action="console-submit"]');
                if (submit) {
                    event.preventDefault();
                    this.submit();
                    return;
                }

                const reset = event.target.closest('[data-action="console-reset"]');
                if (reset) {
                    event.preventDefault();
                    this.reset();
                    return;
                }

                const print = event.target.closest('[data-action="console-print"]');
                if (print) {
                    event.preventDefault();
                    window.print();
                    return;
                }

                const share = event.target.closest('[data-action="console-share"]');
                if (share) {
                    event.preventDefault();
                    this.share();
                }
            });

            this.$host.addEventListener('submit', (event) => {
                if (event.target.matches('[data-console-form]')) {
                    event.preventDefault();
                }
            });

            this.$host.addEventListener('input', (event) => {
                const input = event.target.closest('[data-cart-qty]');
                if (!input) {
                    return;
                }

                const itemId = input.dataset.cartQty;
                const value = Number(input.value);
                this.updateQuantity(itemId, Number.isFinite(value) ? value : 0);
            });

            if (this.$search) {
                this.$search.addEventListener('input', (event) => {
                    const query = event.target.value.trim();
                    window.clearTimeout(this.lookupTimer);
                    this.lookupTimer = window.setTimeout(() => this.lookupProduct(query), 250);
                });

                this.$search.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        const query = event.target.value.trim();
                        window.clearTimeout(this.lookupTimer);
                        this.applyScan(query);
                    }
                });
            }
        },

        collectFormData() {
            if (!this.$form) {
                return {
                    sourceWarehouseId: null,
                    targetWarehouseId: null,
                    reference: '',
                    movedAt: null,
                };
            }

            const formData = new FormData(this.$form);

            const normalizeId = (value) => {
                if (value === null || value === undefined || value === '') {
                    return null;
                }

                const parsed = Number(value);
                return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
            };

            return {
                sourceWarehouseId: normalizeId(formData.get('source_warehouse_id')),
                targetWarehouseId: normalizeId(formData.get('target_warehouse_id')),
                reference: (formData.get('reference') || '').toString().trim(),
                movedAt: formData.get('moved_at') || null,
            };
        },

        showFeedback(message = '', variant = 'danger') {
            if (!this.$feedback) {
                return;
            }

            this.$feedback.classList.remove('alert-danger', 'alert-success', 'alert-warning', 'alert-info');

            if (!message) {
                this.$feedback.classList.add('d-none');
                this.$feedback.textContent = '';
                return;
            }

            this.$feedback.classList.remove('d-none');
            this.$feedback.classList.add(`alert-${variant}`);
            this.$feedback.textContent = message;
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
            this.showFeedback('');
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
                if (this.state.mode === 'adjust') {
                    this.state.activeItemId = existing.id;
                    this.render();
                    return;
                }

                existing.qty += 1;
                existing.inputBuffer = String(existing.qty);
                this.state.activeItemId = existing.id;
            } else {
                const initialQty = this.state.mode === 'adjust' ? 0 : 1;
                this.state.cart.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    image: product.image,
                    price: Number(product.price || 0),
                    unit: product.unit || '',
                    qty: initialQty,
                    onHand: Number(product.onHand || 0),
                    inputBuffer: String(initialQty),
                });
                this.state.activeItemId = product.id;
            }

            const item = this.state.cart.find((entry) => entry.id === product.id);
            if (item) {
                this.validateItem(item);
            }

            this.render();
        },

        setActiveItem(id) {
            this.state.activeItemId = id;
            const active = this.state.cart.find((entry) => entry.id === id);
            if (active) {
                active.inputBuffer = String(active.qty);
            }
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

            const allowNegative = this.state.mode === 'adjust';
            const current = Number.isFinite(item.qty) ? item.qty : 0;
            const next = allowNegative ? current + delta : Math.max(0, current + delta);

            item.qty = next;
            item.inputBuffer = String(item.qty);

            if (!allowNegative && item.qty === 0) {
                this.removeItem(id);
            } else {
                this.validateItem(item);
                this.render();
            }
        },

        updateQuantity(id, qty) {
            const item = this.state.cart.find((entry) => entry.id === id);
            if (!item) {
                return;
            }

            const allowNegative = this.state.mode === 'adjust';
            const value = Number.isFinite(qty) ? qty : 0;
            item.qty = allowNegative ? value : Math.max(0, value);
            item.inputBuffer = String(item.qty);

            if (!allowNegative && item.qty === 0) {
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
                active.inputBuffer = '';
            } else if (key === 'plus') {
                active.qty = (Number.isFinite(active.qty) ? active.qty : 0) + 1;
                active.inputBuffer = String(active.qty);
            } else if (key === 'minus') {
                const allowNegative = this.state.mode === 'adjust';
                const current = Number.isFinite(active.qty) ? active.qty : 0;
                active.qty = allowNegative ? current - 1 : Math.max(0, current - 1);
                active.inputBuffer = String(active.qty);
            } else {
                let buffer = active.inputBuffer;
                if (typeof buffer !== 'string' || buffer === '') {
                    buffer = active.qty > 0 ? String(active.qty) : '';
                }

                if (key === '.' && buffer.includes('.')) {
                    return;
                }

                if (key === '.' && buffer === '') {
                    buffer = '0';
                }

                buffer = `${buffer}${key}`;
                active.inputBuffer = buffer;
                active.qty = Number(buffer);
            }

            if (this.state.mode !== 'adjust' && active.qty <= 0) {
                this.removeItem(active.id);
            } else {
                this.validateItem(active);
                this.render();
            }
        },

        validateItem(item) {
            const allowNegativeStock = this.$host.dataset.allowNegative === 'true';
            const quantity = Number.isFinite(item.qty) ? item.qty : 0;
            const onHand = Number.isFinite(item.onHand) ? item.onHand : 0;
            const isRemoval = this.state.mode === 'out'
                || this.state.mode === 'transfer'
                || (this.state.mode === 'adjust' && quantity < 0);
            const requested = isRemoval ? Math.abs(quantity) : quantity;
            const disallow = !allowNegativeStock && isRemoval && requested > onHand;

            item.qty = quantity;
            item.onHand = onHand;
            item.hasError = disallow;
            item.errorMessage = disallow
                ? (this.state.mode === 'transfer'
                    ? 'Kaynak stok yetersiz. Miktarı azaltın veya farklı depo seçin.'
                    : 'Stok yetersiz. Miktarı azaltın veya farklı depo seçin.')
                : '';
        },

        submit() {
            const endpoint = this.$host.dataset.endpoint;
            if (!endpoint) {
                return;
            }

            const items = this.state.cart
                .filter((item) => Number.isFinite(item.qty) && item.qty !== 0)
                .map(({ id, qty }) => ({ id, qty }));

            if (!items.length) {
                this.showFeedback('Sepete ürün ekleyin.', 'warning');
                return;
            }

            const formData = this.collectFormData();

            const payload = {
                mode: this.state.mode,
                items,
                source_warehouse_id: formData.sourceWarehouseId,
                target_warehouse_id: formData.targetWarehouseId,
                reference: formData.reference,
                moved_at: formData.movedAt,
            };

            this.showFeedback('');
            this.$host.classList.remove('is-error', 'is-success');
            this.$host.classList.add('is-loading');

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            })
                .then(async (response) => {
                    const data = await response.json().catch(() => null);

                    if (!response.ok) {
                        const message = data?.message || 'Stok hareketi kaydedilemedi.';
                        const details = data?.errors ? Object.values(data.errors).flat().join(' ') : '';
                        this.showFeedback([message, details].filter(Boolean).join(' '), 'danger');
                        this.$host.classList.add('is-error');
                        window.setTimeout(() => this.$host.classList.remove('is-error'), 2_000);
                        throw new Error('inventory:console:failed');
                    }

                    return data;
                })
                .then((response) => {
                    const lines = response?.totals?.lines;
                    const successMessage = lines ? `${lines} kalem başarıyla işlendi.` : 'Stok hareketi kaydedildi.';
                    this.showFeedback(successMessage, 'success');
                    this.$host.classList.add('is-success');
                    window.setTimeout(() => this.$host.classList.remove('is-success'), 2_000);
                    this.reset({ keepFeedback: true });
                })
                .catch((error) => {
                    if (error.message !== 'inventory:console:failed') {
                        this.showFeedback('Beklenmeyen bir hata oluştu.', 'danger');
                        this.$host.classList.add('is-error');
                        window.setTimeout(() => this.$host.classList.remove('is-error'), 2_000);
                    }
                })
                .finally(() => {
                    this.$host.classList.remove('is-loading');
                });
        },

        reset(options = {}) {
            const keepFeedback = options.keepFeedback === true;
            const preserveForm = options.preserveForm !== false;

            this.state = DEFAULT_STATE();
            this.state.mode = this.$host.dataset.mode || 'in';
            if (this.$search) {
                this.$search.value = '';
                this.$search.focus();
            }

            if (!preserveForm && this.$form) {
                this.$form.reset();
            }

            if (!keepFeedback) {
                this.showFeedback('');
            }

            window.clearTimeout(this.lookupTimer);
            this.lookupTimer = null;
            this.render();
        },

        share() {
            if (navigator.share && this.state.cart.length) {
                navigator.share({
                    title: 'Stok hareketi',
                    text: `${this.state.cart.length} kalem hazırlandı.`,
                }).catch(() => {});
            }
        },

        render() {
            if (!this.$cartRegion) {
                return;
            }

            const emptyMessages = {
                in: 'Sepete ürün eklemek için arayın veya barkodu okutun.',
                out: 'Çıkış işlemi için ürün ekleyin ve miktarı belirleyin.',
                transfer: 'Transfer edilecek ürünleri seçin ve miktarı girin.',
                adjust: 'Stok düzeltmesi için ürün ekleyin, +/- ile miktarı artırın veya azaltın.',
            };

            if (!this.state.cart.length) {
                const hint = emptyMessages[this.state.mode] || emptyMessages.in;
                this.$cartRegion.innerHTML = `<p class="text-muted">${hint}</p>`;
            } else {
                const allowAdjustNegative = this.state.mode === 'adjust';
                this.$cartRegion.innerHTML = this.state.cart
                    .map((item) => `
                        <article class="inv-card inv-card--product ${item.hasError ? 'inv-card--product--error' : ''} ${this.state.activeItemId === item.id ? 'is-active' : ''}" data-cart-item data-item-id="${item.id}">
                            <div class="inv-card__body" data-action="cart-select" data-item-id="${item.id}">
                                <div class="inv-card__header">
                                    <span class="inv-card__title">${item.name}</span>
                                    <span class="inv-card__subtitle">${item.sku}</span>
                                </div>
                                <div class="inv-card__meta small text-muted">Stok: ${item.onHand}${item.unit ? ' ' + item.unit : ''}</div>
                                ${item.hasError ? `<p class="text-danger small mt-2">${item.errorMessage}</p>` : ''}
                            </div>
                            <div class="inv-card__footer d-flex align-items-center gap-2">
                                <button class="btn btn-outline-secondary btn-sm" data-action="qty-adjust" data-item-id="${item.id}" data-delta="-1" type="button">-</button>
                                <input type="number" class="form-control form-control-sm text-end" value="${item.qty}" step="0.01" ${allowAdjustNegative ? '' : 'min="0"'} data-cart-qty="${item.id}" aria-label="Miktar" />
                                <button class="btn btn-outline-secondary btn-sm" data-action="qty-adjust" data-item-id="${item.id}" data-delta="1" type="button">+</button>
                                <button class="btn btn-link text-danger small" data-action="cart-remove" data-item-id="${item.id}" type="button">Sil</button>
                            </div>
                        </article>
                    `)
                    .join('');
            }

            const totals = this.state.cart.reduce((acc, item) => {
                acc.lines += 1;
                acc.qty += item.qty;
                acc.value += item.qty * (item.price || 0);
                return acc;
            }, { lines: 0, qty: 0, value: 0 });

            if (this.$summaryLines) {
                this.$summaryLines.textContent = totals.lines.toFixed(0);
            }
            if (this.$summaryQty) {
                this.$summaryQty.textContent = totals.qty.toFixed(2);
            }
            if (this.$summaryValue) {
                this.$summaryValue.textContent = totals.value.toFixed(2);
            }
        },
    };

    window.Inventory.StockConsole = StockConsole;

    document.addEventListener('DOMContentLoaded', () => StockConsole.init());
})();
