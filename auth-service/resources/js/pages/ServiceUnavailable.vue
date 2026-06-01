<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, RotateCw, ServerCrash } from 'lucide-vue-next';

defineProps<{
    service: string;
    url: string | null;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Service unavailable', href: '/service-unavailable' }],
    },
});
</script>

<template>
    <Head title="Service unavailable" />

    <div class="flex min-h-full flex-1 items-center justify-center p-6">
        <div class="w-full max-w-md rounded-2xl border border-border bg-card p-8 text-center shadow-card motion-safe:animate-[fadeUp_0.4s_ease-out_both]">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500/15">
                <ServerCrash class="h-8 w-8 text-amber-500" />
            </div>

            <p class="mt-5 text-[11px] font-semibold tracking-widest text-amber-500 uppercase">503 · Service unavailable</p>
            <h1 class="font-display mt-1.5 text-xl font-bold tracking-tight text-foreground">{{ service }} is currently offline</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                We couldn't reach this service right now — it may be starting up, restarting, or temporarily down. The rest of the platform is unaffected.
            </p>

            <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                <Link
                    href="/dashboard"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 transition-colors hover:bg-amber-400"
                >
                    <ArrowLeft class="h-4 w-4" /> Back to Operations Hub
                </Link>
                <a
                    v-if="url"
                    :href="url"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-border px-5 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    <RotateCw class="h-4 w-4" /> Try again
                </a>
            </div>
        </div>
    </div>
</template>
