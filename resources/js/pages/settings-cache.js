// Admin önbellek yönetim sayfası JS bloğu: ısıtma/temizlik butonlarını AJAX ile yönetir.
import bus from '../lib/bus.js';

const formatRelative = (iso) => {
    if (!iso) return 'Kayıt yok';
    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) {
        return '---';
    }

    const formatter = new Intl.DateTimeFormat('tr-TR', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });

    return formatter.format(date);
};

const updateStatus = (root, meta) => {
    const store = (root.dataset.store ?? '').toUpperCase();
    const prefix = root.dataset.prefix ?? '';
    const sourceMeta = meta ?? {
        last_warm: root.dataset.lastWarm ?? null,
        last_flush: root.dataset.lastFlush ?? null,
    };

    root.querySelector('[data-cache-stat="store"]').textContent = store;
    root.querySelector('[data-cache-stat="prefix"]').textContent = prefix;
    root.querySelector('[data-cache-stat="last_warm"]').textContent = formatRelative(sourceMeta.last_warm ?? null);
    root.querySelector('[data-cache-stat="last_flush"]').textContent = formatRelative(sourceMeta.last_flush ?? null);

    if (meta) {
        root.dataset.lastWarm = meta.last_warm ?? '';
        root.dataset.lastFlush = meta.last_flush ?? '';
    }
};

const renderEvents = (root, events) => {
    const list = root.querySelector('[data-cache-events]');
    const counter = root.querySelector('[data-cache-event-count]');

    counter.textContent = events.length;
    list.innerHTML = '';

    if (events.length === 0) {
        const empty = document.createElement('li');
        empty.className = 'list-group-item text-muted';
        empty.textContent = 'Henüz log kaydı bulunmuyor.';
        list.appendChild(empty);
        return;
    }

    events.forEach((event) => {
        const item = document.createElement('li');
        item.className = 'list-group-item d-flex flex-column gap-1';

        const title = document.createElement('span');
        title.className = 'fw-semibold';
        title.textContent = `${event.action.toUpperCase()} ${event.summary ? `• ${event.summary}` : ''}`.trim();

        const subtitle = document.createElement('span');
        subtitle.className = 'text-muted small';
        subtitle.textContent = `${formatRelative(event.timestamp)} • Store: ${event.store}`;

        item.append(title, subtitle);
        list.appendChild(item);
    });
};

const toggleBusy = (root, busy) => {
    root.dataset.busy = busy ? 'true' : 'false';
    root.querySelectorAll('[data-cache-action]').forEach((button) => {
        button.toggleAttribute('disabled', busy);
    });
};

const submitRequest = async (url, payload, csrf) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        const error = await response.json().catch(() => ({ message: 'Beklenmedik hata' }));
        throw new Error(error.message ?? 'Beklenmedik hata');
    }

    return response.json();
};

const initCachePage = () => {
    const root = document.querySelector('[data-cache-page]');
    if (!root) return;

    const entitySelect = root.querySelector('[data-cache-entity]');
    const warmUrl = root.dataset.warmUrl;
    const flushUrl = root.dataset.flushUrl;
    const csrf = root.dataset.csrf;
    const defaultEntities = ['menu', 'sidebar', 'dashboard', 'drive'];
    const defaultTags = ['menu', 'sidebar', 'dashboard', 'drive', 'settings', 'permissions'];

    const handle = async (action) => {
        if (root.dataset.busy === 'true') {
            return;
        }

        try {
            toggleBusy(root, true);
            let payload;
            let endpoint;

            if (action === 'warm-all') {
                endpoint = warmUrl;
                payload = { entities: defaultEntities };
            } else if (action === 'warm-selected') {
                endpoint = warmUrl;
                payload = { entities: [entitySelect.value] };
            } else if (action === 'flush-selected') {
                endpoint = flushUrl;
                payload = { tags: [entitySelect.value] };
            } else if (action === 'flush-all') {
                endpoint = flushUrl;
                payload = { tags: defaultTags, hard: true };
            } else {
                return;
            }

            const data = await submitRequest(endpoint, payload, csrf);

            if (data.store) {
                root.dataset.store = data.store;
            }

            if (data.prefix) {
                root.dataset.prefix = data.prefix;
            }

            updateStatus(root, data.meta ?? null);
            renderEvents(root, data.events ?? []);

            bus.emit('ui:toast:show', {
                variant: 'success',
                message: data.message ?? 'İşlem tamamlandı.',
            });
        } catch (error) {
            bus.emit('ui:toast:show', {
                variant: 'danger',
                message: error.message ?? 'İşlem başarısız.',
            });
        } finally {
            toggleBusy(root, false);
        }
    };

    root.querySelectorAll('[data-cache-action]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            handle(button.dataset.cacheAction);
        });
    });

    updateStatus(root, null);
};

document.addEventListener('DOMContentLoaded', initCachePage);
