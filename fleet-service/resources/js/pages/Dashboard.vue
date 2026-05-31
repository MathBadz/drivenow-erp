<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Car,
    CheckCircle2,
    CalendarClock,
    KeyRound,
    LayoutGrid,
    Plus,
    Wrench,
    XCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/format';
import type { Vehicle, VehicleStats } from '@/types';

const props = defineProps<{
    stats: VehicleStats;
    byCategory: Record<string, number>;
    recent: { data: Vehicle[] };
}>();

const statusCards = computed(() => [
    { key: '', label: 'Total Fleet', count: props.stats.total, icon: LayoutGrid, cls: 'text-blue-500' },
    { key: 'available', label: 'Available', count: props.stats.available, icon: CheckCircle2, cls: 'text-emerald-500' },
    { key: 'reserved', label: 'Reserved', count: props.stats.reserved, icon: CalendarClock, cls: 'text-blue-500' },
    { key: 'rented', label: 'Rented', count: props.stats.rented, icon: KeyRound, cls: 'text-amber-500' },
    { key: 'maintenance', label: 'Maintenance', count: props.stats.maintenance, icon: Wrench, cls: 'text-orange-500' },
    { key: 'inactive', label: 'Inactive', count: props.stats.inactive, icon: XCircle, cls: 'text-slate-400' },
]);

const categories = [
    { key: 'sedan', label: 'Sedan' },
    { key: 'hatchback', label: 'Hatchback' },
    { key: 'suv', label: 'SUV' },
    { key: 'van', label: 'Van' },
    { key: 'pickup', label: 'Pickup' },
];

const maxCategory = computed(() =>
    Math.max(1, ...categories.map((c) => props.byCategory[c.key] ?? 0)),
);

const utilization = computed(() => {
    const inUse = props.stats.rented + props.stats.reserved;
    return props.stats.total ? Math.round((inUse / props.stats.total) * 100) : 0;
});

const badgeClass = (status: string) =>
    ({
        available: 'badge-available',
        reserved: 'badge-reserved',
        rented: 'badge-rented',
        maintenance: 'badge-maintenance',
        inactive: 'badge-inactive',
    })[status] ?? 'badge-inactive';
</script>

<template>
    <Head title="Fleet Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Hero -->
            <div class="relative overflow-hidden rounded-2xl border border-border bg-[#0f172a] p-6 text-white shadow-card">
                <div class="pointer-events-none absolute -top-16 -right-10 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl" />
                <div class="relative flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">
                            Fleet Management
                        </p>
                        <h1 class="font-display mt-1.5 text-2xl font-bold tracking-tight">
                            Fleet Overview
                        </h1>
                        <p class="mt-1 text-sm text-slate-300">
                            {{ stats.total }} vehicles · {{ utilization }}% currently in use
                        </p>
                    </div>
                    <Link
                        href="/vehicles"
                        class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400"
                    >
                        <Plus class="h-4 w-4" />
                        Manage Vehicles
                    </Link>
                </div>
            </div>

            <!-- Status cards (clickable → filtered vehicle list) -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <Link
                    v-for="card in statusCards"
                    :key="card.key"
                    :href="card.key ? `/vehicles?status=${card.key}` : '/vehicles'"
                    class="rounded-xl border border-border bg-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:border-amber-300"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">
                            {{ card.label }}
                        </p>
                        <component :is="card.icon" class="h-4 w-4 shrink-0" :class="card.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ card.count }}</p>
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Category breakdown -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-card lg:col-span-1">
                    <h2 class="font-display text-sm font-semibold text-foreground">Fleet by Category</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="cat in categories" :key="cat.key">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">{{ cat.label }}</span>
                                <span class="font-medium text-foreground">{{ byCategory[cat.key] ?? 0 }}</span>
                            </div>
                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full rounded-full bg-amber-500 transition-all"
                                    :style="{ width: `${((byCategory[cat.key] ?? 0) / maxCategory) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recently added -->
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h2 class="font-display text-sm font-semibold text-foreground">Recently Added</h2>
                        <Link href="/vehicles" class="text-xs font-medium text-amber-600 hover:text-amber-500">
                            View all
                        </Link>
                    </div>
                    <ul class="divide-y divide-border">
                        <li
                            v-for="v in recent.data"
                            :key="v.id"
                            class="flex items-center gap-3 px-5 py-3.5"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted">
                                <Car class="h-4 w-4 text-muted-foreground" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-foreground">{{ v.name }}</p>
                                <p class="truncate text-xs text-muted-foreground">
                                    {{ v.plate_number }} · {{ v.category_label }} · {{ formatCurrency(v.daily_rate) }}/day
                                </p>
                            </div>
                            <span class="badge shrink-0" :class="badgeClass(v.status)">{{ v.status_label }}</span>
                            <span class="hidden w-24 shrink-0 text-right text-xs text-muted-foreground sm:block">
                                {{ formatDate(v.created_at) }}
                            </span>
                        </li>
                        <li v-if="recent.data.length === 0" class="px-5 py-12 text-center text-sm text-muted-foreground">
                            No vehicles yet.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
