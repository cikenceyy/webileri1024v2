import '../scss/marketing.scss';

const clampPercent = (value) => {
    const number = Number.parseFloat(value);
    if (!Number.isFinite(number)) return 0;
    return Math.min(100, Math.max(0, number));
};

const parseNumber = (value) => {
    const number = Number.parseFloat(value);
    return Number.isFinite(number) ? number : 0;
};

const formatCurrency = new Intl.NumberFormat('tr-TR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const select = (root, selector) => root.querySelector(selector);

const selectAll = (root, selector) => Array.from(root.querySelectorAll(selector));

const lineSelector = (suffix) => `[data-marketing-line-${suffix}], [data-crm-line-${suffix}]`;

const initLineEditor = (editor) => {
    const body = select(editor, lineSelector('body'));
    const template = select(editor, lineSelector('template'));
    const totals = select(editor, lineSelector('totals'));
    const addButton = select(editor, lineSelector('add'));
    const defaultTax = clampPercent(editor.dataset.defaultTax ?? editor.dataset.marketingDefaultTax ?? 0);
    let index = selectAll(body ?? editor, lineSelector('row')).length;

    if (!body) return;

    const recalc = () => {
        let subtotal = 0;
        let netTotal = 0;
        let taxTotal = 0;

        selectAll(body, lineSelector('row')).forEach((row) => {
            const qty = parseNumber(select(row, 'input[data-field="qty"]')?.value);
            const unitPrice = parseNumber(select(row, 'input[data-field="unit_price"]')?.value);
            const discountRate = clampPercent(select(row, 'input[data-field="discount_rate"]')?.value);
            const taxRate = clampPercent(select(row, 'input[data-field="tax_rate"]')?.value ?? defaultTax);

            const lineSubtotal = qty * unitPrice;
            const lineNet = lineSubtotal * (1 - discountRate / 100);
            const lineTax = lineNet * (taxRate / 100);

            subtotal += lineSubtotal;
            netTotal += lineNet;
            taxTotal += lineTax;

            const totalCell = select(row, '[data-field="line_total"]');
            if (totalCell) {
                totalCell.textContent = formatCurrency.format(lineNet);
            }
        });

        if (!totals) return;

        const update = (key, value) => {
            const target = select(totals, `[data-total="${key}"]`);
            if (target) {
                target.textContent = formatCurrency.format(value);
            }
        };

        update('subtotal', subtotal);
        update('discount', subtotal - netTotal);
        update('tax', taxTotal);
        update('grand', netTotal + taxTotal);
    };

    const createRow = () => {
        let row = template?.content?.firstElementChild?.cloneNode(true);
        if (!row) {
            row = select(body, `${lineSelector('row')}:last-child`)?.cloneNode(true);
        }
        if (!row) return null;

        const currentIndex = index++;
        selectAll(row, 'input[name]').forEach((input) => {
            const name = input.getAttribute('name');
            if (!name) return;
            if (name.includes('__INDEX__')) {
                input.setAttribute('name', name.replace(/__INDEX__/g, currentIndex));
            } else {
                input.setAttribute('name', name.replace(/lines\[(\d+)\]/, `lines[${currentIndex}]`));
            }

            const field = input.dataset.field;
            if (field === 'qty') {
                input.value = '1';
            } else if (field === 'unit_price') {
                input.value = '0';
            } else if (field === 'discount_rate') {
                input.value = '0';
            } else if (field === 'tax_rate') {
                input.value = String(defaultTax);
            } else if (field === 'description') {
                input.value = '';
            }
        });

        const totalCell = select(row, '[data-field="line_total"]');
        if (totalCell) {
            totalCell.textContent = formatCurrency.format(0);
        }

        return row;
    };

    addButton?.addEventListener('click', (event) => {
        event.preventDefault();
        const row = createRow();
        if (!row) return;
        body.appendChild(row);
        recalc();
    });

    body.addEventListener('click', (event) => {
        const trigger = event.target.closest(lineSelector('remove'));
        if (!trigger) return;
        event.preventDefault();
        const rows = selectAll(body, lineSelector('row'));
        if (rows.length <= 1) {
            rows[0].querySelectorAll('input[data-field]').forEach((input) => {
                const field = input.dataset.field;
                if (field === 'qty') input.value = '1';
                else if (field === 'unit_price') input.value = '0';
                else if (field === 'discount_rate') input.value = '0';
                else if (field === 'tax_rate') input.value = String(defaultTax);
                else input.value = '';
            });
            recalc();
            return;
        }
        trigger.closest(lineSelector('row'))?.remove();
        recalc();
    });

    body.addEventListener('input', (event) => {
        if (!(event.target instanceof HTMLInputElement)) return;
        if (!event.target.dataset.field) return;
        recalc();
    });

    recalc();
};

const initDeleteGuards = () => {
    document.querySelectorAll('[data-marketing-delete-form], [data-crm-delete-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmMessage || 'Bu kaydı silmek istediğinize emin misiniz?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
};

const markFilters = () => {
    document.querySelectorAll('[data-marketing-filters], [data-crm-filters]').forEach((card) => {
        const form = card.matches('form') ? card : card.querySelector('form');
        if (!form) return;

        const syncState = () => {
            const hasValue = Array.from(form.elements).some((element) => {
                if (!(element instanceof HTMLInputElement || element instanceof HTMLSelectElement)) {
                    return false;
                }
                if (element.type === 'checkbox' || element.type === 'radio') {
                    return element.checked;
                }
                return element.value !== '';
            });

            card.dataset.state = hasValue ? 'filtered' : '';
        };

        form.addEventListener('input', syncState);
        form.addEventListener('change', syncState);
        syncState();
    });
};

const initOrderHover = () => {
    document.querySelectorAll('[data-marketing-order-row], [data-crm-order-row]').forEach((row) => {
        row.addEventListener('mouseenter', () => {
            row.dataset.hover = 'true';
        });
        row.addEventListener('mouseleave', () => {
            row.dataset.hover = 'false';
        });
    });
};

export default function bootMarketing(context = {}) {
    const host = document.querySelector('[data-module-slug="marketing"], [data-module="Marketing"], [data-module="marketing"]');
    if (!host) {
        return;
    }

    const pageName = (context.page || host.dataset.page || '').toLowerCase();

    if (pageName === 'demo') {
        const hero = host.querySelector('[data-marketing-hero]');
        if (hero) {
            hero.classList.add('marketing-hero--active');
        }
    }

    document.querySelectorAll('[data-marketing-line-editor], [data-crm-line-editor]').forEach((editor) => {
        initLineEditor(editor);
    });

    initDeleteGuards();
    markFilters();
    initOrderHover();
}
