import { initDateRangeControls } from './plugins/date-range';

const MODE_CLIENT = 'client';
const MODE_SERVER = 'server';

const DEFAULT_DEBOUNCE = 250;

function debounce(fn, delay = DEFAULT_DEBOUNCE) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

class TableKit {
    constructor(element) {
        this.element = element;
        this.body = element.querySelector('[data-tablekit-body]');
        this.status = element.querySelector('[data-tablekit-status]');
        this.pager = element.querySelector('[data-tablekit-pager]');
        this.form = element.querySelector('[data-tablekit-form]');
        this.searchInput = element.querySelector('[data-tablekit-search]');
        this.perPageInput = element.querySelector('[data-tablekit-per-page]');
        this.sortInput = element.querySelector('[data-tablekit-sort-input]');
        this.datasetElement = element.querySelector('[data-tablekit-dataset]');
        this.headers = Array.from(element.querySelectorAll('[data-tablekit-sortable]'));
        this.filters = Array.from(element.querySelectorAll('[data-tablekit-filter]'));

        this.mode = element.getAttribute('data-tablekit-mode') || MODE_SERVER;
        this.count = parseInt(element.getAttribute('data-tablekit-count') || '0', 10);
        this.clientThreshold = parseInt(element.getAttribute('data-tablekit-client-threshold') || '500', 10);

        this.rows = [];
        this.columns = [];
        this.filteredRows = [];
        this.currentPage = 1;
        this.perPage = this.perPageInput ? parseInt(this.perPageInput.value || '25', 10) : 25;
        this.sortState = this.parseSortString(this.sortInput?.value || element.getAttribute('data-tablekit-default-sort'));
        this.filterState = {};
        this.searchTerm = this.searchInput ? this.searchInput.value || '' : '';

        this.determineMode();
        this.bootstrapDataset();
        this.attachEvents();
        initDateRangeControls(this.form);

        if (this.mode === MODE_CLIENT) {
            this.render();
        }
    }

    determineMode() {
        if (this.mode !== MODE_CLIENT && this.mode !== MODE_SERVER) {
            this.mode = this.count <= this.clientThreshold ? MODE_CLIENT : MODE_SERVER;
            this.element.setAttribute('data-tablekit-mode', this.mode);
        }
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
        } catch (error) {
            console.warn('[TableKit] Failed to parse dataset', error);
            this.columns = [];
            this.rows = [];
            this.filteredRows = [];
        }
    }

    attachEvents() {
        if (this.searchInput) {
            const handler = debounce((event) => {
                this.searchTerm = event.target.value;
                if (this.mode === MODE_CLIENT) {
                    this.currentPage = 1;
                    this.render();
                }
            });
            this.searchInput.addEventListener('input', handler);
        }

        if (this.perPageInput) {
            this.perPageInput.addEventListener('change', () => {
                this.perPage = parseInt(this.perPageInput.value || '25', 10);
                if (this.mode === MODE_CLIENT) {
                    this.currentPage = 1;
                    this.render();
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
                    this.render();
                }
            });

            this.form.addEventListener('reset', (event) => {
                if (this.mode === MODE_CLIENT) {
                    event.preventDefault();
                    this.resetFilters();
                    this.render();
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
                this.render();
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
        }
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

            if (type === 'date') {
                const inputs = wrapper.querySelectorAll('input[type="date"]');
                const from = inputs[0]?.value;
                const to = inputs[1]?.value;
                if (from || to) {
                    this.filterState[key] = { from, to };
                }
                return;
            }

            if (type === 'badge' || type === 'enum') {
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
                if (input.type === 'date' || input.type === 'number' || input.type === 'search' || input.type === 'text') {
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
            this.sortInput.value = this.sortState.map((entry) => entry.direction === 'desc' ? `-${entry.key}` : entry.key).join(',');
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

        return sortString.split(',').map((segment) => segment.trim()).filter(Boolean).map((segment) => {
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

    applyFilters() {
        const term = this.searchTerm.trim().toLowerCase();
        const filters = this.filterState;

        const filtered = this.rows.filter((row) => {
            if (term) {
                const matchesSearch = this.columns.some((column) => {
                    const cell = row.cells[column.key];
                    if (!cell) {
                        return false;
                    }
                    return (cell.text || '').toLowerCase().includes(term);
                });

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

    paginate(rows) {
        if (this.mode !== MODE_CLIENT) {
            return rows;
        }

        const pageSize = Math.max(this.perPage, 1);
        const totalPages = Math.max(Math.ceil(rows.length / pageSize), 1);
        this.currentPage = Math.min(this.currentPage, totalPages);
        const start = (this.currentPage - 1) * pageSize;
        const end = start + pageSize;
        return rows.slice(start, end);
    }

    render() {
        if (this.mode !== MODE_CLIENT || !this.body) {
            return;
        }

        this.collectFilterState();
        this.applyFilters();
        const sorted = this.applySort(this.filteredRows);
        const paginated = this.paginate(sorted);

        const rowsHtml = paginated.map((row) => this.renderRow(row)).join('');
        this.body.innerHTML = rowsHtml || this.renderEmpty();
        this.updateStatus();
        this.updatePager();
        this.updateHeaderStates();
        this.syncHistory();
    }

    renderRow(row) {
        const cellsHtml = this.columns.map((column, index) => {
            const cell = row.cells[column.key] || { html: '', raw: '', text: '' };
            const classes = ['tablekit__cell'];
            if (column.hiddenXs) {
                classes.push('tablekit__cell--hidden-xs');
            }
            if (index === 0) {
                classes.push('tablekit__cell--sticky');
            }

            const rawValue = typeof cell.raw === 'number' || typeof cell.raw === 'string' ? String(cell.raw) : '';
            return `<td role="cell" class="${classes.join(' ')}" data-tablekit-col="${column.key}" data-tablekit-col-label="${column.label}" data-tablekit-raw="${this.escape(rawValue)}">${cell.html}</td>`;
        }).join('');

        return `<tr class="tablekit__row" role="row" tabindex="0">${cellsHtml}</tr>`;
    }

    renderEmpty() {
        const span = Math.max(this.columns.length, 1);
        return `<tr class="tablekit__empty"><td colspan="${span}">${this.escape('Kayıt bulunamadı.')}</td></tr>`;
    }

    updateStatus() {
        if (!this.status) {
            return;
        }

        const total = this.filteredRows.length;
        const start = this.mode === MODE_CLIENT ? ((this.currentPage - 1) * this.perPage) + 1 : 1;
        const end = this.mode === MODE_CLIENT ? Math.min(this.currentPage * this.perPage, total) : total;
        if (total === 0) {
            this.status.textContent = '0 kayıt listeleniyor';
        } else if (this.mode === MODE_CLIENT) {
            this.status.textContent = `${start} - ${end} / ${total}`;
        } else {
            this.status.textContent = `${total} kayıt listeleniyor`;
        }
    }

    updatePager() {
        if (!this.pager || this.mode !== MODE_CLIENT) {
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
            params.set('sort', this.sortState.map((entry) => entry.direction === 'desc' ? `-${entry.key}` : entry.key).join(','));
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

        if (this.currentPage > 1) {
            params.set('page', String(this.currentPage));
        } else {
            params.delete('page');
        }

        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.replaceState({}, '', newUrl);
    }

    escape(value) {
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
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
