const debounce = (fn, delay = 250) => {
    let timer = null;
    return (...args) => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => fn(...args), delay);
    };
};

const parseJSON = (value, fallback) => {
    if (!value) return fallback;
    try {
        return JSON.parse(value);
    } catch (error) {
        return fallback;
    }
};

const normalize = (value) => (value ?? '').toString().toLowerCase();

const buildSearchIndex = (row) => {
    if (typeof row.search === 'string') {
        return row.search.toLowerCase();
    }

    const values = Object.values(row)
        .filter((item) => typeof item === 'string' || typeof item === 'number')
        .map((item) => item.toString().toLowerCase());

    return values.join(' ');
};

const getSortValue = (row, column) => {
    const { key } = column;
    const sortKey = column.sortKey || `${key}_raw`;
    if (Object.prototype.hasOwnProperty.call(row, sortKey)) {
        return row[sortKey];
    }
    return row[key];
};

const updateAriaSort = (table, sortKey, sortDir) => {
    table.querySelectorAll('[data-column-key]').forEach((header) => {
        const columnKey = header.dataset.columnKey;
        if (!columnKey) return;
        const ariaValue = columnKey === sortKey ? (sortDir === 'desc' ? 'descending' : 'ascending') : 'none';
        header.setAttribute('aria-sort', ariaValue);
    });
};

const renderActions = (actions = []) => {
    if (!Array.isArray(actions) || actions.length === 0) {
        return '<span class="text-muted">—</span>';
    }

    return actions
        .map((action) => {
            const label = action.label || 'Aç';
            const url = action.url || '#';
            const variant = action.variant || 'outline-primary';
            const size = action.size || 'sm';
            const icon = action.icon ? `<i class="${action.icon} me-1" aria-hidden="true"></i>` : '';

            return `<a href="${url}" class="btn btn-${variant} btn-${size} me-2">${icon}${label}</a>`;
        })
        .join('');
};

const renderCell = (row, column) => {
    const key = column.key;
    const alignClass = column.align ? `text-${column.align}` : '';
    const wrapClass = column.wrap === false ? 'text-nowrap' : '';
    const classes = ['py-3', alignClass, wrapClass].filter(Boolean).join(' ');
    const value = row[key] ?? '';
    let content = value;

    switch (column.type) {
        case 'link': {
            const urlKey = column.urlKey || `${key}_url`;
            const url = row[urlKey];
            if (url) {
                content = `<a href="${url}" class="fw-semibold">${value}</a>`;
            }
            break;
        }
        case 'badge': {
            const badgeKey = column.badgeKey || `${key}_badge`;
            const variant = row[badgeKey] || 'bg-secondary';
            content = `<span class="badge ${variant}">${value}</span>`;
            break;
        }
        case 'actions': {
            content = renderActions(row[key]);
            break;
        }
        default:
            content = value;
    }

    return `<td class="${classes}">${content ?? ''}</td>`;
};

const buildRowHtml = (row, columns, selectable) => {
    const cells = columns.map((column) => renderCell(row, column)).join('');
    const checkbox = selectable
        ? `<td><input type="checkbox" class="form-check-input" data-table-select-row value="${row.id}" aria-label="Satırı seç"></td>`
        : '';

    return `<tr data-row-id="${row.id}">${checkbox}${cells}</tr>`;
};

const createPageItem = (page, label, options = {}) => {
    const { active = false, disabled = false } = options;
    const classes = ['page-item'];
    if (active) classes.push('active');
    if (disabled) classes.push('disabled');

    return `<li class="${classes.join(' ')}"><a class="page-link" href="#" data-page="${page}">${label}</a></li>`;
};

const initClientTable = (table) => {
    const dataset = parseJSON(table.getAttribute('data-table-dataset'), []);
    const tableId = table.getAttribute('data-table-id');
    const configNode = document.querySelector(`[data-table-config="${tableId}"]`);
    const config = configNode ? parseJSON(configNode.textContent, {}) : {};
    if (configNode) {
        configNode.remove();
    }

    const columns = config.columns || [];
    const selectable = Boolean(config.selectable);

    const searchInput = table.querySelector('[data-table-search-input]');
    const filterElements = Array.from(table.querySelectorAll('[data-table-filter]'));
    const pageSizeSelect = table.querySelector('[data-table-page-size]');
    const pagination = table.querySelector('[data-table-pagination]');
    const summary = table.querySelector('[data-table-summary]');
    const tbody = table.querySelector('[data-table-body]');
    const selectAll = table.querySelector('[data-table-select-all]');
    const selectionBar = table.querySelector('[data-table-selection]');
    const selectionCount = table.querySelector('[data-table-selection-count]');
    const clearSelection = table.querySelector('[data-table-clear-selection]');

    const pageSizeOptions = parseJSON(table.getAttribute('data-page-size-options'), [25, 50, 100]);
    let pageSize = Number(table.getAttribute('data-default-page-size')) || pageSizeOptions[0] || 25;
    let page = 1;
    let sortKey = null;
    let sortDir = 'asc';
    let searchTerm = normalize(searchInput ? searchInput.value : '');
    const selection = new Set();
    let filteredRows = [...dataset];

    if (pageSizeSelect) {
        pageSizeSelect.value = String(pageSize);
    }

    const applySelectionState = () => {
        if (!selectionBar || !selectionCount) return;
        const count = selection.size;
        selectionCount.textContent = count.toString();
        selectionBar.hidden = count === 0;
    };

    const syncSelectAll = (pageRows) => {
        if (!selectAll) return;
        if (pageRows.length === 0) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
            return;
        }
        const selectedInPage = pageRows.filter((row) => selection.has(String(row.id))).length;
        selectAll.checked = selectedInPage === pageRows.length && selectedInPage > 0;
        selectAll.indeterminate = selectedInPage > 0 && selectedInPage < pageRows.length;
    };

    const renderTable = () => {
        const total = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(total / pageSize));
        if (page > totalPages) {
            page = totalPages;
        }
        const startIndex = (page - 1) * pageSize;
        const pageRows = filteredRows.slice(startIndex, startIndex + pageSize);

        const rowsHtml = pageRows
            .map((row) => buildRowHtml(row, columns, selectable))
            .join('');
        tbody.innerHTML = rowsHtml;

        if (selectable) {
            tbody.querySelectorAll('[data-table-select-row]').forEach((checkbox) => {
                const id = checkbox.value;
                checkbox.checked = selection.has(id);
                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        selection.add(id);
                    } else {
                        selection.delete(id);
                    }
                    applySelectionState();
                    syncSelectAll(pageRows);
                });
            });
        }

        const start = total === 0 ? 0 : startIndex + 1;
        const end = Math.min(startIndex + pageSize, total);
        if (summary) {
            summary.textContent = total === 0
                ? 'Kayıt bulunamadı.'
                : `${start}-${end} arası (${total} kayıt)`;
        }

        if (pagination) {
            const items = [];
            items.push(createPageItem(Math.max(1, page - 1), 'Önceki', { disabled: page === 1 }));
            for (let index = 1; index <= totalPages; index += 1) {
                items.push(createPageItem(index, index, { active: index === page }));
            }
            items.push(createPageItem(Math.min(totalPages, page + 1), 'Sonraki', { disabled: page === totalPages }));
            pagination.innerHTML = items.join('');
            pagination.querySelectorAll('a[data-page]').forEach((link) => {
                link.addEventListener('click', (event) => {
                    event.preventDefault();
                    const targetPage = Number(link.dataset.page);
                    if (!Number.isNaN(targetPage)) {
                        page = Math.min(Math.max(targetPage, 1), totalPages);
                        renderTable();
                    }
                });
            });
        }

        syncSelectAll(pageRows);
        applySelectionState();
    };

    const sortRows = () => {
        if (!sortKey) return;
        const column = columns.find((item) => item.key === sortKey);
        if (!column) return;

        const direction = sortDir === 'desc' ? -1 : 1;
        filteredRows.sort((a, b) => {
            const valueA = getSortValue(a, column);
            const valueB = getSortValue(b, column);

            if (valueA === valueB) return 0;

            if (valueA === undefined || valueA === null) return -1 * direction;
            if (valueB === undefined || valueB === null) return direction;

            if (typeof valueA === 'number' && typeof valueB === 'number') {
                return valueA > valueB ? direction : -direction;
            }

            return valueA.toString().localeCompare(valueB.toString(), undefined, { numeric: true }) * direction;
        });

        updateAriaSort(table, sortKey, sortDir);
    };

    const applyFilters = () => {
        filteredRows = dataset.filter((row) => {
            if (searchTerm) {
                const haystack = buildSearchIndex(row);
                if (!haystack.includes(searchTerm)) {
                    return false;
                }
            }

            return filterElements.every((element) => {
                const field = element.dataset.filterField || element.dataset.filterKey;
                const value = element.value;
                if (value === '' || value === null) {
                    return true;
                }
                const type = element.dataset.filterType || 'string';
                const rowValue = row[field];
                if (type === 'numeric') {
                    return Number(rowValue) === Number(value);
                }

                return normalize(rowValue) === normalize(value);
            });
        });

        sortRows();
        page = 1;
        renderTable();
    };

    if (searchInput) {
        const onSearch = debounce(() => {
            searchTerm = normalize(searchInput.value);
            applyFilters();
        }, Number(searchInput.dataset.debounce) || 250);

        searchInput.addEventListener('input', onSearch);

        if (table.querySelector('[data-table-behavior="client"]')) {
            const form = searchInput.closest('form');
            form?.addEventListener('submit', (event) => {
                event.preventDefault();
                searchTerm = normalize(searchInput.value);
                applyFilters();
            });
        }
    }

    filterElements.forEach((element) => {
        element.addEventListener('change', () => {
            applyFilters();
        });
    });

    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', () => {
            const value = Number(pageSizeSelect.value);
            if (!Number.isNaN(value)) {
                pageSize = value;
                page = 1;
                renderTable();
            }
        });
    }

    table.querySelectorAll('[data-table-sort]').forEach((button) => {
        button.addEventListener('click', () => {
            const key = button.dataset.sortKey;
            if (!key) return;
            if (sortKey === key) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortKey = key;
                sortDir = 'asc';
            }
            sortRows();
            renderTable();
        });
    });

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            const startIndex = (page - 1) * pageSize;
            const pageRows = filteredRows.slice(startIndex, startIndex + pageSize);
            if (selectAll.checked) {
                pageRows.forEach((row) => selection.add(String(row.id)));
            } else {
                pageRows.forEach((row) => selection.delete(String(row.id)));
            }
            renderTable();
        });
    }

    if (clearSelection) {
        clearSelection.addEventListener('click', () => {
            selection.clear();
            renderTable();
        });
    }

    filteredRows = [...dataset];
    sortRows();
    renderTable();
};

export const initClientTables = () => {
    document.querySelectorAll("[data-ui='table'][data-table-mode='client']").forEach((table) => {
        initClientTable(table);
    });
};
