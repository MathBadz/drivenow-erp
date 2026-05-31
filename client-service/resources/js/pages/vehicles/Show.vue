<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Car,
    Check,
    Fuel,
    Gauge,
    LogIn,
    Settings2,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import VehicleCard from '@/components/VehicleCard.vue';
import { formatCurrency } from '@/lib/format';
import type { StoreVehicle, User } from '@/types';

const props = defineProps<{
    vehicle: StoreVehicle;
    related: StoreVehicle[];
}>();

const page = usePage<{ auth: { user: User | null } }>();
const isAuthed = computed(() => !!page.props.auth?.user);

const today = new Date().toISOString().slice(0, 10);
const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);

const form = useForm({
    vehicle_id: props.vehicle.id,
    pickup_date: today,
    return_date: tomorrow,
    notes: '',
});

const days = computed(() => {
    const p = new Date(form.pickup_date).getTime();
    const r = new Date(form.return_date).getTime();
    if (Number.isNaN(p) || Number.isNaN(r)) return 0;
    return Math.max(1, Math.round((r - p) / 86_400_000));
});
const total = computed(() => days.value * (props.vehicle.daily_rate ?? 0));

function submit() {
    form.post('/bookings', { preserveScroll: true });
}

const specs = computed(() => [
    { label: 'Seats', value: props.vehicle.seats ? `${props.vehicle.seats}` : '—', icon: Users },
    { label: 'Transmission', value: props.vehicle.transmission ?? '—', icon: Settings2 },
    { label: 'Fuel', value: props.vehicle.fuel_type ?? '—', icon: Fuel },
    { label: 'Category', value: props.vehicle.category_label ?? '—', icon: Car },
]);

const perks = ['Comprehensive insurance included', 'Free cancellation up to 24h before', '24/7 roadside assistance', 'Unlimited mileage within the city'];
</script>

<template>
    <Head :title="vehicle.name" />

    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <Link href="/vehicles" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
            <ArrowLeft class="h-4 w-4" /> Back to all cars
        </Link>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1.5fr_1fr]">
            <!-- Showcase -->
            <div>
                <div class="relative h-72 overflow-hidden rounded-2xl border border-border bg-gradient-to-br from-slate-100 to-slate-200 shadow-card dark:from-slate-800 dark:to-slate-900 sm:h-96">
                    <Car class="absolute -right-6 bottom-0 h-72 w-72 text-slate-300/60 dark:text-slate-700/60 sm:h-96 sm:w-96" stroke-width="0.75" />
                    <span v-if="vehicle.category_label" class="absolute top-4 left-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-slate-700 backdrop-blur dark:bg-slate-900/80 dark:text-slate-200">{{ vehicle.category_label }}</span>
                </div>

                <div class="mt-6">
                    <h1 class="font-display text-3xl font-bold tracking-tight text-foreground">{{ vehicle.name }}</h1>
                    <p v-if="vehicle.plate_number" class="mt-1 font-mono text-sm text-muted-foreground">Plate {{ vehicle.plate_number }}</p>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div v-for="s in specs" :key="s.label" class="rounded-xl border border-border bg-card p-4 shadow-card">
                        <component :is="s.icon" class="h-4 w-4 text-amber-500" />
                        <p class="mt-2 text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ s.label }}</p>
                        <p class="text-sm font-medium text-foreground">{{ s.value }}</p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-border bg-card p-6 shadow-card">
                    <h2 class="font-display text-base font-bold text-foreground">What’s included</h2>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        <li v-for="perk in perks" :key="perk" class="flex items-start gap-2 text-sm text-muted-foreground">
                            <Check class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" /> {{ perk }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Booking card -->
            <div class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                    <div class="flex items-baseline justify-between">
                        <span class="font-display text-3xl font-bold text-foreground">{{ formatCurrency(vehicle.daily_rate) }}</span>
                        <span class="text-sm text-muted-foreground">per day</span>
                    </div>

                    <template v-if="isAuthed">
                        <form class="mt-5 space-y-4" @submit.prevent="submit">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Pickup</label>
                                    <input v-model="form.pickup_date" type="date" :min="today" class="h-10 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.pickup_date }" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Return</label>
                                    <input v-model="form.return_date" type="date" :min="form.pickup_date" class="h-10 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.return_date }" />
                                </div>
                            </div>
                            <p v-if="form.errors.pickup_date || form.errors.return_date" class="text-[11px] text-red-500">{{ form.errors.pickup_date || form.errors.return_date }}</p>

                            <div class="grid gap-1.5">
                                <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Notes (optional)</label>
                                <textarea v-model="form.notes" rows="2" placeholder="Any special requests?" class="rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                            </div>

                            <div class="space-y-2 border-t border-border pt-4 text-sm">
                                <div class="flex justify-between text-muted-foreground">
                                    <span>{{ formatCurrency(vehicle.daily_rate) }} × {{ days }} day(s)</span>
                                    <span>{{ formatCurrency(total) }}</span>
                                </div>
                                <div class="flex justify-between font-semibold">
                                    <span class="text-foreground">Total</span>
                                    <span class="font-display text-lg text-foreground">{{ formatCurrency(total) }}</span>
                                </div>
                            </div>

                            <button type="submit" :disabled="form.processing" class="h-12 w-full rounded-xl bg-amber-500 text-sm font-semibold text-amber-950 transition-colors hover:bg-amber-400 disabled:opacity-60">
                                {{ form.processing ? 'Booking…' : 'Book Now' }}
                            </button>
                            <p class="flex items-center justify-center gap-1.5 text-xs text-muted-foreground">
                                <ShieldCheck class="h-3.5 w-3.5 text-emerald-500" /> No payment required to reserve
                            </p>
                        </form>
                    </template>

                    <template v-else>
                        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800/50 dark:bg-amber-900/10">
                            <p class="text-sm text-amber-700 dark:text-amber-300">Sign in to your account to reserve this vehicle.</p>
                        </div>
                        <Link href="/login" class="mt-4 inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-amber-500 text-sm font-semibold text-amber-950 transition-colors hover:bg-amber-400">
                            <LogIn class="h-4 w-4" /> Sign in to Book
                        </Link>
                        <p class="mt-3 text-center text-xs text-muted-foreground">
                            New here? <Link href="/register" class="font-semibold text-amber-600 hover:text-amber-500">Create an account</Link>
                        </p>
                    </template>
                </div>
            </div>
        </div>

        <!-- Related -->
        <div v-if="related.length" class="mt-14">
            <h2 class="font-display text-xl font-bold tracking-tight text-foreground">You might also like</h2>
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <VehicleCard v-for="v in related" :key="v.id" :vehicle="v" />
            </div>
        </div>
    </section>
</template>
