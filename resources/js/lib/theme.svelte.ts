export type ThemePreference = 'light' | 'dark';

class ThemeManager {
    theme = $state<ThemePreference>('light');

    constructor() {
        if (typeof window !== 'undefined') {
            const raw = localStorage.getItem('catatduit_theme');
            const saved: ThemePreference = raw === 'dark' ? 'dark' : 'light';
            this.theme = saved;
            if (raw === 'system') {
                localStorage.setItem('catatduit_theme', 'light');
            }
            this.apply(saved);
        }
    }

    get isDark(): boolean {
        return this.theme === 'dark';
    }

    get resolvedTheme(): 'light' | 'dark' {
        return this.theme;
    }

    setTheme(newTheme: ThemePreference) {
        const validated: ThemePreference =
            newTheme === 'dark' ? 'dark' : 'light';
        this.theme = validated;
        if (typeof window !== 'undefined') {
            localStorage.setItem('catatduit_theme', validated);
            this.apply(validated);
        }
    }

    toggleTheme(): ThemePreference {
        const nextTheme: ThemePreference =
            this.theme === 'dark' ? 'light' : 'dark';
        this.setTheme(nextTheme);
        return nextTheme;
    }

    cycleTheme(): ThemePreference {
        return this.toggleTheme();
    }

    apply(targetTheme: ThemePreference) {
        if (typeof document === 'undefined') return;

        const isDark = targetTheme === 'dark';

        // Disable all CSS transitions temporarily to make the theme change instantaneous
        const css = document.createElement('style');
        css.appendChild(
            document.createTextNode(
                '*, *::before, *::after { -webkit-transition: none !important; -moz-transition: none !important; -o-transition: none !important; -ms-transition: none !important; transition: none !important; animation: none !important; }',
            ),
        );
        document.head.appendChild(css);

        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Force synchronous style recalculation
        (() => window.getComputedStyle(document.body).opacity)();

        // Re-enable transitions on the next animation frame
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                if (document.head.contains(css)) {
                    document.head.removeChild(css);
                }
            });
        });
    }
}

export const themeManager = new ThemeManager();
