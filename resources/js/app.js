import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.apliToast = function apliToast(message, type = 'success') {
    const stackId = 'apli-toast-stack';
    let stack = document.getElementById(stackId);

    if (! stack) {
        stack = document.createElement('div');
        stack.id = stackId;
        stack.className = 'toast-stack';
        document.body.appendChild(stack);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
    toast.textContent = message;
    stack.appendChild(toast);

    window.setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(6px)';
        toast.style.transition = 'opacity 220ms ease, transform 220ms ease';

        window.setTimeout(() => toast.remove(), 220);
    }, 2400);
};

window.syncThemeToggleLabels = function syncThemeToggleLabels() {
    const isDark = document.documentElement.classList.contains('dark');
    const nextLabel = isDark ? 'Mode Terang' : 'Mode Gelap';

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.textContent = nextLabel;
    });
};

window.toggleApliTheme = function toggleApliTheme() {
    const root = document.documentElement;
    const isDark = root.classList.toggle('dark');

    localStorage.setItem('apli-theme', isDark ? 'dark' : 'light');
    window.syncThemeToggleLabels();
};

document.addEventListener('DOMContentLoaded', () => {
    window.syncThemeToggleLabels();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', window.toggleApliTheme);
    });

    document.querySelectorAll('[data-copy-text]').forEach((button) => {
        button.addEventListener('click', async () => {
            const text = button.dataset.copyText;

            if (! text) {
                return;
            }

            try {
                await navigator.clipboard.writeText(text);
                window.apliToast(button.dataset.copySuccess || 'Teks berhasil disalin.');
            } catch (error) {
                window.apliToast(button.dataset.copyError || 'Gagal menyalin teks.', 'error');
                console.error(error);
            }
        });
    });
});
