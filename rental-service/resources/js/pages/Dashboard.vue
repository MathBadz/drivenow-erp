<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CalendarCheck,
    CalendarClock,
    CheckCircle2,
    CircleDot,
    Clock,
    DollarSign,
    LayoutGrid,
    Plus,
    XCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/format';
import type { Rental, RentalStats } from '@/types';

const props = defineProps<{
    stats: RentalStats;
    upcoming: { data: Rental[] };
    pendingApprovals: { data: Rental[] };
}>();

const statusCards = computed(() => [
    { key: '', label: 'Total', count: props.stats.total, icon: LayoutGrid, cls: 'text-blue-500' },
    { key: 'pending', label: 'Pending', count: props.stats.pending, icon: Clock, cls: 'text-amber-500' },
    { key: 'approved', label: 'Approved', count: props.stats.approved, icon: CalendarClock, cls: 'text-blue-500' },
    { key: 'active', label: 'Active', count: props.stats.active, icon: CircleDot, cls: 'text-emerald-500' },
    { key: 'completed', label: 'Completed', count: props.stats.completed, icon: CheckCircle2, cls: 'text-slate-400' },
    { key: 'cancelled', label: 'Cancelled', count: props.stats.cancelled, icon: XCircle, cls: 'text-red-500' },
]);

const badgeClass = (status: string) =>
    ({
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        approved: 'badge-reserved',
        active: 'badge-available',
        completed: 'badge-inactive',
        cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[status] ?? 'badge-inactive';
</script>

<template>
    <Head title="Rentals Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <div class="flex min-h-full flex-1 flex-col gap-6 p-6">
            <!-- Hero + revenue -->
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="relative overflow-hidden rounded-2xl border border-border bg-[#0f172a] p-6 text-white shadow-card lg:col-span-2">
                    <div class="pointer-events-none absolute -top-16 -right-10 h-48 w-48 rounded-full bg-amber-500/20 blur-3xl" />
                    <div class="relative flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Rental Management</p>
                            <h1 class="font-display mt-1.5 text-2xl font-bold tracking-tight">Reservations Overview</h1>
                            <p class="mt-1 text-sm text-slate-300">
                                {{ stats.active }} active · {{ stats.pending }} awaiting approval
                            </p>
                        </div>
                        <Link href="/reservations" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400">
                            <Plus class="h-4 w-4" />
                            New Reservation
                        </Link>
                    </div>
                </div>
                <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Revenue (Active + Completed)</p>
                        <DollarSign class="h-4 w-4 text-emerald-500" />
                    </div>
                    <p class="font-display mt-2 text-3xl font-bold text-foreground">{{ formatCurrency(stats.revenue) }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ stats.completed }} completed rentals</p>
                </div>
            </div>

            <!-- Status cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <Link
                    v-for="card in statusCards"
                    :key="card.key"
                    :href="card.key ? `/reservations?status=${card.key}` : '/reservations'"
                    class="rounded-xl border border-border bg-card p-4 shadow-card transition-all hover:-translate-y-0.5 hover:border-amber-300"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ card.label }}</p>
                        <component :is="card.icon" class="h-4 w-4 shrink-0" :class="card.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ card.count }}</p>
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Pending approvals -->
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h2 class="font-display text-sm font-semibold text-foreground">Pending Approvals</h2>
                        <Link href="/reservations?status=pending" class="text-xs font-medium text-amber-600 hover:text-amber-500">View all</Link>
                    </div>
                    <ul class="divide-y divide-border">
                        <li v-for="r in pendingApprovals.data" :key="r.id" class="flex items-center gap-3 px-5 py-3.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-muted">
                                <Clock class="h-4 w-4 text-amber-500" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-foreground">{{ r.customer_name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ r.reference }} · {{ r.vehicle_name }}</p>
                            </div>
                            <Link :href="`/reservations/${r.id}`" class="text-xs font-medium text-amber-600 hover:text-amber-500">Review</Link>
                        </li>
                        <li v-if="pendingApprovals.data.length === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">
                            No pending approvals 🎉
                        </li>
                    </ul>
                </div>

                <!-- Upcoming pickups -->
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h2 class="font-display text-sm font-semibold text-foreground">Upcoming Pickups</h2>
                        <CalendarCheck class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <ul class="divide-y divide-border">
                        <li v-for="r in upcoming.data" :key="r.id" class="flex items-center gap-3 px-5 py-3.5">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-foreground">{{ r.vehicle_name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ r.customer_name }} · {{ formatDate(r.pickup_date) }}</p>
                            </div>
                            <span class="badge shrink-0" :class="badgeClass(r.status)">{{ r.status_label }}</span>
                        </li>
                        <li v-if="upcoming.data.length === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">
                            No upcoming pickups.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
