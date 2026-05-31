<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Banknote,
    CalendarCheck,
    FileText,
    Gauge,
    Repeat,
    TrendingUp,
    UsersRound,
    Wrench,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatNumber } from '@/lib/format';
import type {
    Kpis,
    MaintenanceMonthly,
    MonthlyRevenue,
    PaymentMix,
    Retention,
    TopVehicle,
    Utilization,
} from '@/types';

const props = defineProps<{
    kpis: Kpis;
    monthlyRevenue: MonthlyRevenue[];
    maintenanceMonthly: MaintenanceMonthly[];
    topVehicles: TopVehicle[];
    utilization: Utilization;
    retention: Retention;
    paymentMix: PaymentMix;
}>();

const kpiCards = computed(() => [
    { label: 'Total Revenue', value: formatCurrency(props.kpis.revenue), icon: Banknote, cls: 'text-emerald-500' },
    { label: 'Total Rentals', value: formatNumber(props.kpis.rentals), icon: CalendarCheck, cls: 'text-blue-500' },
    { label: 'Fleet Utilization', value: `${props.kpis.utilization}%`, icon: Gauge, cls: 'text-amber-500' },
    { label: 'Active Customers', value: formatNumber(props.kpis.active_customers), icon: UsersRound, cls: 'text-sky-500' },
    { label: 'Avg. Rental Value', value: formatCurrency(props.kpis.avg_rental_value), icon: TrendingUp, cls: 'text-violet-500' },
    { label: 'Maintenance Cost', value: formatCurrency(props.kpis.maintenance_cost), icon: Wrench, cls: 'text-orange-500' },
]);

const maxRevenue = computed(() => Math.max(1, ...props.monthlyRevenue.map((m) => m.revenue)));
const maxMaint = computed(() => Math.max(1, ...props.maintenanceMonthly.map((m) => m.cost)));
const maxVehicleRentals = computed(() => Math.max(1, ...props.topVehicles.map((v) => v.rentals)));

const utilizationRing = computed(
    () => `conic-gradient(#f59e0b ${props.utilization.percent * 3.6}deg, var(--muted) 0deg)`,
);

// Revenue line as an SVG polyline (0..100 viewbox).
const revenuePoints = computed(() => {
    const data = props.monthlyRevenue;
    const max = maxRevenue.value;
    return data
        .map((m, i) => {
            const x = (i / Math.max(1, data.length - 1)) * 100;
            const y = 100 - (m.revenue / max) * 92 - 4;
            return `${x.toFixed(2)},${y.toFixed(2)}`;
        })
        .join(' ');
});

const paymentSegments = computed(() => [
    { label: 'Cash', value: props.paymentMix.cash, cls: 'bg-emerald-500' },
    { label: 'GCash', value: props.paymentMix.gcash, cls: 'bg-sky-500' },
    { label: 'Card', value: props.paymentMix.card, cls: 'bg-violet-500' },
]);
</script>

<template>
    <Head title="Analytics Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Hero -->
            <div class="relative overflow-hidden rounded-2xl border border-border bg-[#0f172a] p-6 text-white shadow-card">
                <div class="pointer-events-none absolute -top-16 -right-10 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl" />
                <div class="relative flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Analytics &amp; Business Intelligence</p>
                        <h1 class="font-display mt-1.5 text-2xl font-bold tracking-tight">Performance Overview</h1>
                        <p class="mt-1 text-sm text-slate-300">Trailing 12 months across the entire operation</p>
                    </div>
                    <Link href="/reports" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400">
                        <FileText class="h-4 w-4" /> View Reports
                    </Link>
                </div>
            </div>

            <!-- KPI cards -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
                <div v-for="k in kpiCards" :key="k.label" class="rounded-xl border border-border bg-card p-4 shadow-card">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ k.label }}</p>
                        <component :is="k.icon" class="h-4 w-4 shrink-0" :class="k.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-xl font-bold text-foreground">{{ k.value }}</p>
                </div>
            </div>

            <!-- Revenue trend -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-sm font-semibold text-foreground">Monthly Revenue</h2>
                    <span class="text-xs text-muted-foreground">Last 12 months</span>
                </div>
                <div class="relative mt-5 h-48">
                    <svg class="absolute inset-0 h-full w-full overflow-visible" viewBox="0 0 100 100" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="rev" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#f59e0b" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <polygon :points="`0,100 ${revenuePoints} 100,100`" fill="url(#rev)" />
                        <polyline :points="revenuePoints" fill="none" stroke="#f59e0b" stroke-width="1.5" vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="mt-2 flex justify-between text-[10px] text-muted-foreground">
                    <span v-for="m in monthlyRevenue" :key="m.month">{{ m.month }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Utilization donut -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Fleet Utilization</h2>
                    <div class="mt-4 flex items-center gap-5">
                        <div class="relative h-28 w-28 shrink-0 rounded-full" :style="{ background: utilizationRing }">
                            <div class="absolute inset-2 flex flex-col items-center justify-center rounded-full bg-card">
                                <span class="font-display text-2xl font-bold text-foreground">{{ utilization.percent }}%</span>
                                <span class="text-[10px] text-muted-foreground uppercase">in use</span>
                            </div>
                        </div>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-500" /> Rented <span class="ml-auto font-medium text-foreground">{{ utilization.rented }}</span></li>
                            <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500" /> Available <span class="ml-auto font-medium text-foreground">{{ utilization.available }}</span></li>
                            <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-orange-500" /> Maintenance <span class="ml-auto font-medium text-foreground">{{ utilization.maintenance }}</span></li>
                        </ul>
                    </div>
                </div>

                <!-- Top vehicles -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-card lg:col-span-2">
                    <h2 class="font-display text-sm font-semibold text-foreground">Most Rented Vehicles</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="v in topVehicles" :key="v.plate">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-foreground">{{ v.name }}</span>
                                <span class="text-muted-foreground">{{ v.rentals }} rentals · {{ formatCurrency(v.revenue) }}</span>
                            </div>
                            <div class="mt-1 h-2.5 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-amber-500 transition-all" :style="{ width: `${(v.rentals / maxVehicleRentals) * 100}%` }" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Retention -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                    <div class="flex items-center justify-between">
                        <h2 class="font-display text-sm font-semibold text-foreground">Customer Retention</h2>
                        <Repeat class="h-4 w-4 text-emerald-500" />
                    </div>
                    <p class="font-display mt-3 text-3xl font-bold text-foreground">{{ retention.percent }}%</p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ retention.returning }} returning · {{ retention.new }} new</p>
                    <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-emerald-500" :style="{ width: `${retention.percent}%` }" />
                    </div>
                </div>

                <!-- Payment mix -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Payment Mix</h2>
                    <div class="mt-4 flex h-3 overflow-hidden rounded-full">
                        <div v-for="s in paymentSegments" :key="s.label" class="h-full" :class="s.cls" :style="{ width: `${s.value}%` }" />
                    </div>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li v-for="s in paymentSegments" :key="s.label" class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full" :class="s.cls" /> {{ s.label }}
                            <span class="ml-auto font-medium text-foreground">{{ s.value }}%</span>
                        </li>
                    </ul>
                </div>

                <!-- Maintenance cost bars -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Maintenance Cost</h2>
                    <div class="mt-5 flex h-32 items-end gap-1">
                        <div v-for="m in maintenanceMonthly" :key="m.month" class="flex flex-1 flex-col items-center gap-1">
                            <div class="w-full rounded-t bg-orange-400/80 transition-all hover:bg-orange-400" :style="{ height: `${(m.cost / maxMaint) * 100}%` }" />
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between text-[10px] text-muted-foreground">
                        <span>{{ maintenanceMonthly[0]?.month }}</span>
                        <span>{{ maintenanceMonthly[maintenanceMonthly.length - 1]?.month }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
