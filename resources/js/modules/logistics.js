const statusToneMap = {
    draft: 'secondary',
    preparing: 'info',
    in_transit: 'warning',
    delivered: 'success',
    cancelled: 'danger',
};

const markFilters = () => {
    document.querySelectorAll('[data-logistics-filters]').forEach((card) => {
        const form = card.matches('form') ? card : card.querySelector('form');
        if (!form) return;

        const sync = () => {
            const hasValue = Array.from(form.elements).some((element) => {
                if (!(element instanceof HTMLInputElement || element instanceof HTMLSelectElement || element instanceof HTMLTextAreaElement)) {
                    return false;
                }
                if (element.type === 'checkbox' || element.type === 'radio') {
                    return element.checked;
                }
                return element.value && element.value.trim() !== '';
            });
            card.dataset.state = hasValue ? 'filtered' : 'idle';
        };

        form.addEventListener('input', sync);
        form.addEventListener('change', sync);
        sync();
    });
};

const enhanceStatusBadges = () => {
    document.querySelectorAll('[data-logistics-status]').forEach((badge) => {
        const status = badge.dataset.logisticsStatus;
        if (!status) return;
        const tone = statusToneMap[status] ?? 'secondary';
        badge.dataset.statusTone = tone;
        badge.setAttribute('data-status-label', status);
    });
};

const initRowHighlights = () => {
    document.querySelectorAll('[data-logistics-row]').forEach((row) => {
        row.addEventListener('mouseenter', () => row.setAttribute('data-hover', 'true'));
        row.addEventListener('mouseleave', () => row.removeAttribute('data-hover'));
    });
};

const initDeleteGuards = () => {
    document.querySelectorAll('[data-logistics-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmMessage || 'Bu kaydı silmek istediğinize emin misiniz?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
};

export const initLogisticsModule = () => {
    if (document.body.dataset.module !== 'logistics') return;

    markFilters();
    enhanceStatusBadges();
    initRowHighlights();
    initDeleteGuards();
};
