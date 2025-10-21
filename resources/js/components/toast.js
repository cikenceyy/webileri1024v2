import { $, portal } from '../lib/dom.js';
import bus from '../lib/bus.js';
import { isMotionReduced } from './motion-runtime.js';

export const initToasts = () => {
    const region = $("[data-ui='toast-container']");
    if (!region) return;

    portal(region.parentElement ?? region);

    region.querySelectorAll('.ui-toast').forEach((toast) => {
        requestAnimationFrame(() => {
            toast.classList.add('is-ready');
        });
    });

    const createToast = ({ title, message, variant = 'info', timeout = 4000, progress = false }) => {
        const toast = document.createElement('div');
        toast.className = `ui-toast ui-toast--${variant}`;
        toast.setAttribute('role', 'status');
        toast.dataset.ui = 'toast';
        toast.dataset.timeout = timeout;
        toast.dataset.variant = variant;
        toast.dataset.message = message ?? '';
        toast.innerHTML = `
            <div class="ui-toast__content">
                ${title ? `<h3 class="ui-toast__title">${title}</h3>` : ''}
                ${message ? `<p class="ui-toast__message">${message}</p>` : ''}
            </div>
            <button type="button" class="ui-toast__dismiss" data-action="close" aria-label="Dismiss">×</button>
        `;

        if (progress && !isMotionReduced()) {
            const bar = document.createElement('span');
            bar.className = 'ui-toast__progress';
            toast.appendChild(bar);
        }

        const existing = Array.from(region.querySelectorAll('.ui-toast'));
        const duplicate = existing.find(
            (item) => item.dataset.variant === toast.dataset.variant && item.dataset.message === toast.dataset.message,
        );
        if (duplicate) {
            duplicate.remove();
        }

        requestAnimationFrame(() => {
            toast.classList.add('is-ready');
        });

        const remove = () => {
            toast.classList.remove('is-ready');
            toast.classList.add('is-leaving');
            const dispose = () => toast.remove();
            if (isMotionReduced()) {
                dispose();
            } else {
                toast.addEventListener('transitionend', dispose, { once: true });
            }
        };

        toast.querySelector('[data-action="close"]').addEventListener('click', remove);

        while (region.children.length >= 3) {
            region.firstElementChild?.remove();
        }

        region.appendChild(toast);

        if (timeout) {
            setTimeout(remove, timeout);
        }
    };

    region.addEventListener('click', (event) => {
        const target = event.target.closest('[data-action="close"]');
        if (target) {
            target.closest('.ui-toast')?.remove();
        }
    });

    bus.on('ui:toast:show', createToast);
};
