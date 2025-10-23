import { bootPage } from './core';

bootPage('catalogs', () => {
    const cards = document.querySelectorAll('.catalog-card');
    cards.forEach((card, index) => {
        card.style.transitionDelay = `${index * 40}ms`;
    });
});
