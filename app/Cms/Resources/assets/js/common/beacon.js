export function beacon(element) {
    if (!element) {
        return;
    }

    const endpoint = element.dataset.beaconEndpoint || document.body.dataset.beaconEndpoint;
    if (!navigator.sendBeacon || !endpoint) {
        return;
    }

    const eventName = element.dataset.beaconEvent || 'interaction';
    const payload = element.dataset.beaconPayload || null;

    const handler = () => {
        const body = {
            event: eventName,
            payload,
            href: element.getAttribute('href') || null,
            timestamp: Date.now(),
        };

        try {
            const blob = new Blob([JSON.stringify(body)], { type: 'application/json' });
            navigator.sendBeacon(endpoint, blob);
        } catch (error) {
            // Ignore beacon errors silently.
        }
    };

    element.addEventListener('click', handler);
}
