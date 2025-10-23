import { bootPage } from '../common/page-boot';

bootPage('kvkk', () => {
    const toc = document.querySelector('.kvkk-toc');
    const content = document.querySelector('[data-module="toc-observer"]');
    if (!toc || !content || !('IntersectionObserver' in window)) {
        return;
    }

    const links = Array.from(toc.querySelectorAll('a[href^="#"]'));
    if (!links.length) {
        return;
    }

    const sectionMap = new Map();
    links.forEach((link) => {
        const id = link.getAttribute('href').slice(1);
        const section = document.getElementById(id);
        if (section) {
            sectionMap.set(section, link);
        }
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const link = sectionMap.get(entry.target);
            if (!link) return;
            if (entry.isIntersecting) {
                links.forEach((item) => item.classList.toggle('is-active', item === link));
            }
        });
    }, { rootMargin: '-30% 0px -60% 0px', threshold: 0.1 });

    sectionMap.forEach((_, section) => observer.observe(section));
});
