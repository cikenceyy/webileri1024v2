import { $$, $ } from '../lib/dom.js';
import {
    initMotionRuntime,
    setMotionPreference,
    getMotionState,
    onMotionChange,
} from './motion-runtime.js';

const html = document.documentElement;
const STORAGE_KEYS = {
    theme: 'ui:theme',
    motion: 'ui:motion',
    sidebarMode: 'ui:sidebar',
};

const LEGACY_ALIASES = {
    [STORAGE_KEYS.theme]: ['ui:theme:v1'],
    [STORAGE_KEYS.motion]: ['ui:motion:v1'],
    [STORAGE_KEYS.sidebarMode]: ['ui:sidebar:v1', 'ui:sidebar:collapsed'],
};

const LEGACY_SINGLE_KEYS = ['ui:sidebar:variant', 'ui:sidebar:variant:v1'];
const LEGACY_PREFIXES = [
    'ui:table:v1:cols:',
    'ui:table:v2:cols:',
    'ui:table:cols:',
    'ui:table:reorder:',
    'ui:table:align:',
    'ui:perf-hud',
];

const safeGet = (key) => {
    try {
        return window.localStorage.getItem(key);
    } catch (error) {
        return null;
    }
};

const safeSet = (key, value) => {
    try {
        window.localStorage.setItem(key, value);
    } catch (error) {
        // Storage might be unavailable (private mode). Fail silently.
    }
};

const safeRemove = (key) => {
    try {
        window.localStorage.removeItem(key);
    } catch (error) {
        // Ignore failures when clearing legacy keys.
    }
};

const getStored = (key) => {
    const value = safeGet(key);
    if (value !== null) {
        return value;
    }
    const fallbacks = LEGACY_ALIASES[key] ?? [];
    for (const legacyKey of fallbacks) {
        const legacyValue = safeGet(legacyKey);
        if (legacyValue !== null) {
            safeSet(key, legacyValue);
            safeRemove(legacyKey);
            return legacyValue;
        }
    }
    return null;
};

const setStored = (key, value) => {
    safeSet(key, value);
    (LEGACY_ALIASES[key] ?? []).forEach((legacyKey) => safeRemove(legacyKey));
};

const runWhenReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => callback(), { once: true });
    } else {
        callback();
    }
};

const withSidebar = (callback) => {
    runWhenReady(() => {
        const sidebar = $('#sidebar');
        if (sidebar) {
            callback(sidebar);
        }
    });
};

const setPressedState = (buttons, datasetKey, value) => {
    buttons.forEach((button) => {
        const isActive = button.dataset[datasetKey] === value;
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
};

const markPressed = (selector, datasetKey, value) => {
    runWhenReady(() => {
        setPressedState($$(selector), datasetKey, value);
    });
};

const updateThemeComparison = (theme) => {
    runWhenReady(() => {
        const panel = $("[data-ui='theme-compare']");
        if (!panel) return;

        const items = panel.querySelectorAll('[data-theme]');
        let bestNote = '';

        items.forEach((item) => {
            const matches = item.dataset.theme === theme;
            item.toggleAttribute('hidden', !matches);
            const isBest = matches && item.dataset.grade === 'best';
            item.classList.toggle('is-best', isBest);
            if (isBest) {
                bestNote = item.dataset.note ?? '';
            }
        });

        const note = panel.querySelector("[data-ui='theme-compare-note']");
        if (note) {
            note.textContent = bestNote;
            note.toggleAttribute('hidden', bestNote.length === 0);
        }
    });
};

const applyTheme = (theme) => {
    if (!theme) return;
    html.setAttribute('data-theme', theme);
    markPressed("[data-action='theme']", 'theme', theme);
    updateThemeComparison(theme);
};

const updateSidebarToggles = (mode) => {
    runWhenReady(() => {
        $$("[data-action='toggle'][data-target='#sidebar']").forEach((toggle) => {
            toggle.setAttribute('aria-expanded', mode === 'compact' ? 'false' : 'true');
        });
    });
};

const setSidebarMode = (mode, { persist = true } = {}) => {
    const value = mode === 'compact' ? 'compact' : 'expanded';
    html.setAttribute('data-sidebar', value);
    withSidebar((sidebar) => {
        sidebar.classList.toggle('is-compact', value === 'compact');
    });
    updateSidebarToggles(value);
    if (persist) {
        setStored(STORAGE_KEYS.sidebarMode, value);
    }
};

const getSidebarMode = () => (html.getAttribute('data-sidebar') === 'compact' ? 'compact' : 'expanded');

const toggleSidebarMode = ({ persist = true } = {}) => {
    const next = getSidebarMode() === 'compact' ? 'expanded' : 'compact';
    setSidebarMode(next, { persist });
    return next;
};

const setSidebarVariant = (variant) => {
    const value = variant === 'chip' ? 'chip' : 'tooltip';
    withSidebar((sidebar) => {
        sidebar.dataset.variant = value;
    });
    markPressed("[data-action='sidebar-variant']", 'variant', value);
};

const initThemeControls = () => {
    runWhenReady(() => {
        const buttons = $$("[data-action='theme']");
        if (!buttons.length) return;
        const theme = html.getAttribute('data-theme') || 'soft-indigo';
        setPressedState(buttons, 'theme', theme);
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const nextTheme = button.dataset.theme;
                setStored(STORAGE_KEYS.theme, nextTheme);
                applyTheme(nextTheme);
            });
        });
    });
};

const initMotionControls = () => {
    runWhenReady(() => {
        const buttons = $$("[data-action='motion']");
        if (!buttons.length) return;
        const motion = getMotionState();
        setPressedState(buttons, 'motion', motion);
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const nextMotion = button.dataset.motion === 'reduced' ? 'reduced' : 'soft';
                setStored(STORAGE_KEYS.motion, nextMotion);
                setMotionPreference(nextMotion);
            });
        });
    });
};

const initSidebarVariants = () => {
    runWhenReady(() => {
        const buttons = $$("[data-action='sidebar-variant']");
        if (!buttons.length) return;
        const defaultVariant = 'tooltip';
        setSidebarVariant(defaultVariant);
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const nextVariant = button.dataset.variant === 'chip' ? 'chip' : 'tooltip';
                setSidebarVariant(nextVariant);
                setSidebarMode('compact', { persist: false });
            });
        });
    });
};

const initMotionShowroom = () => {
    runWhenReady(() => {
        const controls = $$("[data-action='motion-speed']");
        if (!controls.length) return;

        const groups = new Map();

        controls.forEach((button) => {
            const key = button.dataset.target;
            if (!groups.has(key)) {
                groups.set(key, []);
            }
            groups.get(key).push(button);

            button.addEventListener('click', () => {
                const target = document.querySelector(button.dataset.target);
                if (!target) return;

                const duration = button.dataset.duration;
                target.style.setProperty('--ui-time-custom', `${duration}ms`);
                groups.get(key).forEach((item) => {
                    const isActive = item === button;
                    item.classList.toggle('is-active', isActive);
                    item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            });
        });

        groups.forEach((buttons) => {
            const preferred = buttons.find((button) => button.dataset.default === 'true') ?? buttons[0];
            preferred?.click();
        });
    });
};

const initTabs = () => {
    runWhenReady(() => {
        $$("[data-ui='tabs']").forEach((tabs) => {
            const buttons = tabs.querySelectorAll('[role="tab"]');
            const panels = tabs.querySelectorAll('[role="tabpanel"]');

            const activate = (button) => {
                buttons.forEach((item) => {
                    const isActive = item === button;
                    item.classList.toggle('is-active', isActive);
                    item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    item.setAttribute('tabindex', isActive ? '0' : '-1');
                });

                panels.forEach((panel) => {
                    const shouldShow = `#${panel.id}` === button.dataset.target;
                    panel.classList.toggle('is-active', shouldShow);
                    panel.toggleAttribute('hidden', !shouldShow);
                });
            };

            buttons.forEach((button, index) => {
                button.addEventListener('click', () => activate(button));
                button.addEventListener('keydown', (event) => {
                    if (!['ArrowRight', 'ArrowLeft', 'Home', 'End'].includes(event.key)) return;
                    event.preventDefault();
                    const nextIndex =
                        event.key === 'ArrowRight'
                            ? (index + 1) % buttons.length
                            : event.key === 'ArrowLeft'
                            ? (index - 1 + buttons.length) % buttons.length
                            : event.key === 'Home'
                            ? 0
                            : buttons.length - 1;
                    buttons[nextIndex].focus();
                    activate(buttons[nextIndex]);
                });
            });

            const initial = Array.from(buttons).find((button) => button.getAttribute('aria-selected') === 'true') ?? buttons[0];
            if (initial) {
                activate(initial);
            }
        });
    });
};

const migrateSidebarPreference = () => {
    try {
        const legacy = window.localStorage.getItem('ui:sidebar:collapsed');
        if (legacy !== null && !getStored(STORAGE_KEYS.sidebarMode)) {
            const value = legacy === '1' ? 'compact' : 'expanded';
            setStored(STORAGE_KEYS.sidebarMode, value);
        }
        window.localStorage.removeItem('ui:sidebar:collapsed');
        window.localStorage.removeItem('ui:sidebar:v1');
    } catch (error) {
        // Ignore migration failures.
    }
};

const cleanLegacyStorage = () => {
    try {
        const store = window.localStorage;
        const keys = [];
        for (let index = 0; index < store.length; index += 1) {
            const key = store.key(index);
            if (key) {
                keys.push(key);
            }
        }

        keys.forEach((key) => {
            if (key.startsWith('ui:table:v1:density:')) {
                const suffix = key.replace('ui:table:v1:density:', '');
                const value = safeGet(key);
                if (value !== null) {
                    safeSet(`ui:table:density:${suffix}`, value);
                }
                safeRemove(key);
                return;
            }

            if (LEGACY_SINGLE_KEYS.includes(key)) {
                safeRemove(key);
                return;
            }

            if (LEGACY_PREFIXES.some((prefix) => key.startsWith(prefix))) {
                safeRemove(key);
            }
        });
    } catch (error) {
        // Storage cleanup is best effort only.
    }
};

export const bootstrapRuntime = () => {
    migrateSidebarPreference();
    cleanLegacyStorage();

    const storedTheme = getStored(STORAGE_KEYS.theme);
    const initialTheme = storedTheme || html.getAttribute('data-theme') || 'soft-indigo';
    applyTheme(initialTheme);

    setSidebarVariant('tooltip');

    const storedSidebar = getStored(STORAGE_KEYS.sidebarMode) || html.getAttribute('data-sidebar');
    const prefersCompact = window.matchMedia('(max-width: 992px)').matches;
    const initialSidebar = storedSidebar || (prefersCompact ? 'compact' : 'expanded');
    setSidebarMode(initialSidebar, { persist: false });

    const storedMotion = getStored(STORAGE_KEYS.motion);
    initMotionRuntime({ storedPreference: storedMotion, initial: html.getAttribute('data-motion') });
    markPressed("[data-action='motion']", 'motion', getMotionState());
};

export const initRuntimeControls = () => {
    initThemeControls();
    initMotionControls();
    initSidebarVariants();
    onMotionChange((motion) => {
        markPressed("[data-action='motion']", 'motion', motion);
    });
};

export const initGalleryShowcase = () => {
    initMotionShowroom();
    initTabs();
    updateThemeComparison(html.getAttribute('data-theme') || 'soft-indigo');
};

export { setSidebarMode, toggleSidebarMode, getSidebarMode };
