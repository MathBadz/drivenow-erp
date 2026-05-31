<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarClock,
    CheckCircle2,
    CircleDot,
    DollarSign,
    LayoutGrid,
    Wrench,
    XCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/format';
import type { MaintenanceRecord, MaintenanceStats } from '@/types';

const props = defineProps<{
    stats: MaintenanceStats;
    byType: Record<string, number>;
    upcoming: { data: MaintenanceRecord[] };
}>();

const statusCards = computed(() => [
    { key: '', label: 'Total', count: props.stats.total, icon: LayoutGrid, cls: 'text-blue-500' },
    { key: 'scheduled', label: 'Scheduled', count: props.stats.scheduled, icon: CalendarClock, cls: 'text-blue-500' },
    { key: 'in_progress', label: 'In Progress', count: props.stats.in_progress, icon: CircleDot, cls: 'text-orange-500' },
    { key: 'completed', label: 'Completed', count: props.stats.completed, icon: CheckCircle2, cls: 'text-emerald-500' },
    { key: 'cancelled', label: 'Cancelled', count: props.stats.cancelled, icon: XCircle, cls: 'text-slate-400' },
]);

const types = [
    { key: 'inspection', label: 'Inspections' },
    { key: 'repair', label: 'Repairs' },
    { key: 'scheduled', label: 'Scheduled Service' },
    { key: 'damage', label: 'Damage Reports' },
];
const maxType = computed(() => Math.max(1, ...types.map((t) => props.byType[t.key] ?? 0)));

const statusBadge = (s: string) =>
    ({
        scheduled: 'badge-reserved',
        in_progress: 'badge-maintenance',
        completed: 'badge-available',
        cancelled: 'badge-inactive',
    })[s] ?? 'badge-inactive';
</script>

<template>
    <Head title="Maintenance Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="relative overflow-hidden rounded-2xl border border-border bg-[#0f172a] p-6 text-white shadow-card lg:col-span-2">
                    <div class="pointer-events-none absolute -top-16 -right-10 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl" />
                    <div class="relative flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Maintenance</p>
                            <h1 class="font-display mt-1.5 text-2xl font-bold tracking-tight">Maintenance Overview</h1>
                            <p class="mt-1 text-sm text-slate-300">{{ stats.downtime }} vehicle(s) currently in the workshop</p>
                        </div>
                        <Link href="/maintenance" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400">
                            <Wrench class="h-4 w-4" /> Manage Records
                        </Link>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Total Maintenance Cost</p>
                        <DollarSign class="h-4 w-4 text-emerald-500" />
                    </div>
                    <p class="font-display mt-2 text-3xl font-bold text-foreground">{{ formatCurrency(stats.total_cost) }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ stats.completed }} completed jobs</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <Link v-for="card in statusCards" :key="card.key" :href="card.key ? `/maintenance?status=${card.key}` : '/maintenance'" class="rounded-xl border border-border bg-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:border-amber-300">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ card.label }}</p>
                        <component :is="card.icon" class="h-4 w-4 shrink-0" :class="card.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ card.count }}</p>
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Records by Type</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="t in types" :key="t.key">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">{{ t.label }}</span>
                                <span class="font-medium text-foreground">{{ byType[t.key] ?? 0 }}</span>
                            </div>
                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-amber-500 transition-all" :style="{ width: `${((byType[t.key] ?? 0) / maxType) * 100}%` }" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h2 class="font-display text-sm font-semibold text-foreground">Maintenance Schedule</h2>
                        <CalendarClock class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <ul class="divide-y divide-border">
                        <li v-for="r in upcoming.data" :key="r.id" class="flex items-center gap-3 px-5 py-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted"><Wrench class="h-4 w-4 text-muted-foreground" /></span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-foreground">{{ r.title }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ r.vehicle_name }} · {{ r.scheduled_date }}</p>
                            </div>
                            <span class="badge shrink-0" :class="statusBadge(r.status)">{{ r.status_label }}</span>
                        </li>
                        <li v-if="upcoming.data.length === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">Nothing scheduled.</li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
