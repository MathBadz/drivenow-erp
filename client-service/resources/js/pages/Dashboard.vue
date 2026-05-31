<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CalendarCheck, Car, CircleDollarSign, Clock, Plus } from 'lucide-vue-next';
import { computed } from 'vue';
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import type { Booking, BookingStats, User } from '@/types';

const props = defineProps<{
    bookings: Booking[];
    stats: BookingStats;
}>();

const page = usePage<{ auth: { user: User } }>();
const firstName = computed(() => page.props.auth.user?.name?.split(' ')[0] ?? 'there');

const cards = computed(() => [
    { label: 'Total Bookings', value: formatNumber(props.stats.total), icon: CalendarCheck, cls: 'text-blue-500' },
    { label: 'Active / Upcoming', value: formatNumber(props.stats.active), icon: Clock, cls: 'text-amber-500' },
    { label: 'Completed', value: formatNumber(props.stats.completed), icon: Car, cls: 'text-emerald-500' },
    { label: 'Total Spent', value: formatCurrency(props.stats.spent), icon: CircleDollarSign, cls: 'text-violet-500' },
]);

const badgeClass = (s: string) =>
    ({
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        confirmed: 'badge-reserved',
        active: 'badge-available',
        completed: 'badge-inactive',
        cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[s] ?? 'badge-inactive';
</script>

<template>
    <Head title="My Trips" />

    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold tracking-widest text-amber-600 uppercase">My Account</p>
                <h1 class="font-display mt-1 text-3xl font-bold tracking-tight text-foreground">Welcome back, {{ firstName }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">Manage your bookings and trip history.</p>
            </div>
            <Link href="/vehicles" class="inline-flex h-10 items-center gap-2 rounded-xl bg-amber-500 px-5 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400">
                <Plus class="h-4 w-4" /> Book a Car
            </Link>
        </div>

        <!-- Stats -->
        <div class="mt-8 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div v-for="c in cards" :key="c.label" class="rounded-xl border border-border bg-card p-4 shadow-card">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ c.label }}</p>
                    <component :is="c.icon" class="h-4 w-4 shrink-0" :class="c.cls" />
                </div>
                <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ c.value }}</p>
            </div>
        </div>

        <!-- Bookings -->
        <div class="mt-8 overflow-hidden rounded-2xl border border-border bg-card shadow-card">
            <div class="border-b border-border px-5 py-3.5">
                <h2 class="font-display text-sm font-semibold text-foreground">My Bookings</h2>
            </div>
            <ul class="divide-y divide-border">
                <li v-for="b in bookings" :key="b.id" class="flex flex-wrap items-center gap-3 px-5 py-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-muted">
                        <Car class="h-5 w-5 text-muted-foreground" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-foreground">{{ b.vehicle_name }}</p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ b.reference }} · {{ formatDate(b.pickup_date) }} → {{ formatDate(b.return_date) }} · {{ b.days }} day(s)
                        </p>
                    </div>
                    <span class="badge shrink-0" :class="badgeClass(b.status)">{{ b.status }}</span>
                    <span class="font-display w-24 shrink-0 text-right text-sm font-bold text-foreground">{{ formatCurrency(b.total) }}</span>
                    <Link :href="`/bookings/${b.id}`" class="shrink-0 text-xs font-semibold text-amber-600 hover:text-amber-500">Details</Link>
                </li>
                <li v-if="bookings.length === 0" class="px-5 py-16 text-center">
                    <CalendarCheck class="mx-auto h-10 w-10 text-muted-foreground/30" />
                    <p class="mt-3 text-sm font-medium text-foreground">No bookings yet</p>
                    <p class="mt-1 text-xs text-muted-foreground">Browse our fleet and book your first ride.</p>
                    <Link href="/vehicles" class="mt-4 inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 hover:bg-amber-400">
                        Browse Cars
                    </Link>
                </li>
            </ul>
        </div>
    </section>
</template>
