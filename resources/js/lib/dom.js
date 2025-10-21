export const $$ = (selector, context = document) => Array.from(context.querySelectorAll(selector));
export const $ = (selector, context = document) => context.querySelector(selector);

export const toggleAttr = (element, name, valueA = 'true', valueB = 'false') => {
    if (!element) return;
    element.setAttribute(name, element.getAttribute(name) === valueA ? valueB : valueA);
};

const PORTAL_ATTRIBUTE = 'data-ui-portal-root';

export const ensurePortalRoot = () => {
    let root = document.querySelector(`[${PORTAL_ATTRIBUTE}]`);
    if (!root) {
        root = document.createElement('div');
        root.setAttribute(PORTAL_ATTRIBUTE, '');
        document.body.appendChild(root);
    }
    return root;
};

export const portal = (element, target = ensurePortalRoot()) => {
    if (!element || !target) return;
    if (element.parentElement === target) return;
    target.appendChild(element);
};

let scrollLockCount = 0;
let previousOverflow = '';
let previousPadding = '';

const getScrollbarWidth = () => {
    const { clientWidth } = document.documentElement;
    return Math.max(0, window.innerWidth - clientWidth);
};

export const lockScroll = () => {
    if (typeof document === 'undefined') return;
    if (scrollLockCount === 0) {
        previousOverflow = document.body.style.overflow;
        previousPadding = document.body.style.paddingRight;
        const scrollbar = getScrollbarWidth();
        document.body.style.overflow = 'hidden';
        if (scrollbar > 0) {
            document.body.style.paddingRight = `${scrollbar}px`;
        }
        document.body.classList.add('is-scroll-locked');
    }
    scrollLockCount += 1;
};

export const unlockScroll = () => {
    if (scrollLockCount === 0) return;
    scrollLockCount -= 1;
    if (scrollLockCount === 0) {
        document.body.style.overflow = previousOverflow;
        document.body.style.paddingRight = previousPadding;
        document.body.classList.remove('is-scroll-locked');
    }
};
