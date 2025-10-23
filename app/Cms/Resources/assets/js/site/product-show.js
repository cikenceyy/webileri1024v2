import { bootPage } from '../common/page-boot';

bootPage('product-show', () => {
    const gallery = document.querySelector('[data-module="light-gallery"]');
    if (!gallery) return;

    const main = gallery.querySelector('.gallery-main img');
    const buttons = gallery.querySelectorAll('.gallery-thumbs .thumb');
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            buttons.forEach((btn) => btn.classList.remove('is-active'));
            button.classList.add('is-active');
            const src = button.dataset.gallerySrc;
            if (src && main) {
                main.src = src;
                main.srcset = `${src} 960w`;
            }
        });
    });
});
