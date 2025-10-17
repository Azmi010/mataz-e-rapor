{{-- Filament Theme Sync Script --}}
<script>
    // Filament Theme Sync - Sinkronkan perubahan dark mode Filament ke localStorage
    (function() {
        const THEME_KEY = 'mataz-theme';

        // Fungsi untuk set theme di localStorage
        function saveTheme(isDark) {
            const theme = isDark ? 'dark' : 'light';
            localStorage.setItem(THEME_KEY, theme);
        }

        // Fungsi untuk load theme dari localStorage
        function loadTheme() {
            const theme = localStorage.getItem(THEME_KEY);
            return theme === 'dark';
        }

        // Set initial theme dari localStorage saat page load
        const savedIsDark = loadTheme();
        const html = document.documentElement;

        if (savedIsDark) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        // Observer untuk mendeteksi perubahan dark mode di Filament
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    const isDark = html.classList.contains('dark');
                    saveTheme(isDark);
                }
            });
        });

        // Start observing setelah DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                observer.observe(html, {
                    attributes: true,
                    attributeFilter: ['class']
                });
            });
        } else {
            observer.observe(html, {
                attributes: true,
                attributeFilter: ['class']
            });
        }

        // Listen untuk perubahan theme dari landing page (cross-tab sync)
        window.addEventListener('storage', function(e) {
            if (e.key === THEME_KEY && e.newValue) {
                const isDark = e.newValue === 'dark';

                if (isDark) {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
            }
        });
    })();
</script>
