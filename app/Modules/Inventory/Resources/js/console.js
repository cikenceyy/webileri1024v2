/**
 * ConsoleKit stok konsolu istemci davranışı.
 */
class InventoryConsole {
    constructor(root) {
        this.root = root;
        this.gridEndpoint = root.dataset.gridEndpoint;
        this.bulkEndpoint = root.dataset.bulkEndpoint;
        this.bulkJobsEndpoint = root.dataset.bulkJobs;
        this.pollInterval = Number.parseInt(root.dataset.pollInterval ?? '15', 10) * 1000;
        this.columns = JSON.parse(root.dataset.columns ?? '[]');
        this.quickFilters = JSON.parse(root.dataset.quickFilters ?? '[]');
        this.commands = JSON.parse(root.dataset.commands ?? '[]');

        this.state = {
            filterText: '',
            filters: {},
            cursorToken: null,
            cursor: { next: null, prev: null },
            rows: [],
            selection: new Set(),
            gridEtag: null,
            jobsEtag: null,
            isPolling: false,
        };

        this.elements = {
            search: root.querySelector('[data-console-search]'),
            warehouse: root.querySelector('[data-console-warehouse]'),
            quickButtons: Array.from(root.querySelectorAll('[data-filter-id]')),
            grid: root.querySelector('[data-console-grid] table'),
            gridHead: root.querySelector('[data-console-grid] thead tr'),
            gridBody: root.querySelector('[data-console-grid] tbody'),
            pager: root.querySelector('[data-console-pager]'),
            jobs: root.querySelector('[data-console-jobs]'),
            status: root.querySelector('[data-console-status]'),
            updatedAt: root.querySelector('[data-console-updated]'),
            selectedCount: root.querySelector('[data-console-selected]'),
            refresh: root.querySelector('[data-console-refresh]'),
            commandBtn: root.querySelector('[data-console-command]'),
        };

        this.attachEvents();
        this.loadGrid(true);
        this.loadJobs(true);
        this.startPolling();
    }

    attachEvents() {
        if (this.elements.search) {
            this.elements.search.addEventListener('input', this.debounce((event) => {
                this.state.filterText = event.target.value;
                this.state.cursorToken = null;
                this.state.selection.clear();
                this.loadGrid(true);
            }, 300));
        }

        if (this.elements.warehouse) {
            this.elements.warehouse.addEventListener('change', (event) => {
                const value = event.target.value;
                if (value) {
                    this.state.filters.warehouse_id = value;
                } else {
                    delete this.state.filters.warehouse_id;
                }
                this.state.cursorToken = null;
                this.state.selection.clear();
                this.loadGrid(true);
            });
        }

        this.elements.quickButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const id = button.dataset.filterId;
                const active = button.classList.contains('active');
                this.elements.quickButtons.forEach((b) => b.classList.remove('active'));

                if (!active) {
                    button.classList.add('active');
                    const def = this.quickFilters.find((item) => item.id === id);
                    this.state.filters.quick = def?.payload?.filters ?? {};
                } else {
                    delete this.state.filters.quick;
                }

                this.state.cursorToken = null;
                this.state.selection.clear();
                this.loadGrid(true);
            });
        });

        if (this.elements.refresh) {
            this.elements.refresh.addEventListener('click', () => this.loadGrid(true));
        }

        if (this.elements.commandBtn) {
            this.elements.commandBtn.addEventListener('click', () => this.openCommandPalette());
        }

        document.addEventListener('keydown', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            const activeTag = document.activeElement?.tagName ?? '';
            const isFormInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeTag);

            if (event.key === '/' && !isFormInput) {
                event.preventDefault();
                this.elements.search?.focus();
                return;
            }

            if (event.key === '.' && !isFormInput) {
                event.preventDefault();
                this.openCommandPalette();
                return;
            }

            if (event.key === 'a' && !event.metaKey && !isFormInput) {
                event.preventDefault();
                this.toggleSelectAll();
            }
        });
    }

    debounce(fn, delay) {
        let timer;
        return (...args) => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => fn.apply(this, args), delay);
        };
    }

    buildQuery() {
        const params = new URLSearchParams();
        if (this.state.filterText) {
            params.set('filter_text', this.state.filterText);
        }

        if (this.state.filters.warehouse_id) {
            params.set('filters[warehouse_id]', this.state.filters.warehouse_id);
        }

        if (this.state.filters.quick) {
            Object.entries(this.state.filters.quick).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    params.set(`filters[${key}]`, value);
                }
            });
        }

        if (this.state.cursorToken) {
            params.set('cursor', this.state.cursorToken);
        }

        return params;
    }

    async loadGrid(force = false) {
        if (!this.gridEndpoint) {
            return;
        }

        try {
            const params = this.buildQuery();
            const headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (this.state.gridEtag && !force) {
                headers['If-None-Match'] = this.state.gridEtag;
            }

            const response = await fetch(`${this.gridEndpoint}?${params.toString()}`, { headers });

            if (response.status === 304) {
                this.markUpdated();
                return;
            }

            if (!response.ok) {
                throw new Error('Grid verisi alınamadı');
            }

            this.state.gridEtag = response.headers.get('ETag');
            const payload = await response.json();
            this.state.rows = payload.rows ?? [];
            this.state.cursor = payload.cursor ?? { next: null, prev: null };
            this.renderGrid(payload.columns ?? this.columns, this.state.rows);
            this.renderPager();
            this.markUpdated();
        } catch (error) {
            console.error(error);
            this.notify('Grid verisi yüklenirken hata oluştu.', 'danger');
            this.setStatusBadge('danger');
        }
    }

    renderGrid(columns, rows) {
        this.renderHeader(columns);
        this.renderBody(rows);
        this.setStatusBadge('success');
    }

    renderHeader(columns) {
        const headRow = this.elements.gridHead;
        headRow.innerHTML = '';

        const selectTh = document.createElement('th');
        selectTh.className = 'text-center';
        const selectAll = document.createElement('input');
        selectAll.type = 'checkbox';
        selectAll.setAttribute('aria-label', 'Tümünü seç');
        selectAll.addEventListener('change', () => {
            this.toggleSelectAll(selectAll.checked);
        });
        selectTh.appendChild(selectAll);
        headRow.appendChild(selectTh);

        columns.forEach((column) => {
            const th = document.createElement('th');
            th.scope = 'col';
            th.textContent = column.label ?? column.key;
            if (column.width) {
                th.style.width = column.width;
            }
            headRow.appendChild(th);
        });
    }

    renderBody(rows) {
        const body = this.elements.gridBody;
        body.innerHTML = '';

        if (!rows.length) {
            const empty = document.createElement('tr');
            empty.innerHTML = '<td colspan="8" class="text-center text-muted py-4">Kayıt bulunmuyor.</td>';
            body.appendChild(empty);
            return;
        }

        rows.forEach((row) => {
            const tr = document.createElement('tr');
            tr.dataset.rowId = row.id;
            tr.tabIndex = 0;

            const selectTd = document.createElement('td');
            selectTd.className = 'text-center';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.dataset.rowSelect = row.id;
            checkbox.checked = this.state.selection.has(String(row.id));
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    this.state.selection.add(String(row.id));
                } else {
                    this.state.selection.delete(String(row.id));
                }
                this.updateSelectionCount();
            });
            selectTd.appendChild(checkbox);
            tr.appendChild(selectTd);

            ['product', 'sku', 'warehouse', 'qty', 'reserved_qty', 'available_qty', 'status', 'updated_at'].forEach((key) => {
                const td = document.createElement('td');
                if (['qty', 'reserved_qty', 'available_qty'].includes(key)) {
                    td.classList.add('text-end');
                }
                if (key === 'status') {
                    td.innerHTML = this.statusBadge(row[key]);
                } else {
                    td.textContent = row[key] ?? '—';
                }
                tr.appendChild(td);
            });

            tr.addEventListener('click', (event) => {
                if (event.target.tagName === 'INPUT') {
                    return;
                }
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
            });

            body.appendChild(tr);
        });

        this.updateSelectionCount();
    }

    statusBadge(status) {
        const map = {
            positive: 'bg-success',
            zero: 'bg-secondary',
            negative: 'bg-danger',
        };
        const labelMap = {
            positive: 'Pozitif',
            zero: 'Sıfır',
            negative: 'Negatif',
        };
        const klass = map[status] ?? 'bg-secondary';
        const label = labelMap[status] ?? status ?? '—';
        return `<span class="badge ${klass}">${label}</span>`;
    }

    renderPager() {
        if (!this.elements.pager) {
            return;
        }

        this.elements.pager.innerHTML = '';
        const prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'btn btn-outline-secondary btn-sm';
        prev.textContent = 'Önceki';
        prev.disabled = !this.state.cursor?.prev;
        prev.addEventListener('click', () => {
            if (!this.state.cursor?.prev) {
                return;
            }
            this.state.cursorToken = this.state.cursor.prev;
            this.loadGrid(true);
        });

        const next = document.createElement('button');
        next.type = 'button';
        next.className = 'btn btn-outline-secondary btn-sm';
        next.textContent = 'Sonraki';
        next.disabled = !this.state.cursor?.next;
        next.addEventListener('click', () => {
            if (!this.state.cursor?.next) {
                return;
            }
            this.state.cursorToken = this.state.cursor.next;
            this.loadGrid(true);
        });

        this.elements.pager.append(prev, next);
    }

    toggleSelectAll(force) {
        const rows = this.state.rows;
        const shouldSelect = typeof force === 'boolean' ? force : this.state.selection.size !== rows.length;
        this.state.selection = new Set();

        rows.forEach((row) => {
            if (shouldSelect) {
                this.state.selection.add(String(row.id));
            }
        });

        this.renderBody(rows);
    }

    updateSelectionCount() {
        if (this.elements.selectedCount) {
            this.elements.selectedCount.textContent = String(this.state.selection.size);
        }
    }

    startPolling() {
        if (this.state.isPolling || Number.isNaN(this.pollInterval) || this.pollInterval <= 0) {
            return;
        }
        this.state.isPolling = true;
        setInterval(() => {
            this.loadGrid();
            this.loadJobs();
        }, this.pollInterval);
    }

    async loadJobs(force = false) {
        if (!this.bulkJobsEndpoint) {
            return;
        }

        try {
            const headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (this.state.jobsEtag && !force) {
                headers['If-None-Match'] = this.state.jobsEtag;
            }

            const response = await fetch(this.bulkJobsEndpoint, { headers });
            if (response.status === 304) {
                return;
            }

            if (!response.ok) {
                throw new Error('Bulk işleri alınamadı');
            }

            this.state.jobsEtag = response.headers.get('ETag');
            const payload = await response.json();
            this.renderJobs(payload.jobs ?? []);
        } catch (error) {
            console.error(error);
        }
    }

    renderJobs(jobs) {
        const container = this.elements.jobs;
        container.innerHTML = '';

        if (!jobs.length) {
            container.innerHTML = '<p class="text-muted mb-0">Henüz kuyrukta iş yok.</p>';
            return;
        }

        jobs.forEach((job) => {
            const item = document.createElement('div');
            item.className = 'console-kit__jobs-item';
            const label = document.createElement('div');
            label.innerHTML = `<strong>${job.module}</strong><br><small>${job.action}</small>`;
            const progress = document.createElement('div');
            progress.innerHTML = `<span class="badge bg-light text-dark">${job.progress ?? 0}%</span>`;
            item.append(label, progress);
            container.appendChild(item);
        });
    }

    markUpdated() {
        if (this.elements.updatedAt) {
            this.elements.updatedAt.textContent = new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }

    setStatusBadge(status) {
        if (!this.elements.status) {
            return;
        }

        const badge = this.elements.status.querySelector('.badge');
        if (!badge) {
            return;
        }

        badge.className = `badge ${status === 'danger' ? 'bg-danger' : 'bg-success'}`;
    }

    notify(message, type = 'success') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.style.position = 'fixed';
        alert.style.top = '1.5rem';
        alert.style.right = '1.5rem';
        alert.style.zIndex = '1060';
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
    }

    openCommandPalette() {
        if (!this.commands.length) {
            this.notify('Uygulanabilir komut bulunamadı.', 'warning');
            return;
        }

        if (this.commandOverlay) {
            return;
        }

        const tpl = document.querySelector('#console-command-template');
        if (!tpl) {
            return;
        }

        this.commandOverlay = tpl.content.firstElementChild.cloneNode(true);
        const search = this.commandOverlay.querySelector('.console-command__search');
        const list = this.commandOverlay.querySelector('.console-command__list');
        const closeBtn = this.commandOverlay.querySelector('[data-close]');

        const commands = this.commands.map((command, index) => ({ ...command, index }));
        let activeIndex = 0;

        const render = () => {
            list.innerHTML = '';
            commands.forEach((command, idx) => {
                const item = document.createElement('div');
                item.className = 'console-command__item';
                item.setAttribute('role', 'option');
                item.dataset.index = String(idx);
                if (idx === activeIndex) {
                    item.setAttribute('aria-selected', 'true');
                }
                item.innerHTML = `<span>${command.label}</span><span class="text-muted">${command.shortcut ?? ''}</span>`;
                item.addEventListener('click', () => {
                    activeIndex = idx;
                    execute();
                });
                list.appendChild(item);
            });
        };

        const execute = () => {
            const command = commands[activeIndex];
            this.executeCommand(command);
            this.closeCommandPalette();
        };

        search.addEventListener('input', () => {
            const term = search.value.toLowerCase();
            const filtered = this.commands
                .filter((command) => command.label.toLowerCase().includes(term));
            commands.length = 0;
            filtered.forEach((item, idx) => commands.push({ ...item, index: idx }));
            activeIndex = 0;
            render();
        });

        this.commandOverlay.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.closeCommandPalette();
            }
            if (event.key === 'Enter') {
                event.preventDefault();
                execute();
            }
            if (event.key === 'ArrowDown' || event.key === 'j') {
                event.preventDefault();
                activeIndex = Math.min(commands.length - 1, activeIndex + 1);
                render();
            }
            if (event.key === 'ArrowUp' || event.key === 'k') {
                event.preventDefault();
                activeIndex = Math.max(0, activeIndex - 1);
                render();
            }
        });

        closeBtn?.addEventListener('click', () => this.closeCommandPalette());
        document.body.appendChild(this.commandOverlay);
        search.focus();
        render();
    }

    closeCommandPalette() {
        if (this.commandOverlay) {
            this.commandOverlay.remove();
            this.commandOverlay = null;
        }
    }

    async executeCommand(command) {
        if (!command) {
            return;
        }

        const ids = Array.from(this.state.selection);
        if (!ids.length) {
            this.notify('Komutu uygulamak için en az bir satır seçin.', 'warning');
            return;
        }

        let qty = null;
        if (['reserve', 'release', 'adjust'].includes(command.action)) {
            const input = window.prompt('İşlem miktarını girin');
            if (!input) {
                return;
            }
            qty = Number.parseFloat(input);
            if (Number.isNaN(qty)) {
                this.notify('Geçerli bir sayı girmelisiniz.', 'danger');
                return;
            }
        }

        const payload = {
            action: command.action,
            items: ids.map((id) => ({ id, qty })),
        };

        try {
            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const response = await fetch(this.bulkEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': tokenEl?.getAttribute('content') ?? '',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                const err = await response.json().catch(() => ({ message: 'İşlem başarısız.' }));
                throw new Error(err.message ?? 'İşlem başarısız.');
            }

            this.notify('Toplu işlem kuyruğa alındı. Hazır olduğunda bildirim alacaksınız.');
            this.loadJobs(true);
        } catch (error) {
            console.error(error);
            this.notify(error.message ?? 'Komut çalıştırılamadı.', 'danger');
        }
    }
}

function bootstrapConsole() {
    const root = document.querySelector('.console-kit');
    if (!root) {
        return;
    }

    new InventoryConsole(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapConsole);
} else {
    bootstrapConsole();
}
