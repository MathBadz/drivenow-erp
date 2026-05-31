<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Car,
    Fuel,
    Gauge,
    MapPin,
    Palette,
    Settings2,
    Users,
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import type { Vehicle } from '@/types';

const props = defineProps<{ vehicle: { data: Vehicle } }>();

const v = props.vehicle.data;

const badgeClass =
    ({
        available: 'badge-available',
        reserved: 'badge-reserved',
        rented: 'badge-rented',
        maintenance: 'badge-maintenance',
        inactive: 'badge-inactive',
    }[v.status] ?? 'badge-inactive');

const specs = [
    { label: 'Category', value: v.category_label, icon: Car },
    { label: 'Transmission', value: v.transmission, icon: Settings2 },
    { label: 'Fuel Type', value: v.fuel_type, icon: Fuel },
    { label: 'Seats', value: `${v.seats}`, icon: Users },
    { label: 'Color', value: v.color ?? '—', icon: Palette },
    { label: 'Mileage', value: `${formatNumber(v.mileage)} km`, icon: Gauge },
    { label: 'Branch', value: v.branch, icon: MapPin },
];
</script>

<template>
    <Head :title="v.name" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Vehicles', href: '/vehicles' },
            { title: v.name, href: `/vehicles/${v.id}` },
        ]"
    >
        <div class="mx-auto flex h-full w-full max-w-4xl flex-1 flex-col gap-6 p-6">
            <Link href="/vehicles" class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
                <ArrowLeft class="h-4 w-4" />
                Back to vehicles
            </Link>

            <!-- Header card -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
                <div class="relative h-40 bg-[#0f172a]">
                    <div class="pointer-events-none absolute -top-10 right-10 h-40 w-40 rounded-full bg-amber-500/20 blur-3xl" />
                    <Car class="absolute right-6 bottom-2 h-28 w-28 text-white/5" stroke-width="1" />
                    <span class="badge absolute top-4 right-4" :class="badgeClass">{{ v.status_label }}</span>
                </div>
                <div class="flex flex-wrap items-end justify-between gap-4 p-6">
                    <div>
                        <p class="font-mono text-xs tracking-wider text-muted-foreground uppercase">{{ v.plate_number }}</p>
                        <h1 class="font-display mt-1 text-2xl font-bold tracking-tight text-foreground">{{ v.name }}</h1>
                    </div>
                    <div class="text-right">
                        <p class="font-display text-2xl font-bold text-foreground">{{ formatCurrency(v.daily_rate) }}</p>
                        <p class="text-xs text-muted-foreground">per day</p>
                    </div>
                </div>
            </div>

            <!-- Specs grid -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <div
                    v-for="spec in specs"
                    :key="spec.label"
                    class="rounded-xl border border-border bg-card p-4 shadow-card"
                >
                    <div class="flex items-center gap-2 text-muted-foreground">
                        <component :is="spec.icon" class="h-4 w-4" />
                        <p class="text-[11px] font-semibold tracking-widest uppercase">{{ spec.label }}</p>
                    </div>
                    <p class="mt-1.5 text-sm font-medium text-foreground">{{ spec.value }}</p>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="v.notes" class="rounded-xl border border-border bg-card p-5 shadow-card">
                <h2 class="font-display text-sm font-semibold text-foreground">Notes</h2>
                <p class="mt-2 text-sm whitespace-pre-line text-muted-foreground">{{ v.notes }}</p>
            </div>

            <p class="text-xs text-muted-foreground">
                Added {{ formatDate(v.created_at) }} · Last updated {{ formatDate(v.updated_at) }}
            </p>
        </div>
    </AppLayout>
</template>
