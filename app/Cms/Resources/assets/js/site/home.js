import { bootPage } from '../common/page-boot';

bootPage('home', () => {
    const hero = document.querySelector('.p-hero');
    if (!hero) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        hero.classList.add('is-visible');
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.35 }
    );

    observer.observe(hero);
});
