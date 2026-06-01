<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    Activity,
    BarChart3,
    Car,
    CalendarCheck,
    Clock,
    Database,
    Globe,
    Receipt,
    Server,
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

type Log = { id: number; event: string; description: string; level: string; causer: string | null; ago: string | null };

const props = defineProps<{
    stats: { users: number; admins: number; staff: number; maintenance: number; customers: number };
    activity: Log[];
    system: Record<string, string | number>;
    services: { name: string; port: number; url: string; self: boolean; online: boolean }[];
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

const quickLinks = [
    { label: 'Fleet', desc: 'Vehicle inventory & status', href: 'http://localhost:8002', icon: Car, cls: 'text-blue-500' },
    { label: 'Rentals', desc: 'Reservations & returns', href: 'http://localhost:8003', icon: CalendarCheck, cls: 'text-emerald-500' },
    { label: 'Customers', desc: 'CRM & loyalty', href: 'http://localhost:8004', icon: UsersRound, cls: 'text-sky-500' },
    { label: 'Billing', desc: 'Invoices & payments', href: 'http://localhost:8005', icon: Receipt, cls: 'text-amber-500' },
    { label: 'Maintenance', desc: 'Inspections & repairs', href: 'http://localhost:8006', icon: Wrench, cls: 'text-orange-500' },
    { label: 'Analytics', desc: 'Reports & insights', href: 'http://localhost:8007', icon: BarChart3, cls: 'text-violet-500' },
    { label: 'Client Portal', desc: 'Customer-facing site', href: 'http://localhost:8008', icon: Globe, cls: 'text-rose-500' },
];

// Merge live online status (from the controller's health probes) into the
// quick-access cards, and route offline services to the friendly fallback page.
const serviceByName = computed(() => {
    const map: Record<string, { online: boolean; url: string }> = {};
    for (const s of props.services) map[s.name] = { online: s.online, url: s.url };
    return map;
});

const links = computed(() =>
    quickLinks.map((l) => {
        const svc = serviceByName.value[l.label];
        const online = svc?.online ?? true;
        const target = svc?.url ?? l.href;
        return {
            ...l,
            online,
            href: online
                ? target
                : `/service-unavailable?service=${encodeURIComponent(l.label)}&url=${encodeURIComponent(target)}`,
        };
    }),
);

const onlineCount = computed(() => props.services.filter((s) => s.online).length);
const totalServices = computed(() => props.services.length);
const allOnline = computed(() => onlineCount.value === totalServices.value);

const levelDot = (level: string) =>
    ({
        success: 'bg-emerald-500',
        info: 'bg-blue-500',
        warning: 'bg-amber-500',
        error: 'bg-red-500',
    })[level] ?? 'bg-slate-400';

const systemRows = computed(() => [
    { label: 'Environment', value: props.system.environment, icon: Server },
    { label: 'Laravel', value: `v${props.system.laravel_version}`, icon: Server },
    { label: 'PHP', value: `v${props.system.php_version}`, icon: Server },
    { label: 'Database', value: props.system.database, icon: Database },
    { label: 'Timezone', value: props.system.timezone, icon: Clock },
    { label: 'Currency', value: props.system.currency, icon: Receipt },
]);
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex min-h-full flex-1 flex-col gap-6 p-6">
        <!-- Greeting -->
        <div class="relative overflow-hidden rounded-2xl border border-border bg-[#0f172a] p-6 text-white shadow-card motion-safe:animate-[fadeUp_0.5s_ease-out_both]">
            <div class="pointer-events-none absolute -top-16 -right-10 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl" />
            <div class="pointer-events-none absolute -bottom-20 right-32 h-44 w-44 rounded-full bg-amber-400/10 blur-3xl" />
            <div class="relative flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">DriveNow ERP · Operations Hub</p>
                    <h1 class="font-display mt-1.5 text-2xl font-bold tracking-tight">{{ greeting }}, {{ user?.name?.split(' ')[0] }}</h1>
                    <p class="mt-1 max-w-xl text-sm text-slate-300">Manage the entire car-rental operation — fleet, rentals, customers, billing, maintenance and analytics.</p>
                </div>
                <div class="text-right text-xs text-slate-400">
                    <p class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full" :class="allOnline ? 'animate-pulse bg-emerald-400' : 'bg-amber-400'" />
                        {{ allOnline ? 'All systems operational' : `${onlineCount} of ${totalServices} services online` }}
                    </p>
                    <p class="mt-1">{{ system.server_time }}</p>
                </div>
            </div>
        </div>

        <!-- KPI cards (staggered) -->
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div
                v-for="(kpi, i) in kpis"
                :key="kpi.label"
                class="rounded-xl border border-border bg-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:border-amber-300 motion-safe:animate-[fadeUp_0.5s_ease-out_both]"
                :style="{ animationDelay: `${i * 60 + 60}ms` }"
            >
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ kpi.label }}</p>
                    <component :is="kpi.icon" class="h-4 w-4 shrink-0" :class="kpi.iconCls" />
                </div>
                <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ formatNumber(kpi.value) }}</p>
            </div>
        </div>

        <!-- Service quick access -->
        <div>
            <h2 class="font-display text-sm font-semibold tracking-tight text-foreground">Services</h2>
            <p class="mb-3 text-xs text-muted-foreground">Jump into any module of the platform.</p>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <a
                    v-for="(svc, i) in links"
                    :key="svc.label"
                    :href="svc.href"
                    class="group flex items-start gap-3 rounded-xl border border-border bg-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md motion-safe:animate-[fadeUp_0.5s_ease-out_both]"
                    :class="{ 'opacity-75': !svc.online }"
                    :style="{ animationDelay: `${i * 40 + 40}ms` }"
                >
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-muted transition-colors group-hover:bg-amber-50 dark:group-hover:bg-amber-900/20">
                        <component :is="svc.icon" class="h-5 w-5" :class="svc.cls" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-medium text-foreground">{{ svc.label }}</span>
                        <span class="block truncate text-xs text-muted-foreground">{{ svc.desc }}</span>
                    </span>
                    <span class="flex shrink-0 items-center gap-1.5" :title="svc.online ? 'Online' : 'Offline'">
                        <span class="h-2 w-2 rounded-full" :class="svc.online ? 'bg-emerald-500' : 'bg-red-500'" />
                        <span v-if="!svc.online" class="text-[10px] font-semibold text-red-500 uppercase">Offline</span>
                    </span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- System activity -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card lg:col-span-2">
                <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                    <h2 class="font-display flex items-center gap-2 text-sm font-semibold text-foreground">
                        <Activity class="h-4 w-4 text-amber-500" /> System Activity
                    </h2>
                    <span class="text-xs text-muted-foreground">Latest events</span>
                </div>
                <ol class="px-5 py-4">
                    <li v-for="(log, i) in activity" :key="log.id" class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full" :class="levelDot(log.level)" />
                            <span v-if="i < activity.length - 1" class="w-px flex-1 bg-border" />
                        </div>
                        <div class="pb-4">
                            <p class="text-sm text-foreground">{{ log.description }}</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                <span class="font-mono">{{ log.event }}</span>
                                <template v-if="log.causer"> · {{ log.causer }}</template>
                                <template v-if="log.ago"> · {{ log.ago }}</template>
                            </p>
                        </div>
                    </li>
                    <li v-if="activity.length === 0" class="py-8 text-center text-sm text-muted-foreground">No activity recorded yet.</li>
                </ol>
            </div>

            <!-- System overview -->
            <div class="flex flex-col gap-6">
                <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                    <h2 class="font-display flex items-center gap-2 text-sm font-semibold text-foreground">
                        <Server class="h-4 w-4 text-amber-500" /> System Overview
                    </h2>
                    <dl class="mt-4 space-y-2.5">
                        <div v-for="row in systemRows" :key="row.label" class="flex items-center justify-between text-sm">
                            <dt class="flex items-center gap-2 text-muted-foreground"><component :is="row.icon" class="h-3.5 w-3.5" /> {{ row.label }}</dt>
                            <dd class="font-medium text-foreground capitalize">{{ row.value }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h2 class="font-display text-sm font-semibold text-foreground">Services Status</h2>
                        <span class="text-xs font-medium" :class="allOnline ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'">{{ onlineCount }}/{{ totalServices }} online</span>
                    </div>
                    <ul class="divide-y divide-border">
                        <li v-for="svc in props.services" :key="svc.port" class="flex items-center gap-3 px-5 py-2.5">
                            <span class="h-2 w-2 rounded-full" :class="svc.online ? 'bg-emerald-500' : 'bg-red-500'" />
                            <span class="text-sm text-foreground">{{ svc.name }}</span>
                            <span class="ml-auto flex items-center gap-2">
                                <span class="text-[11px] font-medium" :class="svc.online ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'">{{ svc.online ? 'Online' : 'Offline' }}</span>
                                <span class="font-mono text-xs text-muted-foreground">:{{ svc.port }}</span>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</template>
