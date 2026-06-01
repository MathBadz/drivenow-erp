import { router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { configureCurrency } from '@/lib/format';

type FlashToast = { type: 'success' | 'info' | 'warning' | 'error'; message: string };
type SharedProps = {
    flash?: { toast?: FlashToast | null };
    settings?: { currency?: string; currency_symbol?: string };
};

function syncFromPage(): void {
    const page = usePage<SharedProps>();
    // Keep money formatting in sync with the dynamic currency setting.
    configureCurrency(page.props?.settings?.currency, page.props?.settings?.currency_symbol);

    const data = page.props?.flash?.toast;
    if (data?.message) {
        toast[data.type](data.message);
    }
}

/**
 * Bridges Laravel session flash messages to vue-sonner toasts and keeps the
 * active currency in sync with system settings. Runs once on load and after
 * every completed Inertia visit.
 */
export function initializeFlashToast(): void {
    syncFromPage();
    router.on('finish', () => syncFromPage());
}
