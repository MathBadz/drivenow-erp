<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Car, Fuel, Settings2, Users } from 'lucide-vue-next';
import { formatCurrency } from '@/lib/format';
import type { StoreVehicle } from '@/types';

defineProps<{ vehicle: StoreVehicle }>();
</script>

<template>
    <Link
        :href="`/vehicles/${vehicle.id}`"
        class="group flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-card transition-all hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg"
    >
        <!-- Image placeholder -->
        <div class="relative h-44 overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
            <Car class="absolute -right-4 bottom-0 h-40 w-40 text-slate-300/60 transition-transform duration-300 group-hover:scale-110 dark:text-slate-700/60" stroke-width="1" />
            <span v-if="vehicle.category_label" class="absolute top-3 left-3 rounded-full bg-white/90 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700 backdrop-blur dark:bg-slate-900/80 dark:text-slate-200">
                {{ vehicle.category_label }}
            </span>
        </div>

        <div class="flex flex-1 flex-col p-4">
            <h3 class="font-display text-base font-bold text-foreground">{{ vehicle.name }}</h3>

            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                <span v-if="vehicle.seats" class="flex items-center gap-1"><Users class="h-3.5 w-3.5" /> {{ vehicle.seats }} seats</span>
                <span v-if="vehicle.transmission" class="flex items-center gap-1"><Settings2 class="h-3.5 w-3.5" /> {{ vehicle.transmission }}</span>
                <span v-if="vehicle.fuel_type" class="flex items-center gap-1"><Fuel class="h-3.5 w-3.5" /> {{ vehicle.fuel_type }}</span>
            </div>

            <div class="mt-4 flex items-end justify-between border-t border-border pt-3">
                <div>
                    <span class="font-display text-xl font-bold text-foreground">{{ formatCurrency(vehicle.daily_rate) }}</span>
                    <span class="text-xs text-muted-foreground">/day</span>
                </div>
                <span class="inline-flex items-center gap-1 text-sm font-semibold text-amber-600 transition-transform group-hover:translate-x-0.5">
                    Book <ArrowRight class="h-4 w-4" />
                </span>
            </div>
        </div>
    </Link>
</template>
