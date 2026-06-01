<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BadgeCheck, Car, Clock, ShieldCheck } from 'lucide-vue-next';
import { computed } from 'vue';
import CityDriveScene from '@/components/CityDriveScene.vue';
import type { SystemSettings } from '@/types';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage<{ settings?: SystemSettings }>();
const businessName = computed(() => page.props.settings?.business_name ?? 'DriveNow');

const perks = [
    { icon: ShieldCheck, text: 'Fully insured rentals' },
    { icon: Clock, text: '24/7 roadside assistance' },
    { icon: BadgeCheck, text: 'Best-price guarantee' },
];
</script>

<template>
    <div class="relative grid min-h-dvh lg:grid-cols-[54%_46%]">
        <!-- Left brand panel with animated city (day/night aware) -->
        <div class="relative hidden flex-col justify-between overflow-hidden bg-sky-200 p-12 lg:flex dark:bg-[#0f172a]">
            <CityDriveScene />

            <!-- Brand -->
            <Link href="/" class="relative z-10 flex w-fit items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500 text-amber-950 shadow-lg">
                    <Car class="h-6 w-6" />
                </div>
                <div class="leading-tight">
                    <p class="font-display text-lg font-bold text-slate-900 dark:text-white">{{ businessName }}</p>
                    <p class="text-[11px] font-medium tracking-widest text-amber-700 uppercase dark:text-amber-400/80">Customer Portal</p>
                </div>
            </Link>

            <!-- Headline -->
            <div class="relative z-10 max-w-md">
                <h2 class="font-display text-4xl leading-[1.1] font-bold text-slate-900 dark:text-white">
                    Your next journey<br />starts here.
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                    Book reliable, affordable cars across Bacolod City in just a few taps.
                </p>

                <ul class="mt-8 space-y-3">
                    <li v-for="perk in perks" :key="perk.text" class="flex items-center gap-3 text-sm font-medium text-slate-800 dark:text-slate-200">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500/20 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400">
                            <component :is="perk.icon" class="h-4 w-4" />
                        </span>
                        {{ perk.text }}
                    </li>
                </ul>
            </div>

            <!-- Bottom accent -->
            <div class="relative z-10 h-1 w-24 rounded-full bg-amber-500" />
        </div>

        <!-- Right form panel -->
        <div class="relative flex items-center justify-center bg-background px-6 py-12">
            <div class="w-full max-w-[400px]">
                <!-- Mobile brand -->
                <Link href="/" class="mb-8 flex w-fit items-center gap-3 lg:hidden">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-amber-950">
                        <Car class="h-5 w-5" />
                    </div>
                    <p class="font-display text-lg font-bold text-foreground">{{ businessName }}</p>
                </Link>

                <div class="mb-6 space-y-1.5">
                    <h1 v-if="title" class="font-display text-2xl font-bold tracking-tight text-foreground">{{ title }}</h1>
                    <p v-if="description" class="text-sm text-muted-foreground">{{ description }}</p>
                </div>

                <slot />

                <p class="mt-8 text-center text-xs text-muted-foreground">
                    By continuing you agree to {{ businessName }}'s terms of service.
                </p>
            </div>
        </div>
    </div>
</template>
