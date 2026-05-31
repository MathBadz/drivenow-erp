<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Car } from 'lucide-vue-next';
import { computed } from 'vue';
import type { SystemSettings } from '@/types';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage<{ settings?: SystemSettings }>();
const businessName = computed(() => page.props.settings?.business_name ?? 'DriveNow');

const stats = [
    { value: '24/7', label: 'Operations' },
    { value: '99.9%', label: 'Uptime' },
    { value: '1', label: 'Branch' },
];
</script>

<template>
    <div class="relative grid min-h-dvh lg:grid-cols-[52%_48%]">
        <!-- Left brand panel -->
        <div
            class="relative hidden flex-col justify-between overflow-hidden bg-[hsl(222,47%,9%)] p-12 text-white lg:flex"
        >
            <!-- Animated grid -->
            <svg
                class="pointer-events-none absolute inset-0 h-full w-full text-white/[0.04]"
                aria-hidden="true"
            >
                <defs>
                    <pattern
                        id="grid"
                        width="44"
                        height="44"
                        patternUnits="userSpaceOnUse"
                    >
                        <path
                            d="M44 0H0V44"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1"
                        />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>

            <!-- Amber orbs -->
            <div
                class="pointer-events-none absolute -top-24 -left-16 h-72 w-72 rounded-full bg-amber-500/20 blur-3xl"
            />
            <div
                class="pointer-events-none absolute right-0 bottom-24 h-64 w-64 rounded-full bg-amber-400/10 blur-3xl"
            />

            <!-- Decorative car outline -->
            <Car
                class="pointer-events-none absolute -right-10 bottom-28 h-72 w-72 text-white/[0.03]"
                stroke-width="1"
            />

            <!-- Brand -->
            <div class="relative z-10 flex items-center gap-3">
                <div
                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-500 text-amber-950 shadow-lg"
                >
                    <Car class="h-6 w-6" />
                </div>
                <div class="leading-tight">
                    <p class="font-display text-lg font-bold">{{ businessName }}</p>
                    <p class="text-[11px] font-medium tracking-widest text-amber-400/80 uppercase">
                        ERP · Operations Hub
                    </p>
                </div>
            </div>

            <!-- Headline -->
            <div class="relative z-10 max-w-md">
                <h2 class="font-display text-3xl leading-tight font-bold">
                    Drive your rental business forward.
                </h2>
                <p class="mt-3 text-sm leading-relaxed text-slate-300">
                    One unified platform for fleet, reservations, customers, billing,
                    maintenance, and analytics.
                </p>

                <div class="mt-8 grid grid-cols-3 gap-4">
                    <div v-for="stat in stats" :key="stat.label">
                        <p class="font-display text-2xl font-bold text-amber-400">
                            {{ stat.value }}
                        </p>
                        <p class="text-[11px] tracking-wide text-slate-400 uppercase">
                            {{ stat.label }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom accent -->
            <div class="relative z-10 h-1 w-24 rounded-full bg-amber-500" />
        </div>

        <!-- Right form panel -->
        <div class="flex items-center justify-center bg-background px-6 py-12">
            <div class="w-full max-w-[400px]">
                <!-- Mobile brand -->
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-amber-950"
                    >
                        <Car class="h-5 w-5" />
                    </div>
                    <p class="font-display text-lg font-bold text-foreground">
                        {{ businessName }}
                    </p>
                </div>

                <div class="mb-6 space-y-1.5">
                    <h1
                        v-if="title"
                        class="font-display text-2xl font-bold tracking-tight text-foreground"
                    >
                        {{ title }}
                    </h1>
                    <p v-if="description" class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
