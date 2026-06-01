<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Banknote,
    CreditCard,
    Receipt,
    Smartphone,
    TrendingUp,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency } from '@/lib/format';
import type { Invoice, InvoiceStats } from '@/types';

const props = defineProps<{
    stats: InvoiceStats;
    byMethod: Record<string, number>;
    overdueInvoices: { data: Invoice[] };
    recent: { data: Invoice[] };
}>();

const summary = computed(() => [
    { label: 'Total Invoiced', value: formatCurrency(props.stats.invoiced), icon: Receipt, cls: 'text-blue-500' },
    { label: 'Collected', value: formatCurrency(props.stats.collected), icon: TrendingUp, cls: 'text-emerald-500' },
    { label: 'Outstanding', value: formatCurrency(props.stats.outstanding), icon: Wallet, cls: 'text-amber-500' },
    { label: 'Overdue', value: formatCurrency(props.stats.overdue_amount), icon: AlertTriangle, cls: 'text-red-500' },
]);

const methods = computed(() => [
    { label: 'Cash', value: props.byMethod.cash ?? 0, icon: Banknote, cls: 'text-emerald-500' },
    { label: 'GCash', value: props.byMethod.gcash ?? 0, icon: Smartphone, cls: 'text-sky-500' },
    { label: 'Card', value: props.byMethod.card ?? 0, icon: CreditCard, cls: 'text-violet-500' },
]);

const statusBadge = (s: string) =>
    ({
        unpaid: 'bg-slate-100 text-slate-600 dark:bg-slate-800/40 dark:text-slate-400',
        partial: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        paid: 'badge-available',
        overdue: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        refunded: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
    })[s] ?? 'badge-inactive';
</script>

<template>
    <Head title="Billing Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <div class="flex min-h-full flex-1 flex-col gap-6 p-6">
            <div class="relative overflow-hidden rounded-2xl border border-border bg-[#0f172a] p-6 text-white shadow-card">
                <div class="pointer-events-none absolute -top-16 -right-10 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl" />
                <div class="relative flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Billing &amp; Invoices</p>
                        <h1 class="font-display mt-1.5 text-2xl font-bold tracking-tight">Financial Overview</h1>
                        <p class="mt-1 text-sm text-slate-300">{{ stats.total }} invoices · {{ stats.overdue }} overdue</p>
                    </div>
                    <Link href="/invoices" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400">
                        <Receipt class="h-4 w-4" /> Manage Invoices
                    </Link>
                </div>
            </div>

            <!-- Financial summary -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div v-for="s in summary" :key="s.label" class="rounded-xl border border-border bg-card p-4 shadow-card">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ s.label }}</p>
                        <component :is="s.icon" class="h-4 w-4 shrink-0" :class="s.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-xl font-bold text-foreground">{{ s.value }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Payments by method -->
                <div class="rounded-xl border border-border bg-card p-5 shadow-card">
                    <h2 class="font-display text-sm font-semibold text-foreground">Collected by Method</h2>
                    <div class="mt-4 space-y-4">
                        <div v-for="m in methods" :key="m.label" class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-muted"><component :is="m.icon" class="h-4 w-4" :class="m.cls" /></span>
                            <span class="text-sm text-muted-foreground">{{ m.label }}</span>
                            <span class="font-display ml-auto text-sm font-bold text-foreground">{{ formatCurrency(m.value) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Overdue -->
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h2 class="font-display text-sm font-semibold text-foreground">Overdue Invoices</h2>
                        <AlertTriangle class="h-4 w-4 text-red-500" />
                    </div>
                    <ul class="divide-y divide-border">
                        <li v-for="inv in overdueInvoices.data" :key="inv.id" class="flex items-center gap-3 px-5 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-foreground">{{ inv.customer_name }}</p>
                                <p class="truncate font-mono text-xs text-muted-foreground">{{ inv.invoice_number }} · due {{ inv.due_date }}</p>
                            </div>
                            <span class="text-sm font-medium text-red-600 dark:text-red-400">{{ formatCurrency(inv.balance) }}</span>
                            <Link :href="`/invoices/${inv.id}`" class="text-xs font-medium text-amber-600 hover:text-amber-500">View</Link>
                        </li>
                        <li v-if="overdueInvoices.data.length === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">No overdue invoices 🎉</li>
                    </ul>
                </div>
            </div>

            <!-- Recent -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                <div class="border-b border-border px-5 py-3.5"><h2 class="font-display text-sm font-semibold text-foreground">Recent Invoices</h2></div>
                <ul class="divide-y divide-border">
                    <li v-for="inv in recent.data" :key="inv.id" class="flex items-center gap-3 px-5 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-foreground">{{ inv.customer_name }}</p>
                            <p class="truncate font-mono text-xs text-muted-foreground">{{ inv.invoice_number }}</p>
                        </div>
                        <span class="text-sm font-medium text-foreground">{{ formatCurrency(inv.total) }}</span>
                        <span class="badge shrink-0" :class="statusBadge(inv.status)">{{ inv.status_label }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
