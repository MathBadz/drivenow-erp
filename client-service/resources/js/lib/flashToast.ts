import { router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

type FlashProps = {
    flash?: {
        toast?: FlashToast | null;
    };
};

function fireFromPage(): void {
    const page = usePage<FlashProps>();
    const data = page.props?.flash?.toast;

    if (data?.message) {
        toast[data.type](data.message);
    }
}

/**
 * Bridges Laravel session flash messages to vue-sonner toasts. Reads the
 * shared `flash.toast` prop after every completed visit (and once on load).
 */
export function initializeFlashToast(): void {
    fireFromPage();
    router.on('finish', () => fireFromPage());
}
