export function lightGallery(element) {
    if (!element) {
        return;
    }

    const mainImage = element.querySelector('.gallery-main img');
    const buttons = element.querySelectorAll('.gallery-thumbs .thumb');

    if (!mainImage || !buttons.length) {
        return;
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const src = button.dataset.gallerySrc;
            if (!src) {
                return;
            }
            mainImage.src = src;
            mainImage.srcset = `${src} 960w`;
        });

        button.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                button.click();
            }
        });
    });
}
