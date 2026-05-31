import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AuthLayout from '@/layouts/AuthLayout.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [PublicLayout, SettingsLayout];
            // Storefront + customer account both use the public chrome.
            default:
                return PublicLayout;
        }
    },
    progress: {
        color: '#F59E0B',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
