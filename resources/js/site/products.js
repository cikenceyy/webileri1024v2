const sendAnalytics = (action) => {
    if (!action || !navigator.sendBeacon) return;
    navigator.sendBeacon('/analytics', JSON.stringify({ action }));
};

document.querySelectorAll('[data-analytics-click]').forEach((element) => {
    element.addEventListener('click', () => sendAnalytics(element.dataset.analyticsClick));
});
