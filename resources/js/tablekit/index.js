import { initDateRangeControls } from './plugins/date-range';
import { TableVirtualizer } from './virtualize';

const MODE_CLIENT = 'client';
const MODE_SERVER = 'server';

const DEFAULT_DEBOUNCE = 250;
const DEFAULT_PAGE_SIZE = 25;
const DEFAULT_EMPTY_TEXT = 'Kayıt bulunamadı.';

function debounce(fn, delay = DEFAULT_DEBOUNCE) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function safeString(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value);
}

function isModifierActive(event) {
    return event.altKey || event.ctrlKey || event.metaKey;
}

class TableKit {
    constructor(element) {
        this.element = element;
        this.wrapper = element.querySelector('.tablekit__table-wrapper');
        this.body = element.querySelector('[data-tablekit-body]');
        this.status = element.querySelector('[data-tablekit-status]');
        this.pager = element.querySelector('[data-tablekit-pager]');
        this.bulkElement = element.querySelector('[data-tablekit-bulk]');
        this.bulkCountElement = element.querySelector('[data-tablekit-bulk-count]');
        this.stepperSummary = element.querySelector('[data-tablekit-stepper-summary]');
        this.toolbar = element.querySelector('[data-tablekit-toolbar]');
        this.form = element.querySelector('[data-tablekit-form]');
        this.searchInput = element.querySelector('[data-tablekit-search]');
        this.perPageInput = element.querySelector('[data-tablekit-per-page]');
        this.sortInput = element.querySelector('[data-tablekit-sort-input]');
        this.datasetElement = element.querySelector('[data-tablekit-dataset]');
        this.headers = Array.from(element.querySelectorAll('[data-tablekit-sortable]'));
        this.filters = Array.from(element.querySelectorAll('[data-tablekit-filter]'));
        this.selectAllInputs = Array.from(element.querySelectorAll('[data-tablekit-select-all]'));
        this.rowMetaTemplate = element.querySelector('[data-tablekit-row-meta-template]');

        this.mode = element.getAttribute('data-tablekit-mode') || MODE_SERVER;
        this.count = parseInt(element.getAttribute('data-tablekit-count') || '0', 10);
        this.clientThreshold = parseInt(element.getAttribute('data-tablekit-client-threshold') || '500', 10);
        this.defaultSort = element.getAttribute('data-tablekit-default-sort') || '';
        this.virtual = element.getAttribute('data-tablekit-virtual') === 'true';
        this.virtualRowHeight = parseInt(element.getAttribute('data-tablekit-row-height') || '0', 10) || 48;
        this.selectable = element.getAttribute('data-tablekit-selectable') === 'true';
        this.dense = element.getAttribute('data-tablekit-dense') === 'true';
        this.filterKeys = (element.getAttribute('data-tablekit-filters') || '')
            .split(',')
            .map((key) => key.trim())
            .filter((key) => key.length > 0);

        this.rows = [];
        this.columns = [];
        this.filteredRows = [];
        this.visibleRows = [];
        this.visibleRange = { start: 0, end: 0 };
        this.currentPage = 1;
        this.perPage = this.perPageInput ? parseInt(this.perPageInput.value || DEFAULT_PAGE_SIZE, 10) : DEFAULT_PAGE_SIZE;
        this.sortState = this.parseSortString(this.sortInput?.value || this.defaultSort);
        this.filterState = {};
        this.searchTerm = this.searchInput ? this.searchInput.value || '' : '';
        this.selected = new Set();
        this.virtualizer = null;
        this.emptyPlaceholder = this.body ? (this.body.querySelector('.tablekit__empty')?.outerHTML || '') : '';

        if (this.dense) {
            this.element.classList.add('tablekit--dense');
        }

        this.bootstrapDataset();
        this.determineMode();
        this.setupVirtualizer();
        this.attachEvents();
        initDateRangeControls(this.form);

        if (this.mode === MODE_CLIENT) {
            this.render(true);
        } else {
            this.updateHeaderStates();
            this.updateStatus();
            this.updateBulk();
        }

        this.emitSelection();
        this.element.dispatchEvent(new CustomEvent('tablekit:ready', {
            bubbles: true,
            detail: {
                mode: this.mode,
                virtual: Boolean(this.virtualizer),
            },
        }));
    }

    determineMode() {
        let thresholdOverride = Number.NaN;
        if (typeof window !== 'undefined' && Object.prototype.hasOwnProperty.call(window, 'TABLEKIT_THRESHOLD')) {
            thresholdOverride = Number(window.TABLEKIT_THRESHOLD);
        }

        if (!Number.isNaN(thresholdOverride) && thresholdOverride > 0) {
            this.clientThreshold = thresholdOverride;
        }

        const attrMode = this.element.getAttribute('data-tablekit-mode');
        if (attrMode === MODE_SERVER && this.mode === MODE_SERVER) {
            this.element.setAttribute('data-tablekit-mode', MODE_SERVER);
            return;
        }

        if (attrMode === MODE_CLIENT && this.mode === MODE_CLIENT) {
            this.element.setAttribute('data-tablekit-mode', MODE_CLIENT);
            return;
        }

        this.mode = this.count <= this.clientThreshold ? MODE_CLIENT : MODE_SERVER;
        this.element.setAttribute('data-tablekit-mode', this.mode);
    }

    bootstrapDataset() {
        if (!this.datasetElement) {
            return;
        }

        try {
            const payload = JSON.parse(this.datasetElement.textContent || '{}');
            this.columns = Array.isArray(payload.columns) ? payload.columns : [];
            this.rows = Array.isArray(payload.rows) ? payload.rows : [];
            this.filteredRows = [...this.rows];
            this.rows.forEach((row) => {
                const value = this.resolveRowSelectionValue(row);
                const selectionColumn = this.getSelectionColumn();
                if (!selectionColumn) {
                    return;
                }

                const cell = row.cells?.[selectionColumn.key];
                if (cell && cell.raw && cell.raw.checked && value !== null) {
                    this.selected.add(String(value));
                }
            });
        } catch (error) {
            console.warn('[TableKit] Failed to parse dataset', error);
            this.columns = [];
            this.rows = [];
            this.filteredRows = [];
        }
    }

    setupVirtualizer() {
        if (this.virtualizer) {
            this.virtualizer.destroy();
            this.virtualizer = null;
        }

        if (this.mode !== MODE_CLIENT || !this.virtual || !this.wrapper) {
            return;
        }

        this.virtualizer = new TableVirtualizer(this, {
            rowHeight: this.virtualRowHeight,
        });
    }

    attachEvents() {
        if (this.searchInput) {
            const handler = debounce((event) => {
                this.searchTerm = event.target.value;
                if (this.mode === MODE_CLIENT) {
                    this.currentPage = 1;
                    this.render(true);
                }
            });
            this.searchInput.addEventListener('input', handler);
        }

        if (this.perPageInput) {
            this.perPageInput.addEventListener('change', () => {
                this.perPage = parseInt(this.perPageInput.value || DEFAULT_PAGE_SIZE, 10);
                if (this.mode === MODE_CLIENT) {
                    this.currentPage = 1;
                    this.render(true);
                } else if (this.form) {
                    this.form.submit();
                }
            });
        }

        if (this.form) {
            this.form.addEventListener('submit', (event) => {
                if (this.mode === MODE_CLIENT) {
                    event.preventDefault();
                    this.collectFilterState();
                    this.currentPage = 1;
                    this.render(true);
                }
            });

            this.form.addEventListener('reset', (event) => {
                if (this.mode === MODE_CLIENT) {
                    event.preventDefault();
                    this.resetFilters();
                    this.render(true);
                }
            });
        }

        this.filters.forEach((input) => {
            if (this.mode !== MODE_CLIENT) {
                return;
            }

            input.addEventListener('change', () => {
                this.collectFilterState();
                this.currentPage = 1;
                this.render(true);
            });
        });

        this.headers.forEach((header) => {
            header.addEventListener('click', (event) => {
                event.preventDefault();
                this.toggleSort(header.dataset.tablekitCol);
            });

            header.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.toggleSort(header.dataset.tablekitCol);
                }
            });
        });

        if (this.body) {
            this.body.addEventListener('keydown', (event) => this.handleRowNavigation(event));
            this.body.addEventListener('change', (event) => this.handleCheckboxChange(event));
        }

        this.selectAllInputs.forEach((input) => {
            input.addEventListener('change', (event) => {
                const checked = event.target.checked;
                this.handleSelectAll(checked);
            });
        });

        this.shortcutHandler = (event) => this.handleShortcut(event);
        document.addEventListener('keydown', this.shortcutHandler);
    }

    getSelectionColumn() {
        return this.columns.find((column) => column.type === 'select');
    }

    resolveRowSelectionValue(row) {
        const selectionColumn = this.getSelectionColumn();
        if (!selectionColumn) {
            return null;
        }

        const cell = row.cells?.[selectionColumn.key];
        if (cell && cell.raw && Object.prototype.hasOwnProperty.call(cell.raw, 'value')) {
            return cell.raw.value !== null && cell.raw.value !== undefined ? cell.raw.value : null;
        }

        return row.id ?? null;
    }

    isRowSelectable(row) {
        const selectionColumn = this.getSelectionColumn();
        if (!selectionColumn) {
            return false;
        }

        const cell = row.cells?.[selectionColumn.key];
        return cell ? !cell.raw?.disabled : true;
    }

    collectFilterState() {
        const filterWrappers = this.form ? this.form.querySelectorAll('[data-tablekit-filter-wrapper]') : [];
        this.filterState = {};

        filterWrappers.forEach((wrapper) => {
            const key = wrapper.getAttribute('data-tablekit-filter-key');
            const type = wrapper.getAttribute('data-tablekit-filter-type');
            if (!key) {
                return;
            }

            if (this.filterKeys.length > 0 && !this.filterKeys.includes(key)) {
                return;
            }

            if (type === 'date') {
                const inputs = wrapper.querySelectorAll('input[type="date"]');
                const from = inputs[0]?.value;
                const to = inputs[1]?.value;
                if (from || to) {
                    this.filterState[key] = { from, to };
                }
                return;
            }

            if (type === 'badge' || type === 'enum' || type === 'chip') {
                const select = wrapper.querySelector('select');
                if (select) {
                    const selected = Array.from(select.selectedOptions).map((option) => option.value);
                    if (selected.length > 0) {
                        this.filterState[key] = selected;
                    }
                }
                return;
            }

            const input = wrapper.querySelector('input');
            if (input && input.value !== '') {
                this.filterState[key] = input.value;
            }
        });
    }

    resetFilters() {
        if (this.form) {
            this.form.querySelectorAll('input').forEach((input) => {
                if (input.type === 'hidden') {
                    return;
                }
                if (['date', 'number', 'search', 'text'].includes(input.type)) {
                    input.value = '';
                }
            });
            this.form.querySelectorAll('select').forEach((select) => {
                Array.from(select.options).forEach((option) => {
                    option.selected = false;
                });
                select.value = '';
            });
        }

        this.filterState = {};
        this.searchTerm = '';
        if (this.searchInput) {
            this.searchInput.value = '';
        }
        this.currentPage = 1;
    }

    toggleSort(columnKey) {
        if (!columnKey) {
            return;
        }

        const column = this.columns.find((col) => col.key === columnKey);
        if (!column || !column.sortable) {
            return;
        }

        const current = this.sortState.find((entry) => entry.key === columnKey);
        if (current) {
            if (current.direction === 'asc') {
                current.direction = 'desc';
            } else if (current.direction === 'desc') {
                this.sortState = this.sortState.filter((entry) => entry.key !== columnKey);
            }
        } else {
            this.sortState = [{ key: columnKey, direction: 'asc' }];
        }

        if (this.sortState.length === 0 && this.sortInput) {
            this.sortInput.value = '';
        } else if (this.sortInput) {
            this.sortInput.value = this.sortState.map((entry) => (entry.direction === 'desc' ? `-${entry.key}` : entry.key)).join(',');
        }

        if (this.mode === MODE_CLIENT) {
            this.render();
            this.syncHistory();
        } else if (this.form) {
            this.form.submit();
        }

        this.updateHeaderStates();
    }

    parseSortString(sortString) {
        if (!sortString) {
            return [];
        }

        return sortString
            .split(',')
            .map((segment) => segment.trim())
            .filter(Boolean)
            .map((segment) => {
                const direction = segment.startsWith('-') ? 'desc' : 'asc';
                const key = segment.replace(/^-/, '');
                return { key, direction };
            });
    }

    updateHeaderStates() {
        this.headers.forEach((header) => {
            const key = header.dataset.tablekitCol;
            const state = this.sortState.find((entry) => entry.key === key);
            if (!state) {
                header.setAttribute('aria-sort', 'none');
            } else {
                header.setAttribute('aria-sort', state.direction === 'desc' ? 'descending' : 'ascending');
            }
        });
    }

    handleRowNavigation(event) {
        const { key, target } = event;
        if (!['ArrowDown', 'ArrowUp', 'Enter', 'Escape'].includes(key)) {
            return;
        }

        const rows = Array.from(this.body.querySelectorAll('.tablekit__row'));
        const index = rows.indexOf(target.closest('.tablekit__row'));

        if (key === 'ArrowDown' && index < rows.length - 1) {
            event.preventDefault();
            rows[index + 1].focus();
        }

        if (key === 'ArrowUp' && index > 0) {
            event.preventDefault();
            rows[index - 1].focus();
        }

        if (key === 'Escape') {
            event.preventDefault();
            if (this.searchInput) {
                this.searchInput.focus();
            }
        }

        if (key === 'Enter') {
            const primaryAction = target.querySelector('.tablekit__action');
            if (primaryAction) {
                primaryAction.click();
            }
        }
    }

    handleCheckboxChange(event) {
        const checkbox = event.target;
        if (!checkbox.matches('[data-tablekit-select]')) {
            return;
        }

        const value = safeString(checkbox.value);
        if (!value) {
            return;
        }

        if (checkbox.checked) {
            this.selected.add(value);
        } else {
            this.selected.delete(value);
        }

        const row = checkbox.closest('[data-tablekit-row]');
        if (row) {
            if (checkbox.checked) {
                row.setAttribute('data-selected', 'true');
            } else {
                row.removeAttribute('data-selected');
            }
        }

        this.updateBulk();
        this.emitSelection();
    }

    handleSelectAll(checked) {
        const rows = this.visibleRows.filter((row) => this.isRowSelectable(row));
        rows.forEach((row) => {
            const value = this.resolveRowSelectionValue(row);
            if (value === null || value === undefined) {
                return;
            }
            const key = String(value);
            if (checked) {
                this.selected.add(key);
            } else {
                this.selected.delete(key);
            }
        });

        if (this.virtualizer) {
            this.virtualizer.update();
        } else {
            this.syncSelectionState();
        }

        this.updateBulk();
        this.emitSelection();
    }

    syncSelectionState() {
        const checkboxes = this.body ? this.body.querySelectorAll('[data-tablekit-select]') : [];
        checkboxes.forEach((checkbox) => {
            const value = safeString(checkbox.value);
            checkbox.checked = this.selected.has(value);
            const row = checkbox.closest('[data-tablekit-row]');
            if (row) {
                if (checkbox.checked) {
                    row.setAttribute('data-selected', 'true');
                } else {
                    row.removeAttribute('data-selected');
                }
            }
        });
    }

    handleShortcut(event) {
        if (isModifierActive(event)) {
            return;
        }

        if (!this.element.contains(document.activeElement) && ['/', 'a', 'A', 'p', 'P'].includes(event.key)) {
            // allow global shortcuts even if focus outside
        }

        switch (event.key) {
            case '/':
                if (this.searchInput) {
                    event.preventDefault();
                    this.searchInput.focus();
                }
                break;
            case 'a':
            case 'A':
                if (this.selectable && this.mode === MODE_CLIENT) {
                    event.preventDefault();
                    const shouldSelectAll = this.selectAllInputs.every((input) => !input.checked || input.indeterminate);
                    this.handleSelectAll(shouldSelectAll);
                    this.syncSelectionState();
                }
                break;
            case 'p':
            case 'P':
                if (this.selectable && this.selected.size > 0) {
                    const shortcutEvent = new CustomEvent('tablekit:shortcut', {
                        bubbles: true,
                        detail: {
                            key: 'print',
                            selected: Array.from(this.selected),
                        },
                    });
                    this.element.dispatchEvent(shortcutEvent);
                }
                break;
            default:
                break;
        }
    }

    applyFilters() {
        const term = this.searchTerm.trim().toLowerCase();
        const filters = this.filterState;

        const filtered = this.rows.filter((row) => {
            const metaText = row.meta?.text ? String(row.meta.text).toLowerCase() : '';

            if (term) {
                const matchesSearch = this.columns.some((column) => {
                    const cell = row.cells[column.key];
                    if (!cell) {
                        return false;
                    }
                    return (cell.text || '').toLowerCase().includes(term);
                }) || metaText.includes(term);

                if (!matchesSearch) {
                    return false;
                }
            }

            for (const [key, value] of Object.entries(filters)) {
                const column = this.columns.find((col) => col.key === key);
                const cell = row.cells[key];
                if (!column || !cell) {
                    continue;
                }

                if (Array.isArray(value)) {
                    if (!value.includes(String(cell.raw))) {
                        return false;
                    }
                    continue;
                }

                if (typeof value === 'object' && value !== null) {
                    const from = value.from ? new Date(value.from) : null;
                    const to = value.to ? new Date(value.to) : null;
                    const cellDate = cell.raw ? new Date(cell.raw) : null;
                    if ((from && (!cellDate || cellDate < from)) || (to && (!cellDate || cellDate > to))) {
                        return false;
                    }
                    continue;
                }

                const cellValue = (cell.text || '').toLowerCase();
                if (!cellValue.includes(String(value).toLowerCase())) {
                    return false;
                }
            }

            return true;
        });

        this.filteredRows = filtered;
    }

    comparableValue(cell, column) {
        if (!cell) {
            return '';
        }

        const raw = cell.raw;
        switch (column.type) {
            case 'number':
                return Number(raw) || 0;
            case 'money':
                if (typeof raw === 'number') {
                    return raw;
                }
                if (raw && typeof raw === 'object') {
                    return Number(raw.amount) || 0;
                }
                return 0;
            case 'date':
                return raw ? new Date(raw).getTime() || 0 : 0;
            default:
                return (cell.text || '').toLowerCase();
        }
    }

    applySort(rows) {
        if (this.sortState.length === 0) {
            return rows;
        }

        const [{ key, direction }] = this.sortState;
        const column = this.columns.find((col) => col.key === key);
        if (!column) {
            return rows;
        }

        const multiplier = direction === 'desc' ? -1 : 1;
        return [...rows].sort((a, b) => {
            const valueA = this.comparableValue(a.cells[key], column);
            const valueB = this.comparableValue(b.cells[key], column);

            if (valueA < valueB) {
                return -1 * multiplier;
            }
            if (valueA > valueB) {
                return 1 * multiplier;
            }
            return 0;
        });
    }

    paginate(rows) {
        if (this.mode !== MODE_CLIENT) {
            return rows;
        }

        const pageSize = Math.max(this.perPage, 1);
        const totalPages = Math.max(Math.ceil(rows.length / pageSize), 1);
        this.currentPage = Math.min(this.currentPage, totalPages);
        const start = (this.currentPage - 1) * pageSize;
        const end = start + pageSize;
        this.visibleRange = {
            start,
            end: Math.min(end, rows.length),
        };
        return rows.slice(start, end);
    }

    render(reset = false) {
        if (this.mode !== MODE_CLIENT || !this.body) {
            return;
        }

        this.collectFilterState();
        this.applyFilters();
        const sorted = this.applySort(this.filteredRows);

        if (this.virtualizer) {
            this.virtualizer.setRows(sorted, reset);
            this.visibleRows = sorted;
            return;
        }

        const paginated = this.paginate(sorted);
        this.visibleRows = paginated;

        const rowsHtml = paginated.map((row) => this.renderRow(row)).join('');
        this.body.innerHTML = rowsHtml || this.renderEmpty();
        this.syncSelectionState();
        this.updateStatus();
        this.updatePager();
        this.updateHeaderStates();
        this.updateBulk();
        this.syncHistory();
    }

    onVirtualRender(range, slice) {
        this.visibleRange = range;
        this.visibleRows = slice;
        this.syncSelectionState();
        this.updateStatus();
        this.updatePager();
        this.updateHeaderStates();
        this.updateBulk();
        this.syncHistory();
    }

    renderRow(row) {
        const rowId = safeString(row.id ?? '');
        const selectionValue = this.resolveRowSelectionValue(row);
        const isSelected = selectionValue !== null && this.selected.has(String(selectionValue));

        const cellsHtml = this.columns.map((column, index) => {
            const cell = row.cells[column.key] || { html: '', raw: '', text: '' };
            const classes = ['tablekit__cell'];
            if (column.hiddenXs) {
                classes.push('tablekit__cell--hidden-xs');
            }
            if (index === 0) {
                classes.push('tablekit__cell--sticky');
            }
            if (column.type === 'select') {
                classes.push('tablekit__cell--select');
            }

            const rawValue = this.resolveRawValue(cell.raw);
            let innerHtml;

            if (column.type === 'select') {
                const disabled = cell.raw?.disabled;
                const selected = !disabled && selectionValue !== null && this.selected.has(String(selectionValue));
                const value = selectionValue !== null ? safeString(selectionValue) : '';
                innerHtml = `
                    <div class="tablekit__cell-main">
                        <span class="tablekit__checkbox-wrapper">
                            <input type="checkbox" class="tablekit__checkbox" data-tablekit-select value="${this.escape(value)}" ${selected ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
                        </span>
                    </div>
                `;
            } else {
                let payload = typeof cell.preformatted === 'string' && cell.preformatted !== ''
                    ? this.escape(cell.preformatted)
                    : cell.html;

                if (typeof payload !== 'string' || payload.trim() === '') {
                    payload = cell.html;
                }

                innerHtml = `<div class="tablekit__cell-main">${payload}</div>`;
                if (index === 0 && row.meta && row.meta.html) {
                    innerHtml += `<div class="tablekit__row-meta" data-tablekit-row-meta>${row.meta.html}</div>`;
                }
            }

            innerHtml = innerHtml.trim();

            return `<td role="cell" class="${classes.join(' ')}" data-tablekit-col="${column.key}" data-tablekit-col-label="${this.escape(column.label)}" data-tablekit-raw="${this.escape(rawValue)}">${innerHtml}</td>`;
        }).join('');

        const attrs = [
            'class="tablekit__row"',
            'role="row"',
            'tabindex="0"',
            'data-tablekit-row',
        ];

        if (rowId) {
            attrs.push(`data-row-id="${this.escape(rowId)}"`);
        }

        if (isSelected) {
            attrs.push('data-selected="true"');
        }

        return `<tr ${attrs.join(' ')}>${cellsHtml}</tr>`;
    }

    renderEmpty() {
        if (this.emptyPlaceholder) {
            return this.emptyPlaceholder;
        }

        const span = Math.max(this.columns.length, 1);
        return `<tr class="tablekit__empty"><td colspan="${span}">${this.escape(DEFAULT_EMPTY_TEXT)}</td></tr>`;
    }

    resolveRawValue(raw) {
        if (raw === null || raw === undefined) {
            return '';
        }

        if (typeof raw === 'object') {
            return '';
        }

        return String(raw);
    }

    updateStatus() {
        if (!this.status) {
            return;
        }

        const total = this.filteredRows.length;
        if (total === 0) {
            this.status.textContent = '0 kayıt listeleniyor';
            return;
        }

        if (this.mode === MODE_CLIENT) {
            if (this.virtualizer) {
                const start = this.visibleRange.start;
                const end = Math.min(this.visibleRange.end, total);
                this.status.textContent = `${start + 1} - ${end} / ${total}`;
            } else {
                const start = ((this.currentPage - 1) * this.perPage) + 1;
                const end = Math.min(this.currentPage * this.perPage, total);
                this.status.textContent = `${start} - ${end} / ${total}`;
            }
        } else {
            this.status.textContent = `${total} kayıt listeleniyor`;
        }
    }

    updatePager() {
        if (!this.pager || this.mode !== MODE_CLIENT || this.virtualizer) {
            if (this.pager) {
                this.pager.innerHTML = '';
            }
            return;
        }

        const totalPages = Math.max(Math.ceil(this.filteredRows.length / Math.max(this.perPage, 1)), 1);

        if (totalPages <= 1) {
            this.pager.innerHTML = '';
            return;
        }

        const createButton = (label, page, disabled = false) => {
            const classes = ['tablekit__btn', 'tablekit__btn--ghost'];
            return `<button type="button" class="${classes.join(' ')}" data-page="${page}" ${disabled ? 'disabled' : ''}>${label}</button>`;
        };

        const buttons = [
            createButton('Önceki', String(this.currentPage - 1), this.currentPage === 1),
            `<span class="tablekit__status">${this.currentPage} / ${totalPages}</span>`,
            createButton('Sonraki', String(this.currentPage + 1), this.currentPage >= totalPages),
        ].join('');

        this.pager.innerHTML = `<div class="tablekit__pager-controls">${buttons}</div>`;

        this.pager.querySelectorAll('button[data-page]').forEach((button) => {
            button.addEventListener('click', () => {
                const page = Number(button.dataset.page);
                if (!Number.isNaN(page)) {
                    this.currentPage = Math.min(Math.max(page, 1), totalPages);
                    this.render();
                }
            });
        });
    }

    updateBulk() {
        if (!this.bulkElement) {
            return;
        }

        const totalSelected = this.selected.size;
        if (totalSelected === 0) {
            this.bulkElement.setAttribute('hidden', 'hidden');
        } else {
            this.bulkElement.removeAttribute('hidden');
        }

        if (this.bulkCountElement) {
            this.bulkCountElement.textContent = `${totalSelected} kayıt seçildi`;
        }

        const selectableRows = this.visibleRows.filter((row) => this.isRowSelectable(row));
        const selectedVisible = selectableRows.filter((row) => {
            const value = this.resolveRowSelectionValue(row);
            return value !== null && this.selected.has(String(value));
        }).length;

        this.selectAllInputs.forEach((input) => {
            if (selectableRows.length === 0) {
                input.checked = false;
                input.indeterminate = false;
                return;
            }

            input.checked = selectedVisible === selectableRows.length;
            input.indeterminate = selectedVisible > 0 && selectedVisible < selectableRows.length;
        });
    }

    syncHistory() {
        if (typeof window === 'undefined' || !window.history || this.mode !== MODE_CLIENT) {
            return;
        }

        const params = new URLSearchParams(window.location.search);
        if (this.searchTerm) {
            params.set('q', this.searchTerm);
        } else {
            params.delete('q');
        }

        if (this.perPageInput) {
            params.set('perPage', String(this.perPage));
        }

        if (this.sortState.length > 0) {
            params.set('sort', this.sortState.map((entry) => (entry.direction === 'desc' ? `-${entry.key}` : entry.key)).join(','));
        } else {
            params.delete('sort');
        }

        Array.from(params.keys()).forEach((key) => {
            if (key.startsWith('filters[')) {
                params.delete(key);
            }
        });

        Object.entries(this.filterState).forEach(([key, value]) => {
            const prefix = `filters[${key}]`;
            if (Array.isArray(value)) {
                value.forEach((item) => params.append(`${prefix}[]`, String(item)));
            } else if (value && typeof value === 'object') {
                if (value.from) {
                    params.set(`${prefix}[from]`, value.from);
                }
                if (value.to) {
                    params.set(`${prefix}[to]`, value.to);
                }
            } else if (value) {
                params.set(prefix, String(value));
            }
        });

        if (!this.virtualizer && this.currentPage > 1) {
            params.set('page', String(this.currentPage));
        } else {
            params.delete('page');
        }

        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState({}, '', newUrl);
    }

    escape(value) {
        return safeString(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    emitSelection() {
        const event = new CustomEvent('tablekit:selection', {
            bubbles: true,
            detail: {
                selected: Array.from(this.selected),
            },
        });
        this.element.dispatchEvent(event);
    }
}

export function bootTableKit(root = document) {
    const tables = root.querySelectorAll('[data-tablekit]');
    tables.forEach((table) => {
        if (!table.__tableKitInstance) {
            table.__tableKitInstance = new TableKit(table);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    bootTableKit();
});

export default TableKit;
