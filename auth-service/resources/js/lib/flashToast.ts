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
 * Bridges Laravel session flash messages to vue-sonner toasts.
 *
 * The `HandleInertiaRequests` middleware maps `success`/`error`/`warning`/`info`
 * session keys onto a `flash.toast` shared prop. Because session flash data is
 * only present on the response immediately following `back()->with(...)`, we
 * read it after every completed visit (and once on initial load).
 */
export function initializeFlashToast(): void {
    fireFromPage();
    router.on('finish', () => fireFromPage());
}
