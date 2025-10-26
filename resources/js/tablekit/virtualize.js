const DEFAULT_OVERSCAN = 6;
const DEFAULT_ROW_HEIGHT = 48;

export class TableVirtualizer {
    constructor(tableKit, options = {}) {
        this.tableKit = tableKit;
        this.wrapper = tableKit.wrapper;
        this.body = tableKit.body;
        this.rows = [];
        this.rowHeight = Number(options.rowHeight || DEFAULT_ROW_HEIGHT);
        this.overscan = Number.isFinite(options.overscan) ? Number(options.overscan) : DEFAULT_OVERSCAN;
        this.range = { start: 0, end: 0 };

        this.handleScroll = this.handleScroll.bind(this);
        this.handleResize = this.handleResize.bind(this);

        if (this.wrapper) {
            this.wrapper.addEventListener('scroll', this.handleScroll, { passive: true });
        }

        if (typeof window !== 'undefined') {
            window.addEventListener('resize', this.handleResize);
        }
    }

    destroy() {
        if (this.wrapper) {
            this.wrapper.removeEventListener('scroll', this.handleScroll);
        }

        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', this.handleResize);
        }
    }

    setRows(rows, reset = false) {
        this.rows = Array.isArray(rows) ? rows : [];
        if (reset && this.wrapper) {
            this.wrapper.scrollTop = 0;
        }
        this.update();
    }

    handleScroll() {
        this.update();
    }

    handleResize() {
        this.update();
    }

    update() {
        if (!this.body) {
            return;
        }

        if (!Array.isArray(this.rows) || this.rows.length === 0) {
            this.range = { start: 0, end: 0 };
            this.body.innerHTML = this.tableKit.renderEmpty();
            this.tableKit.onVirtualRender(this.range, []);
            return;
        }

        const wrapperHeight = this.wrapper ? this.wrapper.clientHeight : 0;
        const scrollTop = this.wrapper ? this.wrapper.scrollTop : 0;
        const rowHeight = Math.max(this.rowHeight, 24);
        const total = this.rows.length;

        const visibleCount = wrapperHeight > 0 ? Math.ceil(wrapperHeight / rowHeight) + this.overscan : total;
        const start = Math.max(Math.floor(scrollTop / rowHeight) - this.overscan, 0);
        const end = Math.min(start + visibleCount, total);

        this.range = { start, end };

        const offsetTop = start * rowHeight;
        const offsetBottom = Math.max((total * rowHeight) - (end * rowHeight), 0);

        const slice = this.rows.slice(start, end);
        const rowsHtml = slice.map((row) => this.tableKit.renderRow(row)).join('');

        const before = `<tr class="tablekit__virtual-spacer" aria-hidden="true" style="height:${offsetTop}px"></tr>`;
        const after = `<tr class="tablekit__virtual-spacer" aria-hidden="true" style="height:${offsetBottom}px"></tr>`;

        this.body.innerHTML = `${before}${rowsHtml}${after}`;
        this.tableKit.onVirtualRender(this.range, slice);
    }

    getVisibleRange() {
        return this.range;
    }
}

export default TableVirtualizer;
