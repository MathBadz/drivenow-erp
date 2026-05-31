import { onMounted, ref } from 'vue';
import type { ComputedRef, Ref } from 'vue';
import { computed } from 'vue';

export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type UseAppearanceReturn = {
    appearance: Ref<Appearance>;
    resolvedAppearance: ComputedRef<ResolvedAppearance>;
    updateAppearance: (value: Appearance) => void;
};

export function updateTheme(value: Appearance): void {
    if (typeof window === 'undefined') {
        return;
    }

    if (value === 'system') {
        const systemTheme = window.matchMedia('(prefers-color-scheme: dark)')
            .matches
            ? 'dark'
            : 'light';
        document.documentElement.classList.toggle('dark', systemTheme === 'dark');
    } else {
        document.documentElement.classList.toggle('dark', value === 'dark');
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }
    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = () =>
    typeof window === 'undefined'
        ? null
        : (localStorage.getItem('appearance') as Appearance | null);

const prefersDark = () =>
    typeof window !== 'undefined' &&
    window.matchMedia('(prefers-color-scheme: dark)').matches;

export function initializeTheme(): void {
    if (typeof window === 'undefined') {
        return;
    }
    updateTheme(getStoredAppearance() || 'system');
    window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', () =>
            updateTheme(getStoredAppearance() || 'system'),
        );
}

const appearance = ref<Appearance>('system');

export function useAppearance(): UseAppearanceReturn {
    onMounted(() => {
        const saved = localStorage.getItem('appearance') as Appearance | null;
        if (saved) {
            appearance.value = saved;
        }
    });

    const resolvedAppearance = computed<ResolvedAppearance>(() =>
        appearance.value === 'system'
            ? prefersDark()
                ? 'dark'
                : 'light'
            : appearance.value,
    );

    function updateAppearance(value: Appearance) {
        appearance.value = value;
        localStorage.setItem('appearance', value);
        setCookie('appearance', value);
        updateTheme(value);
    }

    return { appearance, resolvedAppearance, updateAppearance };
}
