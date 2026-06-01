<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Ban,
    CalendarClock,
    Car,
    CheckCircle2,
    CircleDot,
    Gauge,
    PlayCircle,
    Wrench,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import type { MaintenanceRecord } from '@/types';

const props = defineProps<{ record: { data: MaintenanceRecord } }>();
const r = computed(() => props.record.data);

const isCancelled = computed(() => r.value.status === 'cancelled');

const statusBadge = computed(
    () => ({ scheduled: 'badge-reserved', in_progress: 'badge-maintenance', completed: 'badge-available', cancelled: 'badge-inactive' })[r.value.status] ?? 'badge-inactive',
);
const severityBadge = computed(
    () => ({
        low: 'bg-slate-100 text-slate-600 dark:bg-slate-800/40 dark:text-slate-400',
        medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        high: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[r.value.severity] ?? 'bg-slate-100 text-slate-600',
);

const steps = computed(() => {
    const order = ['scheduled', 'in_progress', 'completed'];
    const current = order.indexOf(r.value.status);
    return [
        { key: 'scheduled', label: 'Scheduled', at: r.value.scheduled_date, icon: CalendarClock },
        { key: 'in_progress', label: 'In Progress', at: null, icon: CircleDot },
        { key: 'completed', label: 'Completed', at: r.value.completed_date, icon: CheckCircle2 },
    ].map((s, i) => ({ ...s, done: !isCancelled.value && i <= current }));
});

function action(verb: string) {
    router.post(`/maintenance/${r.value.id}/${verb}`, {}, { preserveScroll: true });
}

const details = computed(() => [
    { label: 'Vehicle', value: r.value.vehicle_name, icon: Car },
    { label: 'Plate', value: r.value.vehicle_plate, icon: Car },
    { label: 'Type', value: r.value.type_label, icon: Wrench },
    { label: 'Cost', value: formatCurrency(r.value.cost), icon: Gauge },
    { label: 'Odometer', value: r.value.odometer ? `${formatNumber(r.value.odometer)} km` : '—', icon: Gauge },
    { label: 'Scheduled', value: formatDate(r.value.scheduled_date), icon: CalendarClock },
]);
</script>

<template>
    <Head :title="r.reference" />

    <AppLayout :breadcrumbs="[{ title: 'Maintenance', href: '/maintenance' }, { title: r.reference, href: `/maintenance/${r.id}` }]">
        <div class="mx-auto flex min-h-full w-full max-w-4xl flex-1 flex-col gap-6 p-6">
            <Link href="/maintenance" class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
                <ArrowLeft class="h-4 w-4" /> Back to records
            </Link>

            <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sm text-muted-foreground">{{ r.reference }}</span>
                            <span class="badge" :class="statusBadge">{{ r.status_label }}</span>
                            <span class="badge" :class="severityBadge">{{ r.severity_label }}</span>
                        </div>
                        <h1 class="font-display mt-1 text-2xl font-bold tracking-tight text-foreground">{{ r.title }}</h1>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ r.vehicle_name }} · {{ r.vehicle_plate }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button v-if="r.status === 'scheduled'" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 hover:bg-amber-400" @click="action('start')">
                            <PlayCircle class="h-4 w-4" /> Start Work
                        </button>
                        <button v-if="r.status === 'in_progress'" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-emerald-500 px-4 text-sm font-semibold text-white hover:bg-emerald-400" @click="action('complete')">
                            <CheckCircle2 class="h-4 w-4" /> Mark Complete
                        </button>
                        <button v-if="!['completed', 'cancelled'].includes(r.status)" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800/50 dark:hover:bg-red-900/10" @click="action('cancel')">
                            <Ban class="h-4 w-4" /> Cancel
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Progress</h2>
                    <ol class="mt-4 space-y-1">
                        <li v-for="(step, i) in steps" :key="step.key" class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 transition-colors" :class="step.done ? 'border-amber-500 bg-amber-500 text-amber-950' : 'border-border bg-card text-muted-foreground'">
                                    <component :is="step.icon" class="h-4 w-4" />
                                </span>
                                <span v-if="i < steps.length - 1" class="h-8 w-0.5" :class="step.done ? 'bg-amber-500' : 'bg-border'" />
                            </div>
                            <div class="pt-1 pb-4">
                                <p class="text-sm font-medium" :class="step.done ? 'text-foreground' : 'text-muted-foreground'">{{ step.label }}</p>
                                <p v-if="step.at" class="text-xs text-muted-foreground">{{ formatDate(step.at) }}</p>
                            </div>
                        </li>
                    </ol>
                    <div v-if="isCancelled" class="mt-2 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800/50 dark:bg-red-900/10">
                        <Ban class="h-4 w-4 shrink-0 text-red-600 dark:text-red-400" />
                        <p class="text-sm text-red-700 dark:text-red-300">This record was cancelled.</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Details</h2>
                    <dl class="mt-4 grid grid-cols-2 gap-4">
                        <div v-for="d in details" :key="d.label">
                            <dt class="flex items-center gap-1.5 text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">
                                <component :is="d.icon" class="h-3.5 w-3.5" /> {{ d.label }}
                            </dt>
                            <dd class="mt-1 text-sm font-medium text-foreground">{{ d.value }}</dd>
                        </div>
                    </dl>
                    <div v-if="r.description" class="mt-5 border-t border-border pt-5">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Description</p>
                        <p class="mt-1 text-sm whitespace-pre-line text-muted-foreground">{{ r.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
