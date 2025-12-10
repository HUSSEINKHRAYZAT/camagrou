(() => {
    const root = document.body;
    const themeSelect = document.getElementById('themeSelect');
    const accentPicker = document.getElementById('accentPicker');
    const THEME_KEY = 'camagrou-theme';
    const ACCENT_KEY = 'camagrou-accent';

    const applyTheme = (theme) => {
        if (!theme) return;
        root.setAttribute('data-theme', theme);
    };

    const applyAccent = (accent) => {
        if (!accent) return;
        root.style.setProperty('--accent', accent);
    };

    const detectPreferred = () => {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return 'dark';
        }
        return 'light';
    };

    const initTheme = () => {
        const stored = localStorage.getItem(THEME_KEY);
        const initial = stored || detectPreferred();
        applyTheme(initial);
        if (themeSelect) themeSelect.value = initial;
    };

    const initAccent = () => {
        const stored = localStorage.getItem(ACCENT_KEY);
        if (stored) {
            applyAccent(stored);
            if (accentPicker) accentPicker.value = stored;
        }
    };

    themeSelect?.addEventListener('change', (event) => {
        const val = event.target.value;
        applyTheme(val);
        localStorage.setItem(THEME_KEY, val);
    });

    accentPicker?.addEventListener('input', (event) => {
        const val = event.target.value;
        applyAccent(val);
        localStorage.setItem(ACCENT_KEY, val);
    });

    initTheme();
    initAccent();
})();
