// Filament Theme Sync - Sinkronkan perubahan dark mode Filament ke localStorage
document.addEventListener('DOMContentLoaded', function() {
    const THEME_KEY = 'mataz-theme';
    
    // Fungsi untuk set theme di localStorage
    function saveTheme(isDark) {
        const theme = isDark ? 'dark' : 'light';
        localStorage.setItem(THEME_KEY, theme);
        
        // Dispatch event untuk sync dengan landing page
        window.dispatchEvent(new StorageEvent('storage', {
            key: THEME_KEY,
            newValue: theme
        }));
    }
    
    // Fungsi untuk load theme dari localStorage
    function loadTheme() {
        const theme = localStorage.getItem(THEME_KEY);
        return theme === 'dark';
    }
    
    // Set initial theme dari localStorage
    const savedIsDark = loadTheme();
    if (savedIsDark !== null) {
        const html = document.documentElement;
        if (savedIsDark) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    }
    
    // Observer untuk mendeteksi perubahan dark mode di Filament
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class') {
                const isDark = document.documentElement.classList.contains('dark');
                saveTheme(isDark);
            }
        });
    });
    
    // Start observing
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });
    
    // Listen untuk perubahan theme dari landing page
    window.addEventListener('storage', function(e) {
        if (e.key === THEME_KEY) {
            const isDark = e.newValue === 'dark';
            const html = document.documentElement;
            
            if (isDark) {
                html.classList.add('dark');
            } else {
                html.classList.remove('dark');
            }
        }
    });
});
