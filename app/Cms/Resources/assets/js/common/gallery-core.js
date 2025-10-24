export function galleryCore(element) {
    if (!element) {
        return;
    }

    const mainImage = element.querySelector('.gallery-main img');
    const buttons = Array.from(element.querySelectorAll('.gallery-thumbs .thumb'));

    if (!mainImage || !buttons.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia
        ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
        : false;

    const updateImage = (button) => {
        const src = button.dataset.gallerySrc;
        if (!src) {
            return;
        }

        const thumbAlt = button.dataset.galleryAlt || button.querySelector('img')?.alt || mainImage.alt;

        if (!prefersReducedMotion) {
            mainImage.classList.add('is-transitioning');
        }

        mainImage.src = src;
        mainImage.srcset = `${src} 1280w, ${src} 960w`;
        if (thumbAlt) {
            mainImage.alt = thumbAlt;
        }

        buttons.forEach((btn) => {
            btn.setAttribute('aria-selected', btn === button ? 'true' : 'false');
        });

        if (!prefersReducedMotion) {
            requestAnimationFrame(() => {
                mainImage.classList.remove('is-transitioning');
            });
        }
    };

    const focusButtonByOffset = (currentIndex, delta) => {
        const nextIndex = (currentIndex + delta + buttons.length) % buttons.length;
        const nextButton = buttons[nextIndex];
        nextButton.focus();
        updateImage(nextButton);
    };

    buttons.forEach((button, index) => {
        if (!button.hasAttribute('aria-selected')) {
            button.setAttribute('aria-selected', index === 0 ? 'true' : 'false');
        }

        if (!button.dataset.galleryAlt) {
            const thumbAlt = button.querySelector('img')?.alt;
            if (thumbAlt) {
                button.dataset.galleryAlt = thumbAlt;
            }
        }

        button.addEventListener('click', (event) => {
            event.preventDefault();
            updateImage(button);
        });

        button.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                updateImage(button);
                return;
            }

            if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
                event.preventDefault();
                focusButtonByOffset(index, 1);
            }

            if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
                event.preventDefault();
                focusButtonByOffset(index, -1);
            }
        });
    });

    updateImage(buttons[0]);
}
