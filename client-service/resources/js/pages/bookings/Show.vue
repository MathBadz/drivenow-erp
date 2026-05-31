<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Ban, CalendarCheck, Car, CheckCircle2, MapPin } from 'lucide-vue-next';
import { computed } from 'vue';
import { formatCurrency, formatDate } from '@/lib/format';
import type { Booking } from '@/types';

const props = defineProps<{ booking: Booking }>();
const b = computed(() => props.booking);

const badgeClass = computed(
    () => ({
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        confirmed: 'badge-reserved',
        active: 'badge-available',
        completed: 'badge-inactive',
        cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[b.value.status] ?? 'badge-inactive',
);

const canCancel = computed(() => ['pending', 'confirmed'].includes(b.value.status));

function cancel() {
    router.post(`/bookings/${b.value.id}/cancel`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="b.reference" />

    <section class="mx-auto w-full max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        <Link href="/dashboard" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
            <ArrowLeft class="h-4 w-4" /> Back to my trips
        </Link>

        <div class="mt-6 overflow-hidden rounded-2xl border border-border bg-card shadow-card">
            <!-- Confirmation header -->
            <div class="relative overflow-hidden bg-[#0f172a] px-6 py-8 text-white">
                <div class="pointer-events-none absolute -top-10 -right-8 h-40 w-40 rounded-full bg-amber-500/20 blur-2xl" />
                <div class="relative flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-500 text-amber-950">
                        <CheckCircle2 class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="font-mono text-xs text-slate-300">{{ b.reference }}</p>
                        <h1 class="font-display text-xl font-bold">Booking {{ b.status }}</h1>
                    </div>
                    <span class="badge ml-auto" :class="badgeClass">{{ b.status }}</span>
                </div>
            </div>

            <!-- Details -->
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-muted"><Car class="h-6 w-6 text-muted-foreground" /></span>
                    <div>
                        <p class="font-display text-lg font-bold text-foreground">{{ b.vehicle_name }}</p>
                        <p v-if="b.vehicle_plate" class="font-mono text-xs text-muted-foreground">{{ b.vehicle_plate }}</p>
                    </div>
                </div>

                <dl class="mt-6 grid grid-cols-2 gap-4">
                    <div>
                        <dt class="flex items-center gap-1.5 text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"><CalendarCheck class="h-3.5 w-3.5" /> Pickup</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatDate(b.pickup_date) }}</dd>
                    </div>
                    <div>
                        <dt class="flex items-center gap-1.5 text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"><CalendarCheck class="h-3.5 w-3.5" /> Return</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ formatDate(b.return_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Duration</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">{{ b.days }} day(s)</dd>
                    </div>
                    <div>
                        <dt class="flex items-center gap-1.5 text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"><MapPin class="h-3.5 w-3.5" /> Pickup point</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">Main Branch, Bacolod</dd>
                    </div>
                </dl>

                <div v-if="b.notes" class="mt-5 rounded-xl bg-muted/50 p-4">
                    <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Notes</p>
                    <p class="mt-1 text-sm text-foreground">{{ b.notes }}</p>
                </div>

                <div class="mt-6 space-y-2 border-t border-border pt-5 text-sm">
                    <div class="flex justify-between text-muted-foreground">
                        <span>{{ formatCurrency(b.daily_rate) }} × {{ b.days }} day(s)</span>
                        <span>{{ formatCurrency(b.total) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span class="text-foreground">Total</span>
                        <span class="font-display text-lg text-foreground">{{ formatCurrency(b.total) }}</span>
                    </div>
                </div>

                <button v-if="canCancel" type="button" class="mt-6 inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-red-200 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:border-red-800/50 dark:hover:bg-red-900/10" @click="cancel">
                    <Ban class="h-4 w-4" /> Cancel Booking
                </button>
            </div>
        </div>
    </section>
</template>
