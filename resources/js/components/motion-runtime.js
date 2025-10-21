import { $$ } from '../lib/dom.js';
import bus from '../lib/bus.js';

const html = document.documentElement;

let mediaQuery = null;
let motionState = 'soft';
let override = false;
let overlayDepth = 0;
const listeners = new Set();

const notify = () => {
    listeners.forEach((callback) => {
        try {
            callback(motionState);
        } catch (error) {
            // Ignore listener errors to avoid breaking motion runtime.
        }
    });
};

const applyState = (value) => {
    const mode = value === 'reduced' ? 'reduced' : 'soft';
    if (motionState === mode) {
        html.classList.toggle('is-motion-reduced', mode === 'reduced');
        html.toggleAttribute('data-motion-override', override);
        return motionState;
    }

    motionState = mode;
    html.setAttribute('data-motion', motionState);
    html.classList.toggle('is-motion-reduced', motionState === 'reduced');
    html.toggleAttribute('data-motion-override', override);
    notify();
    return motionState;
};

const updateOverlayFreeze = () => {
    const active = overlayDepth > 0;
    html.classList.toggle('is-overlay-open', active);
    $$("[data-ui='table'], [data-motion='list']").forEach((element) => {
        const manualFreeze = element.dataset.motionFreeze === 'true';
        element.classList.toggle('is-frozen', active || manualFreeze);
    });
};

const handleOverlayOpened = () => {
    overlayDepth += 1;
    updateOverlayFreeze();
};

const handleOverlayClosed = () => {
    overlayDepth = Math.max(0, overlayDepth - 1);
    updateOverlayFreeze();
};

const bindMediaQuery = () => {
    if (!window.matchMedia) return;
    mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    mediaQuery.addEventListener('change', (event) => {
        if (override) return;
        applyState(event.matches ? 'reduced' : 'soft');
    });
};

export const initMotionRuntime = ({ storedPreference = null, initial = null } = {}) => {
    bindMediaQuery();
    override = Boolean(storedPreference);

    const derived = storedPreference
        || (mediaQuery?.matches ? 'reduced' : null)
        || initial
        || html.getAttribute('data-motion')
        || 'soft';

    applyState(derived);

    bus.on('ui:overlay:open', handleOverlayOpened);
    bus.on('ui:overlay:close', handleOverlayClosed);
    bus.on('ui:overlay:closed', handleOverlayClosed);

    updateOverlayFreeze();
};

export const setMotionPreference = (value) => {
    override = true;
    return applyState(value);
};

export const clearMotionOverride = () => {
    override = false;
    const fallback = mediaQuery?.matches ? 'reduced' : 'soft';
    applyState(fallback);
};

export const getMotionState = () => motionState;

export const isMotionReduced = () => motionState === 'reduced';

export const onMotionChange = (callback) => {
    listeners.add(callback);
    return () => listeners.delete(callback);
};

export const freezeElement = (element, frozen) => {
    if (!element) return;
    if (frozen) {
        element.dataset.motionFreeze = 'true';
    } else {
        element.removeAttribute('data-motion-freeze');
    }

    const shouldFreeze = frozen || overlayDepth > 0;
    element.classList.toggle('is-frozen', shouldFreeze);
};
