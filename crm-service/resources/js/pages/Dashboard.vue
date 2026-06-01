<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Ban,
    Crown,
    LayoutGrid,
    UserCheck,
    UserMinus,
    UsersRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatNumber } from '@/lib/format';
import type { Customer, CustomerStats } from '@/types';

const props = defineProps<{
    stats: CustomerStats;
    byTier: Record<string, number>;
    loyaltyLeaders: { data: Customer[] };
    recent: { data: Customer[] };
}>();

const cards = computed(() => [
    { key: '', label: 'Total', count: props.stats.total, icon: LayoutGrid, cls: 'text-blue-500' },
    { key: 'active', label: 'Active', count: props.stats.active, icon: UserCheck, cls: 'text-emerald-500' },
    { key: 'inactive', label: 'Inactive', count: props.stats.inactive, icon: UserMinus, cls: 'text-slate-400' },
    { key: 'blacklisted', label: 'Blacklisted', count: props.stats.blacklisted, icon: Ban, cls: 'text-red-500' },
]);

const tiers = [
    { key: 'regular', label: 'Regular', cls: 'bg-slate-400' },
    { key: 'silver', label: 'Silver', cls: 'bg-slate-300' },
    { key: 'gold', label: 'Gold', cls: 'bg-amber-400' },
    { key: 'platinum', label: 'Platinum', cls: 'bg-sky-400' },
];
const maxTier = computed(() => Math.max(1, ...tiers.map((t) => props.byTier[t.key] ?? 0)));

const tierBadge = (tier: string) =>
    ({
        regular: 'bg-slate-100 text-slate-600 dark:bg-slate-800/40 dark:text-slate-400',
        silver: 'bg-slate-200 text-slate-700 dark:bg-slate-700/40 dark:text-slate-300',
        gold: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        platinum: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
    })[tier] ?? 'bg-slate-100 text-slate-600';
</script>

<template>
    <Head title="CRM Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <div class="flex min-h-full flex-1 flex-col gap-6 p-6">
            <div class="relative overflow-hidden rounded-2xl border border-border bg-[#0f172a] p-6 text-white shadow-card">
                <div class="pointer-events-none absolute -top-16 -right-10 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl" />
                <div class="relative flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Customer Relations</p>
                        <h1 class="font-display mt-1.5 text-2xl font-bold tracking-tight">Customer Overview</h1>
                        <p class="mt-1 text-sm text-slate-300">{{ stats.total }} customers · {{ stats.blacklisted }} blacklisted</p>
                    </div>
                    <Link href="/customers" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400">
                        <UsersRound class="h-4 w-4" /> Manage Customers
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <Link v-for="card in cards" :key="card.key" :href="card.key ? `/customers?status=${card.key}` : '/customers'" class="rounded-xl border border-border bg-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:border-amber-300">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ card.label }}</p>
                        <component :is="card.icon" class="h-4 w-4 shrink-0" :class="card.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ formatNumber(card.count) }}</p>
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Loyalty Tiers</h2>
                    <div class="mt-4 space-y-3">
                        <div v-for="t in tiers" :key="t.key">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-muted-foreground">{{ t.label }}</span>
                                <span class="font-medium text-foreground">{{ byTier[t.key] ?? 0 }}</span>
                            </div>
                            <div class="mt-1 h-2 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full transition-all" :class="t.cls" :style="{ width: `${((byTier[t.key] ?? 0) / maxTier) * 100}%` }" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h2 class="font-display text-sm font-semibold text-foreground">Loyalty Leaders</h2>
                        <Crown class="h-4 w-4 text-amber-500" />
                    </div>
                    <ul class="divide-y divide-border">
                        <li v-for="(c, i) in loyaltyLeaders.data" :key="c.id" class="flex items-center gap-3 px-5 py-3">
                            <span class="font-display w-5 text-center text-sm font-bold text-muted-foreground">{{ i + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-foreground">{{ c.name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ formatNumber(c.loyalty_points) }} pts · {{ formatCurrency(c.total_spent) }} spent</p>
                            </div>
                            <span class="badge shrink-0" :class="tierBadge(c.tier)">{{ c.tier_label }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
