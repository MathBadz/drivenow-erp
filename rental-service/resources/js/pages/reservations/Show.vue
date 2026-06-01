<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Ban,
    Calendar,
    Car,
    CheckCircle2,
    Clock,
    KeyRound,
    RotateCcw,
    User,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/format';
import type { Rental } from '@/types';

const props = defineProps<{ rental: { data: Rental } }>();

const r = computed(() => props.rental.data);

const badgeClass = computed(
    () =>
        ({
            pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            approved: 'badge-reserved',
            active: 'badge-available',
            completed: 'badge-inactive',
            cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        })[r.value.status] ?? 'badge-inactive',
);

const isCancelled = computed(() => r.value.status === 'cancelled');

const steps = computed(() => {
    const order = ['pending', 'approved', 'active', 'completed'];
    const currentIndex = order.indexOf(r.value.status);
    return [
        { key: 'pending', label: 'Reservation created', at: r.value.created_at, icon: Clock },
        { key: 'approved', label: 'Approved', at: r.value.approved_at, icon: CheckCircle2 },
        { key: 'active', label: 'Vehicle released', at: r.value.released_at, icon: KeyRound },
        { key: 'completed', label: 'Returned', at: r.value.returned_at, icon: RotateCcw },
    ].map((step, i) => ({
        ...step,
        done: !isCancelled.value && i <= currentIndex,
        current: !isCancelled.value && i === currentIndex,
    }));
});

function action(verb: string) {
    router.post(`/reservations/${r.value.id}/${verb}`, {}, { preserveScroll: true });
}

/* Extend modal */
const showExtend = ref(false);
const extendForm = useForm({ return_date: r.value.return_date });

function submitExtend() {
    extendForm.post(`/reservations/${r.value.id}/extend`, {
        preserveScroll: true,
        onSuccess: () => (showExtend.value = false),
    });
}

const details = computed(() => [
    { label: 'Vehicle', value: r.value.vehicle_name, icon: Car },
    { label: 'Plate', value: r.value.vehicle_plate, icon: Car },
    { label: 'Pickup', value: formatDate(r.value.pickup_date), icon: Calendar },
    { label: 'Return', value: formatDate(r.value.return_date), icon: Calendar },
    { label: 'Customer', value: r.value.customer_name, icon: User },
    { label: 'Branch', value: r.value.pickup_branch, icon: Car },
]);
</script>

<template>
    <Head :title="r.reference" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Reservations', href: '/reservations' },
            { title: r.reference, href: `/reservations/${r.id}` },
        ]"
    >
        <div class="mx-auto flex min-h-full w-full max-w-4xl flex-1 flex-col gap-6 p-6">
            <Link href="/reservations" class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to reservations
            </Link>

            <!-- Header -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm font-medium text-muted-foreground">{{ r.reference }}</span>
                            <span class="badge" :class="badgeClass">{{ r.status_label }}</span>
                        </div>
                        <h1 class="font-display mt-1 text-2xl font-bold tracking-tight text-foreground">{{ r.customer_name }}</h1>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ r.customer_email }} · {{ r.customer_phone }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-display text-2xl font-bold text-foreground">{{ formatCurrency(r.total) }}</p>
                        <p class="text-xs text-muted-foreground">{{ r.days }} day(s) × {{ formatCurrency(r.daily_rate) }}</p>
                    </div>
                </div>

                <!-- Workflow actions -->
                <div class="mt-5 flex flex-wrap gap-2 border-t border-border pt-5">
                    <button v-if="r.status === 'pending'" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 hover:bg-amber-400" @click="action('approve')">
                        <CheckCircle2 class="h-4 w-4" /> Approve
                    </button>
                    <button v-if="r.status === 'approved'" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 hover:bg-amber-400" @click="action('release')">
                        <KeyRound class="h-4 w-4" /> Release Vehicle
                    </button>
                    <button v-if="r.status === 'active'" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-emerald-500 px-4 text-sm font-semibold text-white hover:bg-emerald-400" @click="action('return')">
                        <RotateCcw class="h-4 w-4" /> Process Return
                    </button>
                    <button v-if="['approved', 'active'].includes(r.status)" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-4 text-sm font-medium text-foreground hover:bg-muted" @click="showExtend = true">
                        <Calendar class="h-4 w-4" /> Extend
                    </button>
                    <button v-if="!['completed', 'cancelled'].includes(r.status)" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800/50 dark:hover:bg-red-900/10" @click="action('cancel')">
                        <Ban class="h-4 w-4" /> Cancel
                    </button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
                <!-- Timeline -->
                <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Rental Timeline</h2>
                    <ol class="mt-4 space-y-1">
                        <li v-for="(step, i) in steps" :key="step.key" class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <span
                                    class="flex h-8 w-8 items-center justify-center rounded-full border-2 transition-colors"
                                    :class="step.done ? 'border-amber-500 bg-amber-500 text-amber-950' : 'border-border bg-card text-muted-foreground'"
                                >
                                    <component :is="step.icon" class="h-4 w-4" />
                                </span>
                                <span v-if="i < steps.length - 1" class="h-8 w-0.5" :class="step.done ? 'bg-amber-500' : 'bg-border'" />
                            </div>
                            <div class="pb-4 pt-1">
                                <p class="text-sm font-medium" :class="step.done ? 'text-foreground' : 'text-muted-foreground'">{{ step.label }}</p>
                                <p v-if="step.at" class="text-xs text-muted-foreground">{{ formatDate(step.at) }}</p>
                            </div>
                        </li>
                    </ol>
                    <div v-if="isCancelled" class="mt-2 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800/50 dark:bg-red-900/10">
                        <Ban class="h-4 w-4 shrink-0 text-red-600 dark:text-red-400" />
                        <p class="text-sm text-red-700 dark:text-red-300">Cancelled on {{ formatDate(r.cancelled_at) }}</p>
                    </div>
                </div>

                <!-- Details -->
                <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Reservation Details</h2>
                    <dl class="mt-4 grid grid-cols-2 gap-4">
                        <div v-for="d in details" :key="d.label">
                            <dt class="flex items-center gap-1.5 text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">
                                <component :is="d.icon" class="h-3.5 w-3.5" /> {{ d.label }}
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-foreground">{{ d.value }}</dd>
                        </div>
                    </dl>
                    <div class="mt-5 space-y-2 border-t border-border pt-5 text-sm">
                        <div class="flex justify-between"><span class="text-muted-foreground">Subtotal</span><span class="text-foreground">{{ formatCurrency(r.subtotal) }}</span></div>
                        <div class="flex justify-between font-semibold"><span class="text-foreground">Total</span><span class="font-display text-foreground">{{ formatCurrency(r.total) }}</span></div>
                    </div>
                    <div v-if="r.notes" class="mt-5 border-t border-border pt-5">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Notes</p>
                        <p class="mt-1 text-sm whitespace-pre-line text-muted-foreground">{{ r.notes }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Extend modal -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition duration-150" leave-to-class="opacity-0" leave-active-class="transition duration-150">
                <div v-if="showExtend" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showExtend = false" />
                    <div class="relative z-10 mx-4 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-2xl">
                        <div class="flex items-center justify-between">
                            <h2 class="font-display font-semibold text-foreground">Extend Rental</h2>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted" @click="showExtend = false">
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                        <form class="mt-4" @submit.prevent="submitExtend">
                            <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">New Return Date</label>
                            <input v-model="extendForm.return_date" type="date" class="mt-1.5 h-9 w-full rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': extendForm.errors.return_date }" />
                            <p v-if="extendForm.errors.return_date" class="mt-1 text-[11px] text-red-500">{{ extendForm.errors.return_date }}</p>
                            <div class="mt-5 flex items-center justify-end gap-3">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="showExtend = false">Cancel</button>
                                <button type="submit" :disabled="extendForm.processing" class="h-9 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
