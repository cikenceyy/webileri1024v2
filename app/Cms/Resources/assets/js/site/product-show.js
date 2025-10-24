import { bootPage } from '../common/page-boot';

bootPage('product-show', () => {
    const meta = document.querySelector('.product-meta');
    if (meta) {
        meta.setAttribute('role', 'list');
        meta.querySelectorAll('li').forEach((item) => item.setAttribute('role', 'listitem'));
    }
});
