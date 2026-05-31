<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Car, Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import VehicleCard from '@/components/VehicleCard.vue';
import type { StoreVehicle } from '@/types';

const props = defineProps<{
    vehicles: StoreVehicle[];
    filters: { category: string | null; search: string | null };
}>();

const search = ref(props.filters.search ?? '');
const category = ref(props.filters.category ?? '');
let debounce: ReturnType<typeof setTimeout> | undefined;

function push() {
    router.get('/vehicles', {
        search: search.value || undefined,
        category: category.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(push, 300);
});

function setCategory(value: string) {
    category.value = value;
    push();
}

const categories = [
    { key: '', label: 'All' },
    { key: 'sedan', label: 'Sedan' },
    { key: 'hatchback', label: 'Hatchback' },
    { key: 'suv', label: 'SUV' },
    { key: 'van', label: 'Van' },
    { key: 'pickup', label: 'Pickup' },
];
</script>

<template>
    <Head title="Browse Cars" />

    <!-- Header strip -->
    <section class="border-b border-border bg-[#0f172a] text-white">
        <div class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Our Fleet</p>
            <h1 class="font-display mt-1 text-3xl font-bold tracking-tight sm:text-4xl">Browse available cars</h1>
            <p class="mt-2 max-w-xl text-sm text-slate-300">Choose from our range of sedans, SUVs, vans and more — all ready to book.</p>
        </div>
    </section>

    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <!-- Filters -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[240px] flex-1">
                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by make or model…"
                    class="h-11 w-full rounded-xl border border-input bg-card pr-3 pl-10 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                />
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button
                v-for="cat in categories"
                :key="cat.key"
                type="button"
                class="rounded-full px-4 py-1.5 text-sm font-medium transition-colors"
                :class="category === cat.key ? 'bg-amber-500 text-amber-950' : 'border border-border bg-card text-muted-foreground hover:border-amber-300'"
                @click="setCategory(cat.key)"
            >
                {{ cat.label }}
            </button>
        </div>

        <!-- Grid -->
        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <VehicleCard v-for="v in vehicles" :key="v.id" :vehicle="v" />
        </div>

        <div v-if="vehicles.length === 0" class="mt-8 rounded-2xl border border-border bg-card p-16 text-center">
            <Car class="mx-auto h-10 w-10 text-muted-foreground/30" />
            <p class="mt-3 text-sm font-medium text-foreground">No vehicles match your search</p>
            <p class="mt-1 text-xs text-muted-foreground">Try a different category or clear your filters.</p>
        </div>
    </section>
</template>
