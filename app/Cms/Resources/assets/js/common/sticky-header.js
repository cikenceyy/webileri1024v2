export function stickyHeader(element) {
    if (!element) {
        return;
    }

    let lastScroll = window.scrollY;

    const onScroll = () => {
        const current = window.scrollY;
        element.classList.toggle('is-condensed', current > 64 && current > lastScroll);
        lastScroll = current;
    };

    window.addEventListener('scroll', onScroll, { passive: true });
}
