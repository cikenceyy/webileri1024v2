import { $$ } from '../lib/dom.js';
import bus from '../lib/bus.js';
import { isMotionReduced } from './motion-runtime.js';

const STORAGE_KEY = (routeKey, tableId) => `ui:table:density:${routeKey}:${tableId}`;
const LEGACY_KEYS = (routeKey, tableId) => [
    `ui:table:v1:density:${routeKey}:${tableId}`,
    `ui:table:v1:density:${tableId}`,
];

const safeGet = (key) => {
    try {
        return window.localStorage.getItem(key);
    } catch (error) {
        return null;
    }
};

const safeRemove = (key) => {
    try {
        window.localStorage.removeItem(key);
    } catch (error) {
        // ignore removal failures (storage might be disabled)
    }
};

const idleWrites = new Map();

const requestIdle = (callback) => {
    if (typeof window.requestIdleCallback === 'function') {
        return window.requestIdleCallback(callback, { timeout: 320 });
    }
    return window.setTimeout(callback, 120);
};

const cancelIdle = (handle) => {
    if (!handle) return;
    if (typeof window.cancelIdleCallback === 'function') {
        window.cancelIdleCallback(handle);
    } else {
        window.clearTimeout(handle);
    }
};

const writePreference = (key, value) => {
    try {
        window.localStorage.setItem(key, value);
    } catch (error) {
        // Ignore persistence failures (e.g. storage disabled).
    }
};

const schedulePreference = (key, value) => {
    const handle = idleWrites.get(key);
    if (handle) {
        cancelIdle(handle);
    }
    const nextHandle = requestIdle(() => {
        writePreference(key, value);
        idleWrites.delete(key);
    });
    idleWrites.set(key, nextHandle);
};

const clampDensity = (value) => (value === 'compact' ? 'compact' : 'comfortable');

const migrateLegacyPreference = (routeKey, tableId) => {
    const legacyKeys = LEGACY_KEYS(routeKey, tableId);
    for (const legacyKey of legacyKeys) {
        const legacyValue = safeGet(legacyKey);
        if (legacyValue !== null) {
            schedulePreference(STORAGE_KEY(routeKey, tableId), clampDensity(legacyValue));
            safeRemove(legacyKey);
            return legacyValue;
        }
    }
    return null;
};

const readPreference = (routeKey, tableId) => {
    const key = STORAGE_KEY(routeKey, tableId);
    const stored = safeGet(key);
    if (stored !== null) {
        return stored;
    }
    return migrateLegacyPreference(routeKey, tableId);
};

export const applyDensity = (table, density) => {
    const next = clampDensity(density);
    table.dataset.density = next;
    table.classList.toggle('ui-table--dense', next === 'compact');
    if (!isMotionReduced()) {
        table.classList.add('has-change');
        window.setTimeout(() => {
            table.classList.remove('has-change');
        }, 240);
    } else {
        table.classList.remove('has-change');
    }
    const state = table.querySelector('[data-ui="density-state"]');
    if (state) {
        state.textContent = next === 'compact' ? 'Compact' : 'Comfortable';
    }

    const toggle = table.querySelector('[data-action="table-density"]');
    if (toggle) {
        toggle.setAttribute('aria-pressed', next === 'compact' ? 'true' : 'false');
    }
};

export const initDensityToggle = () => {
    $$("[data-ui='table']").forEach((table) => {
        const routeKey = table.dataset.routeKey || 'default';
        const tableId = table.dataset.tableId || 'default';
        const stored = readPreference(routeKey, tableId);
        applyDensity(table, stored || table.dataset.density || 'compact');

        table.querySelectorAll('[data-action="table-density"]').forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const next = table.dataset.density === 'compact' ? 'comfortable' : 'compact';
                applyDensity(table, next);
                schedulePreference(STORAGE_KEY(routeKey, tableId), next);
                bus.emit('ui:table:density', { element: table, density: next, tableId, routeKey });
            });
        });
    });
};

export const readDensityPreference = (routeKey, tableId) => readPreference(routeKey, tableId);

export const persistDensityPreference = (routeKey, tableId, density) => {
    schedulePreference(STORAGE_KEY(routeKey, tableId), clampDensity(density));
};
