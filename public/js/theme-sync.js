// Theme Sync Script - Sinkronisasi dark/light mode antara Filament dan Landing Page
(function() {
    'use strict';

    const THEME_KEY = 'mataz-theme';

    // Fungsi untuk mendapatkan theme dari localStorage
    function getStoredTheme() {
        return localStorage.getItem(THEME_KEY);
    }

    // Fungsi untuk menyimpan theme ke localStorage
    function setStoredTheme(theme) {
        localStorage.setItem(THEME_KEY, theme);
    }

    // Fungsi untuk menerapkan theme
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        setStoredTheme(theme);

        // Dispatch event untuk notifikasi perubahan theme
        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: { theme }
        }));
    }

    // Fungsi untuk toggle theme
    function toggleTheme() {
        const currentTheme = getStoredTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
        return newTheme;
    }

    // Fungsi untuk mendapatkan theme saat ini
    function getCurrentTheme() {
        return getStoredTheme() || 'light';
    }

    // Inisialisasi theme saat page load
    function initTheme() {
        const theme = getStoredTheme() || 'light';
        applyTheme(theme);
    }

    // Jalankan saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }

    // Listen untuk perubahan theme dari Filament
    window.addEventListener('storage', function(e) {
        if (e.key === THEME_KEY) {
            applyTheme(e.newValue || 'light');
        }
    });

    // Expose fungsi ke window untuk digunakan di mana saja
    window.matazTheme = {
        toggle: toggleTheme,
        set: applyTheme,
        get: getCurrentTheme
    };
})();
