export function emitBeacon(eventName, payload = null, href = null) {
    const endpoint = document.body.dataset.beaconEndpoint;
    if (!navigator.sendBeacon || !endpoint) {
        return;
    }

    const body = {
        event: eventName || 'interaction',
        payload,
        href,
        timestamp: Date.now(),
    };

    try {
        const blob = new Blob([JSON.stringify(body)], { type: 'application/json' });
        navigator.sendBeacon(endpoint, blob);
    } catch (error) {
        // Ignore beacon errors silently.
    }
}

export function beacon(element) {
    if (!element) {
        return;
    }

    const eventName = element.dataset.beaconEvent || 'interaction';
    const payload = element.dataset.beaconPayload || null;

    const handler = () => {
        emitBeacon(eventName, payload, element.getAttribute('href') || null);
    };

    element.addEventListener('click', handler);
}
