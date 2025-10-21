import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    window.dispatchEvent(new CustomEvent('app:ready'));
});
