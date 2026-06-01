<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BadgeCheck,
    CalendarCheck,
    Clock,
    KeyRound,
    MapPin,
    Search,
    ShieldCheck,
    Sparkles,
    Star,
    Wallet,
} from 'lucide-vue-next';
import { computed } from 'vue';
import CityDriveScene from '@/components/CityDriveScene.vue';
import VehicleCard from '@/components/VehicleCard.vue';
import type { StoreVehicle, SystemSettings } from '@/types';

const props = defineProps<{
    featured: StoreVehicle[];
    fleetCount: number;
}>();

const page = usePage<{ settings: SystemSettings }>();
const businessName = computed(() => page.props.settings?.business_name ?? 'DriveNow');

const stats = computed(() => [
    { value: `${props.fleetCount}+`, label: 'Vehicles' },
    { value: '24/7', label: 'Support' },
    { value: '4.9', label: 'Rating' },
    { value: '1', label: 'Branch' },
]);

const steps = [
    { icon: Search, title: 'Browse', text: 'Explore our fleet and find the perfect car for your trip.' },
    { icon: CalendarCheck, title: 'Book', text: 'Pick your dates and reserve online in under two minutes.' },
    { icon: KeyRound, title: 'Drive', text: 'Pick up your keys and hit the road. It’s that simple.' },
];

const features = [
    { icon: ShieldCheck, title: 'Fully Insured', text: 'Every rental includes comprehensive coverage for peace of mind.' },
    { icon: Wallet, title: 'Best Price Guarantee', text: 'Transparent daily rates with no hidden fees, ever.' },
    { icon: Clock, title: '24/7 Assistance', text: 'Round-the-clock roadside support whenever you need it.' },
    { icon: BadgeCheck, title: 'Quality Fleet', text: 'Well-maintained, regularly inspected modern vehicles.' },
];
</script>

<template>
    <Head title="Rent a car in Bacolod City" />

    <!-- Hero (day in light mode, night in dark mode) -->
    <section class="relative overflow-hidden bg-sky-200 dark:bg-[#0f172a]">
        <CityDriveScene />

        <div class="relative mx-auto w-full max-w-7xl px-4 pt-24 pb-48 sm:px-6 lg:px-8 lg:pt-28 lg:pb-56">
            <div class="max-w-2xl motion-safe:animate-[fadeUp_0.6s_ease-out_both]">
                <span class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-400/20 px-3 py-1 text-xs font-medium text-amber-800 backdrop-blur-sm dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-300">
                    <Sparkles class="h-3.5 w-3.5" /> {{ businessName }} · Bacolod City
                </span>
                <h1 class="font-display mt-6 text-4xl leading-[1.05] font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white">
                    Find your perfect ride for the journey ahead.
                </h1>
                <p class="mt-5 max-w-xl text-lg text-slate-700 dark:text-slate-300">
                    Affordable, reliable car rentals for tourists, families and businesses across
                    Bacolod City and nearby areas. Book in minutes — drive in moments.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <Link href="/vehicles" class="inline-flex h-12 items-center gap-2 rounded-xl bg-amber-500 px-6 text-sm font-semibold text-amber-950 shadow-lg shadow-amber-500/20 transition-all hover:-translate-y-0.5 hover:bg-amber-400">
                        Browse Cars <ArrowRight class="h-4 w-4" />
                    </Link>
                    <Link href="/register" class="inline-flex h-12 items-center gap-2 rounded-xl border border-slate-900/15 bg-white/40 px-6 text-sm font-semibold text-slate-900 backdrop-blur-sm transition-colors hover:bg-white/60 dark:border-white/15 dark:bg-transparent dark:text-white dark:hover:bg-white/5">
                        Create Account
                    </Link>
                </div>

                <dl class="mt-12 grid max-w-lg grid-cols-4 gap-4">
                    <div v-for="s in stats" :key="s.label">
                        <dt class="font-display text-2xl font-bold text-amber-600 dark:text-amber-400">{{ s.value }}</dt>
                        <dd class="text-[11px] tracking-wide text-slate-600 uppercase dark:text-slate-400">{{ s.label }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        <div class="relative h-1 w-full bg-gradient-to-r from-amber-500 via-amber-400 to-transparent" />
    </section>

    <!-- Featured fleet -->
    <section class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold tracking-widest text-amber-600 uppercase">Our Fleet</p>
                <h2 class="font-display mt-1 text-3xl font-bold tracking-tight text-foreground">Featured vehicles</h2>
                <p class="mt-1 text-sm text-muted-foreground">Hand-picked rides ready for your next trip.</p>
            </div>
            <Link href="/vehicles" class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-600 hover:text-amber-500">
                View all <ArrowRight class="h-4 w-4" />
            </Link>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <VehicleCard
                v-for="(v, i) in featured"
                :key="v.id"
                :vehicle="v"
                :style="{ animationDelay: `${i * 70}ms` }"
                class="motion-safe:animate-[fadeUp_0.5s_ease-out_both]"
            />
        </div>
        <p v-if="featured.length === 0" class="mt-8 rounded-xl border border-border bg-card p-10 text-center text-sm text-muted-foreground">
            No vehicles available right now. Please check back soon.
        </p>
    </section>

    <!-- How it works -->
    <section class="bg-muted/40">
        <div class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-[11px] font-semibold tracking-widest text-amber-600 uppercase">Simple Process</p>
                <h2 class="font-display mt-1 text-3xl font-bold tracking-tight text-foreground">How it works</h2>
            </div>
            <div class="mt-12 grid gap-8 md:grid-cols-3">
                <div v-for="(step, i) in steps" :key="step.title" class="relative text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-500 text-amber-950 shadow-lg shadow-amber-500/20">
                        <component :is="step.icon" class="h-7 w-7" />
                    </div>
                    <span class="font-display mt-4 block text-xs font-bold tracking-widest text-amber-600 uppercase">Step {{ i + 1 }}</span>
                    <h3 class="font-display mt-1 text-lg font-bold text-foreground">{{ step.title }}</h3>
                    <p class="mx-auto mt-2 max-w-xs text-sm text-muted-foreground">{{ step.text }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why choose us -->
    <section class="mx-auto w-full max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="f in features" :key="f.title" class="rounded-2xl border border-border bg-card p-6 shadow-card transition-all hover:-translate-y-1 hover:border-amber-300">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/20">
                    <component :is="f.icon" class="h-5 w-5" />
                </div>
                <h3 class="font-display mt-4 text-base font-bold text-foreground">{{ f.title }}</h3>
                <p class="mt-1.5 text-sm text-muted-foreground">{{ f.text }}</p>
            </div>
        </div>
    </section>

    <!-- CTA band -->
    <section class="mx-auto w-full max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500 to-amber-400 px-8 py-14 text-center text-amber-950 shadow-xl">
            <div class="pointer-events-none absolute -top-10 -right-6 h-40 w-40 rounded-full bg-white/20 blur-2xl" />
            <div class="relative">
                <div class="mx-auto flex w-fit items-center gap-1.5 text-sm font-semibold">
                    <Star class="h-4 w-4 fill-amber-950" /> Trusted by drivers across Negros Occidental
                </div>
                <h2 class="font-display mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Ready to hit the road?</h2>
                <p class="mx-auto mt-2 max-w-md text-amber-900">Create an account and book your first ride today.</p>
                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <Link href="/vehicles" class="inline-flex h-12 items-center gap-2 rounded-xl bg-[#0f172a] px-6 text-sm font-semibold text-white transition-transform hover:scale-[1.02]">
                        Browse Cars <ArrowRight class="h-4 w-4" />
                    </Link>
                    <Link href="/register" class="inline-flex h-12 items-center gap-2 rounded-xl border-2 border-amber-950/20 px-6 text-sm font-semibold text-amber-950 transition-colors hover:bg-amber-950/10">
                        Sign Up Free
                    </Link>
                </div>
                <p class="mt-6 flex items-center justify-center gap-1.5 text-xs text-amber-900">
                    <MapPin class="h-3.5 w-3.5" /> Brgy. Alijis, Bacolod City, Negros Occidental
                </p>
            </div>
        </div>
    </section>
</template>

<style>
@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
