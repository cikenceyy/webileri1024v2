import { bootPage } from '../common/page-boot';

bootPage('kvkk', () => {
    const toc = document.querySelector('.kvkk-toc');
    if (!toc) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        const first = toc.querySelector('a[href^="#"]');
        if (first) {
            first.classList.add('is-active');
        }
    }
});
