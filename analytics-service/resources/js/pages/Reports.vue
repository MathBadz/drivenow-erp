<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CalendarCheck, Download, RotateCcw, TrendingUp, Wrench } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import type { ReportRow } from '@/types';

const props = defineProps<{
    filters: { from: string; to: string };
    rows: ReportRow[];
    totals: { rentals: number; revenue: number; returns: number; maintenance_cost: number };
}>();

const from = ref(props.filters.from);
const to = ref(props.filters.to);

function apply() {
    router.get('/reports', { from: from.value, to: to.value }, { preserveState: true, preserveScroll: true, replace: true });
}

const summary = computed(() => [
    { label: 'Total Rentals', value: formatNumber(props.totals.rentals), icon: CalendarCheck, cls: 'text-blue-500' },
    { label: 'Total Revenue', value: formatCurrency(props.totals.revenue), icon: TrendingUp, cls: 'text-emerald-500' },
    { label: 'Returns', value: formatNumber(props.totals.returns), icon: RotateCcw, cls: 'text-amber-500' },
    { label: 'Maintenance Cost', value: formatCurrency(props.totals.maintenance_cost), icon: Wrench, cls: 'text-orange-500' },
]);

function exportCsv() {
    const header = ['Date', 'Rentals', 'Revenue', 'Returns', 'Maintenance Cost'];
    const lines = props.rows.map((r) => [r.date, r.rentals, r.revenue, r.returns, r.maintenance_cost].join(','));
    const csv = [header.join(','), ...lines].join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `drivenow-report_${props.filters.from}_${props.filters.to}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}
</script>

<template>
    <Head title="Reports" />

    <AppLayout :breadcrumbs="[{ title: 'Reports', href: '/reports' }]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-display text-2xl font-semibold tracking-tight text-foreground">Reports</h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">Operational performance over a custom date range.</p>
                </div>
                <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400" @click="exportCsv">
                    <Download class="h-4 w-4" /> Export CSV
                </button>
            </div>

            <!-- Date range -->
            <div class="flex flex-wrap items-end gap-3 rounded-xl border border-border bg-card p-4 shadow-card">
                <div class="grid gap-1.5">
                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">From</label>
                    <input v-model="from" type="date" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                </div>
                <div class="grid gap-1.5">
                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">To</label>
                    <input v-model="to" type="date" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                </div>
                <button type="button" class="h-9 rounded-lg bg-[#0f172a] px-5 text-sm font-semibold text-white hover:bg-[#1e293b]" @click="apply">Apply</button>
            </div>

            <!-- Summary -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div v-for="s in summary" :key="s.label" class="rounded-xl border border-border bg-card p-4 shadow-card">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ s.label }}</p>
                        <component :is="s.icon" class="h-4 w-4 shrink-0" :class="s.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-xl font-bold text-foreground">{{ s.value }}</p>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                    <h2 class="font-display text-sm font-semibold text-foreground">Daily Breakdown</h2>
                    <span class="text-xs text-muted-foreground">{{ rows.length }} days</span>
                </div>
                <div class="max-h-[480px] overflow-auto">
                    <table class="w-full">
                        <thead class="sticky top-0 bg-muted/60 backdrop-blur">
                            <tr class="border-b border-border">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Date</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Rentals</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Revenue</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Returns</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Maint. Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="row in rows" :key="row.date" class="transition-colors hover:bg-muted/30">
                                <td class="px-5 py-2.5 text-sm text-foreground">{{ formatDate(row.date) }}</td>
                                <td class="px-4 py-2.5 text-right text-sm text-muted-foreground">{{ row.rentals }}</td>
                                <td class="px-4 py-2.5 text-right text-sm font-medium text-foreground">{{ formatCurrency(row.revenue) }}</td>
                                <td class="px-4 py-2.5 text-right text-sm text-muted-foreground">{{ row.returns }}</td>
                                <td class="px-5 py-2.5 text-right text-sm text-muted-foreground">{{ formatCurrency(row.maintenance_cost) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
