import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.toggleApliTheme = function toggleApliTheme() {
    const root = document.documentElement;
    const isDark = root.classList.toggle('dark');

    localStorage.setItem('apli-theme', isDark ? 'dark' : 'light');
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', window.toggleApliTheme);
    });
});
