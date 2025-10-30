/**
 * Drive ekleme modalını yöneten küçük controller.
 * Not: AJAX uçları modüller tarafından sağlanmalıdır; bu dosya sadece
 * debounced arama ve seçim mantığını sunar.
 */
const debounce = (fn, wait = 0) => {
    let timeoutId;

    return function debounced(...args) {
        const context = this;
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => {
            fn.apply(context, args);
        }, wait);
    };
};

const translate = (key) => (typeof window !== 'undefined' && typeof window.__ === 'function' ? window.__(key) : key);

export default class DriveAttachController {
    constructor(element) {
        this.element = element;
        this.selected = new Set();
        this.debouncedSearch = debounce(this.search.bind(this), 350);
    }

    connect() {
        this.refresh();
        if (this.confirmTarget) {
            this.confirmTarget.disabled = true;
        }
    }

    search() {
        if (!this.searchTarget) {
            this.refresh();
            return;
        }

        this.refresh(this.searchTarget.value);
    }

    refresh(query = '') {
        const context = this.element.dataset.context || 'default';
        const url = new URL(this.element.dataset.endpoint, window.location.origin);
        if (query) {
            url.searchParams.set('search', query);
        }
        url.searchParams.set('context', context);

        this.selected.clear();
        if (this.confirmTarget) {
            this.confirmTarget.disabled = true;
        }

        if (!this.listTarget) {
            return;
        }

        this.listTarget.setAttribute('aria-busy', 'true');

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        })
            .then((response) => response.text())
            .then((html) => {
                this.listTarget.innerHTML = html;
                this.listTarget.removeAttribute('aria-busy');
            })
            .catch(() => {
                this.listTarget.innerHTML = `<p class="text-danger">${translate('Drive listesi alınamadı.')}</p>`;
                this.listTarget.removeAttribute('aria-busy');
            });
    }

    toggleSelection(event) {
        const checkbox = event.target.closest('input[type="checkbox"]');
        if (!checkbox) {
            return;
        }

        const key = checkbox.value;
        if (checkbox.checked) {
            this.selected.add(key);
        } else {
            this.selected.delete(key);
        }

        this.confirmTarget.disabled = this.selected.size === 0;
    }

    confirm() {
        this.element.dispatchEvent(
            new CustomEvent('drive:attach', {
                detail: {
                    media: Array.from(this.selected),
                    context: this.element.dataset.context || 'default',
                },
                bubbles: true,
            }),
        );
        this.close();
    }

    close() {
        this.selected.clear();
        this.confirmTarget.disabled = true;
        this.element.dispatchEvent(new CustomEvent('modal:close'));
    }

    get listTarget() {
        return this.element.querySelector('[data-drive-attach-target="list"]');
    }

    get searchTarget() {
        return this.element.querySelector('[data-drive-attach-target="search"]');
    }

    get confirmTarget() {
        return this.element.querySelector('[data-drive-attach-target="confirm"]');
    }
}
