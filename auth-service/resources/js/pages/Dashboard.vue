<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Car,
    CalendarCheck,
    Globe,
    Receipt,
    ShieldCheck,
    UserCog,
    Users,
    UsersRound,
    Wrench,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { formatNumber } from '@/lib/format';
import type { User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }],
    },
});

const props = defineProps<{
    stats: {
        users: number;
        admins: number;
        staff: number;
        maintenance: number;
        customers: number;
    };
}>();

const page = usePage<{ auth: { user: User } }>();
const user = computed(() => page.props.auth.user);

const greeting = computed(() => {
    const h = new Date().getHours();
    if (h < 12) return 'Good morning';
    if (h < 18) return 'Good afternoon';
    return 'Good evening';
});

const kpis = computed(() => [
    { label: 'Total Users', value: props.stats.users, icon: Users, iconCls: 'text-blue-500' },
    { label: 'Administrators', value: props.stats.admins, icon: ShieldCheck, iconCls: 'text-amber-500' },
    { label: 'Staff', value: props.stats.staff + props.stats.maintenance, icon: UserCog, iconCls: 'text-emerald-500' },
    { label: 'Customers', value: props.stats.customers, icon: UsersRound, iconCls: 'text-sky-500' },
]);

const services = [
    { label: 'Fleet', desc: 'Vehicle inventory & status', href: 'http://localhost:8002', icon: Car, cls: 'text-blue-500' },
    { label: 'Rentals', desc: 'Reservations & returns', href: 'http://localhost:8003', icon: CalendarCheck, cls: 'text-emerald-500' },
    { label: 'Customers', desc: 'CRM & loyalty', href: 'http://localhost:8004', icon: UsersRound, cls: 'text-sky-500' },
    { label: 'Billing', desc: 'Invoices & payments', href: 'http://localhost:8005', icon: Receipt, cls: 'text-amber-500' },
    { label: 'Maintenance', desc: 'Inspections & repairs', href: 'http://localhost:8006', icon: Wrench, cls: 'text-orange-500' },
    { label: 'Analytics', desc: 'Reports & insights', href: 'http://localhost:8007', icon: BarChart3, cls: 'text-violet-500' },
    { label: 'Client Portal', desc: 'Customer-facing site', href: 'http://localhost:8008', icon: Globe, cls: 'text-rose-500' },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <!-- Greeting -->
        <div
            class="relative overflow-hidden rounded-2xl border border-border bg-[#0f172a] p-6 text-white shadow-card"
        >
            <div
                class="pointer-events-none absolute -top-16 -right-10 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -bottom-20 right-32 h-44 w-44 rounded-full bg-amber-400/10 blur-3xl"
            />
            <div class="relative">
                <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">
                    DriveNow ERP · Operations Hub
                </p>
                <h1 class="font-display mt-1.5 text-2xl font-bold tracking-tight">
                    {{ greeting }}, {{ user?.name?.split(' ')[0] }}
                </h1>
                <p class="mt-1 max-w-xl text-sm text-slate-300">
                    Manage the entire car-rental operation from one place — fleet,
                    rentals, customers, billing, maintenance and analytics.
                </p>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div
                v-for="kpi in kpis"
                :key="kpi.label"
                class="rounded-xl border border-border bg-card p-4 shadow-card"
            >
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">
                        {{ kpi.label }}
                    </p>
                    <component :is="kpi.icon" class="h-4 w-4 shrink-0" :class="kpi.iconCls" />
                </div>
                <p class="font-display mt-1.5 text-2xl font-bold text-foreground">
                    {{ formatNumber(kpi.value) }}
                </p>
            </div>
        </div>

        <!-- Service quick access -->
        <div>
            <h2 class="font-display text-sm font-semibold tracking-tight text-foreground">
                Services
            </h2>
            <p class="mb-3 text-xs text-muted-foreground">
                Jump into any module of the platform.
            </p>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <a
                    v-for="svc in services"
                    :key="svc.label"
                    :href="svc.href"
                    class="group flex items-start gap-3 rounded-xl border border-border bg-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md"
                >
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-muted transition-colors group-hover:bg-amber-50 dark:group-hover:bg-amber-900/20"
                    >
                        <component :is="svc.icon" class="h-5 w-5" :class="svc.cls" />
                    </span>
                    <span class="min-w-0">
                        <span class="block font-medium text-foreground">{{ svc.label }}</span>
                        <span class="block truncate text-xs text-muted-foreground">{{
                            svc.desc
                        }}</span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</template>
