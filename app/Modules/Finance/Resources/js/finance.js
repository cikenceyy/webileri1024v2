import Toast from '../../../../../resources/js/lib/toast';

const Notifier = {
    success(message) {
        if (Toast && typeof Notifier.success === 'function') {
            Notifier.success(message);
        } else {
            console.log(message);
        }
    },
    error(message) {
        if (Toast && typeof Notifier.error === 'function') {
            Notifier.error(message);
        } else {
            console.error(message);
        }
    },
};

const FinanceApp = (() => {
    const state = {
        slideover: null,
        slideoverContent: null,
        activeScreen: null,
        summaryEndpointTemplate: null,
        laneEndpointTemplate: null,
        csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
    };

    function init() {
        state.slideover = document.querySelector('[data-finance-slideover]');
        state.slideoverContent = state.slideover?.querySelector('[data-slideover-content]') ?? null;
        state.activeScreen = document.querySelector('[data-finance-screen]')?.getAttribute('data-finance-screen') ?? null;
        const root = document.querySelector('[data-finance-screen]');
        state.summaryEndpointTemplate = root?.getAttribute('data-summary-endpoint') ?? null;
        state.laneEndpointTemplate = root?.getAttribute('data-lane-endpoint') ?? null;

        bindGlobalShortcuts();
        bindSlideover();
        if (state.activeScreen === 'collections') {
            setupCollections();
        }
        if (state.activeScreen === 'invoices') {
            setupInvoiceStudio();
        }
        if (state.activeScreen === 'cash-panel') {
            setupCashPanel();
        }
        if (state.activeScreen === 'home') {
            setupHomeShortcuts();
        }
        setupFilterPresets();
    }

    function bindGlobalShortcuts() {
        document.addEventListener('keydown', (event) => {
            if (event.altKey || event.metaKey || event.ctrlKey) {
                return;
            }

            const key = event.key.toLowerCase();

            if (key === '/' && !isTyping(event)) {
                const target = document.querySelector('[data-shortcut="/"]');
                if (target) {
                    event.preventDefault();
                    target.focus();
                }
            }

            if (isTyping(event)) {
                return;
            }

            const button = document.querySelector(`[data-shortcut="${key.toUpperCase()}"]`);
            if (button) {
                event.preventDefault();
                if (button.tagName === 'A') {
                    window.location.href = button.getAttribute('href');
                } else {
                    button.click();
                }
            }
        });
    }

    function bindSlideover() {
        if (!state.slideover) {
            return;
        }

        state.slideover.addEventListener('click', (event) => {
            if (event.target === state.slideover) {
                closeSlideover();
            }
        });

        const closeButton = state.slideover.querySelector('[data-action="close"]');
        closeButton?.addEventListener('click', closeSlideover);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && state.slideover?.getAttribute('data-open') === 'true') {
                closeSlideover();
            }
        });
    }

    function openSlideover() {
        if (!state.slideover) {
            return;
        }

        state.slideover.setAttribute('data-open', 'true');
        state.slideover.setAttribute('aria-hidden', 'false');
    }

    function closeSlideover() {
        if (!state.slideover) {
            return;
        }

        state.slideover.setAttribute('data-open', 'false');
        state.slideover.setAttribute('aria-hidden', 'true');
    }

    function setupHomeShortcuts() {
        const quickButtons = document.querySelectorAll('[data-shortcut]');
        quickButtons.forEach((button) => {
            button.setAttribute('title', `${button.textContent?.trim() ?? ''}`);
        });
    }

    function setupCollections() {
        const cards = document.querySelectorAll('[data-invoice-id]');
        cards.forEach((card) => {
            card.addEventListener('dragstart', onDragStart);
            card.addEventListener('click', onCardClick);
        });

        document.querySelectorAll('[data-droppable]').forEach((column) => {
            column.addEventListener('dragover', (event) => {
                event.preventDefault();
            });
            column.addEventListener('drop', (event) => onDrop(event, column));
        });
    }

    function setupInvoiceStudio() {
        document.querySelectorAll('[data-action="quick-pay"]').forEach((button) => {
            button.addEventListener('click', (event) => {
                const invoiceId = button.getAttribute('data-invoice-id');
                if (!invoiceId) {
                    return;
                }
                openSlideover();
                loadSummary(invoiceId);
            });
        });

        document.querySelectorAll('[data-invoice-row]').forEach((row) => {
            row.addEventListener('dblclick', () => {
                const invoiceId = row.getAttribute('data-invoice-id');
                if (!invoiceId) {
                    return;
                }
                openSlideover();
                loadSummary(invoiceId);
            });
        });
    }

    function setupCashPanel() {
        document.querySelectorAll('[data-currency-filter]').forEach((button) => {
            button.addEventListener('click', () => {
                const currency = button.getAttribute('data-currency-filter') ?? '';
                const url = new URL(window.location.href);
                if (currency === '') {
                    url.searchParams.delete('currency');
                } else {
                    url.searchParams.set('currency', currency);
                }
                window.location.href = url.toString();
            });
        });
    }

    function setupFilterPresets() {
        document.querySelectorAll('[data-preset]').forEach((button) => {
            button.addEventListener('click', () => {
                const preset = button.getAttribute('data-preset');
                if (!preset) {
                    return;
                }
                const url = new URL(window.location.href);
                url.searchParams.set('preset', preset);
                window.location.href = url.toString();
            });
        });
    }

    function onDragStart(event) {
        const card = event.currentTarget;
        event.dataTransfer?.setData('text/plain', card.getAttribute('data-invoice-id'));
        event.dataTransfer?.setData('application/x-lane', card.getAttribute('data-current-lane'));
        event.dataTransfer?.effectAllowed = 'move';
    }

    function onDrop(event, column) {
        event.preventDefault();
        const invoiceId = event.dataTransfer?.getData('text/plain');
        const fromLane = event.dataTransfer?.getData('application/x-lane');
        if (!invoiceId) {
            return;
        }

        const card = document.querySelector(`[data-invoice-id="${invoiceId}"]`);
        if (!card || column.contains(card)) {
            return;
        }

        const targetLane = column.getAttribute('data-droppable');
        if (!targetLane || targetLane === fromLane) {
            column.appendChild(card);
            return;
        }

        column.appendChild(card);
        card.setAttribute('data-current-lane', targetLane);
        optimisticUpdate(column.closest('.finance-kanban'));
        persistLaneChange(invoiceId, targetLane, () => {
            Notifier.success(window.__ ? window.__('Tahsilat sırası güncellendi.') : 'Tahsilat sırası güncellendi.');
        });
    }

    function optimisticUpdate(container) {
        if (!container) {
            return;
        }

        container.querySelectorAll('[data-droppable]').forEach((column) => {
            const items = Array.from(column.querySelectorAll('[data-invoice-id]'));
            const count = items.length;
            const total = items.reduce((carry, card) => carry + parseFloat(card.getAttribute('data-amount') ?? '0'), 0);
            const headerMeta = column.closest('.finance-kanban__column')?.querySelector('.finance-kanban__meta');
            if (headerMeta) {
                const currency = headerMeta.getAttribute('data-lane-currency') ?? '';
                const formattedTotal = total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const display = currency ? `${count} · ${formattedTotal} ${currency}` : `${count} · ${formattedTotal}`;
                headerMeta.textContent = display;
            }

            const emptyMessage = column.getAttribute('data-empty-message');
            const existingPlaceholder = column.querySelector('.finance-card--empty');
            if (count === 0 && emptyMessage && !existingPlaceholder) {
                const placeholder = document.createElement('div');
                placeholder.className = 'finance-card finance-card--empty';
                placeholder.textContent = emptyMessage;
                column.appendChild(placeholder);
            } else if (count > 0 && existingPlaceholder) {
                existingPlaceholder.remove();
            }
        });
    }

    function onCardClick(event) {
        const action = event.target.closest('[data-action]');
        if (!action) {
            return;
        }

        const card = event.currentTarget;
        const invoiceId = card.getAttribute('data-invoice-id');
        if (!invoiceId) {
            return;
        }

        openSlideover();
        loadSummary(invoiceId);
    }

    async function loadSummary(invoiceId) {
        if (!state.summaryEndpointTemplate || !state.slideoverContent) {
            return;
        }

        const endpoint = state.summaryEndpointTemplate.replace('__INVOICE__', invoiceId);
        try {
            state.slideoverContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border" role="status"></div></div>';
            const response = await fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                throw new Error('Failed to load summary');
            }
            const html = await response.text();
            state.slideoverContent.innerHTML = html;
        } catch (error) {
            state.slideoverContent.innerHTML = `<p class="text-danger">${window.__ ? window.__('Özet yüklenemedi.') : 'Özet yüklenemedi.'}</p>`;
        }
    }

    async function persistLaneChange(invoiceId, lane, onSuccess) {
        try {
            const laneEndpoint = (state.laneEndpointTemplate ?? '').replace('__INVOICE__', invoiceId) || `/admin/finance/collections/invoices/${invoiceId}/lane`;
            const response = await fetch(laneEndpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': state.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ lane }),
            });

            if (!response.ok) {
                throw new Error('Request failed');
            }

            onSuccess?.();
        } catch (error) {
            Notifier.error(window.__ ? window.__('Sürükle bırak işlemi kaydedilemedi.') : 'Sürükle bırak işlemi kaydedilemedi.');
        }
    }

    function isTyping(event) {
        const target = event.target;
        return target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement;
    }

    return { init };
})();

document.addEventListener('DOMContentLoaded', FinanceApp.init);
