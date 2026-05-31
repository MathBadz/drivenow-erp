import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';
import 'vue-sonner/style.css';
import { initializeTheme } from '@/composables/useAppearance';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', {
            eager: true,
        });

        return pages[`./pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#F59E0B',
    },
});

initializeTheme();
initializeFlashToast();
