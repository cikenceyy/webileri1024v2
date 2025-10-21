const formatCurrency = new Intl.NumberFormat('tr-TR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const parseNumber = (value) => {
    const number = Number.parseFloat(value);
    return Number.isFinite(number) ? number : 0;
};

const initInvoiceEditor = (root) => {
    const body = root.querySelector('[data-invoice-body]');
    const template = root.querySelector('[data-invoice-template]');
    const addButton = root.querySelector('[data-invoice-add]');
    const defaultTax = parseNumber(root.dataset.defaultTax);
    let index = body?.querySelectorAll('[data-invoice-row]').length ?? 0;

    if (!body) return;

    const recalcRow = (row) => {
        const qty = parseNumber(row.querySelector('input[data-field="qty"]')?.value);
        const price = parseNumber(row.querySelector('input[data-field="unit_price"]')?.value);
        const discount = parseNumber(row.querySelector('input[data-field="discount_rate"]')?.value);
        const tax = parseNumber(row.querySelector('input[data-field="tax_rate"]')?.value || defaultTax);
        const base = qty * price;
        const net = base - base * (discount / 100);
        const gross = net + net * (tax / 100);
        const cell = row.querySelector('[data-field="line_total"]');
        if (cell) {
            cell.textContent = formatCurrency.format(gross);
        }
    };

    const recalcAll = () => {
        body.querySelectorAll('[data-invoice-row]').forEach(recalcRow);
    };

    const createRow = () => {
        let row = template?.content?.firstElementChild?.cloneNode(true);
        if (!row) {
            row = body.querySelector('[data-invoice-row]:last-child')?.cloneNode(true);
        }
        if (!row) return null;

        const currentIndex = index++;
        row.querySelectorAll('input[name]').forEach((input) => {
            const name = input.getAttribute('name');
            if (!name) return;
            if (name.includes('__INDEX__')) {
                input.setAttribute('name', name.replace(/__INDEX__/g, currentIndex));
            } else {
                input.setAttribute('name', name.replace(/lines\[(\d+)\]/, `lines[${currentIndex}]`));
            }
            const field = input.dataset.field;
            if (field === 'qty') input.value = '1';
            else if (field === 'unit_price') input.value = '0';
            else if (field === 'discount_rate') input.value = '0';
            else if (field === 'tax_rate') input.value = String(defaultTax);
            else input.value = '';
        });

        recalcRow(row);
        return row;
    };

    addButton?.addEventListener('click', (event) => {
        event.preventDefault();
        const row = createRow();
        if (!row) return;
        body.appendChild(row);
        recalcAll();
    });

    body.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-invoice-remove]');
        if (!trigger) return;
        event.preventDefault();
        const rows = body.querySelectorAll('[data-invoice-row]');
        if (rows.length <= 1) {
            rows[0].querySelectorAll('input[data-field]').forEach((input) => {
                const field = input.dataset.field;
                if (field === 'qty') input.value = '1';
                else if (field === 'unit_price') input.value = '0';
                else if (field === 'discount_rate') input.value = '0';
                else if (field === 'tax_rate') input.value = String(defaultTax);
                else input.value = '';
            });
            recalcAll();
            return;
        }
        trigger.closest('[data-invoice-row]')?.remove();
        recalcAll();
    });

    body.addEventListener('input', (event) => {
        if (!(event.target instanceof HTMLInputElement)) return;
        if (!event.target.dataset.field) return;
        recalcRow(event.target.closest('[data-invoice-row]'));
    });

    recalcAll();
};

export const initFinanceModule = () => {
    if (document.body.dataset.module !== 'finance') return;

    document.querySelectorAll('[data-finance-invoice]').forEach(initInvoiceEditor);
};
