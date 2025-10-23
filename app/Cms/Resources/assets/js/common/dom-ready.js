export function domReady(callback) {
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        queueMicrotask(callback);
        return;
    }

    document.addEventListener('DOMContentLoaded', callback, { once: true });
}
