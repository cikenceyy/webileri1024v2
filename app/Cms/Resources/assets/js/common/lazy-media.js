export function lazyMedia(element) {
    if (!element) {
        return;
    }

    const media = element.querySelectorAll('img, iframe');

    media.forEach((node) => {
        if (node.tagName === 'IMG' && !node.hasAttribute('loading')) {
            node.loading = 'lazy';
        }

        const skeletonHost = node.closest('[data-skeleton]');
        const clearSkeleton = () => {
            skeletonHost?.removeAttribute('data-skeleton');
            skeletonHost?.classList.remove('placeholder');
        };

        if (node.complete) {
            clearSkeleton();
            return;
        }

        node.addEventListener('load', clearSkeleton, { once: true });
        node.addEventListener('error', clearSkeleton, { once: true });
    });
}
