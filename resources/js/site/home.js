import { bootPage } from './core';

bootPage('home', () => {
    const hero = document.querySelector('.home-hero');
    if (!hero) return;

    const observer = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('home-hero--visible');
            }
        });
    }, { threshold: 0.35 }) : null;

    if (observer) {
        observer.observe(hero);
    } else {
        hero.classList.add('home-hero--visible');
    }
});
