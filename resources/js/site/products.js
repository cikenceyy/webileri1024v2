import { bootPage } from './core';

bootPage('products', () => {
    const filters = document.querySelectorAll('[data-filter]');
    filters.forEach((filter) => {
        filter.addEventListener('click', (event) => {
            event.preventDefault();
        });
    });
});
