<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    CalendarCheck,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleDot,
    Clock,
    Eye,
    LayoutGrid,
    Plus,
    Search,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import type { AvailableVehicle, Option, Paginated, Rental, RentalFilters, RentalStats } from '@/types';

const props = defineProps<{
    rentals: Paginated<Rental>;
    stats: RentalStats;
    filters: RentalFilters;
    availableVehicles: AvailableVehicle[];
    statusOptions: Option[];
}>();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
let debounce: ReturnType<typeof setTimeout> | undefined;

function pushFilters() {
    router.get(
        '/reservations',
        { search: search.value || undefined, status: statusFilter.value || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(pushFilters, 300);
});

function applyStatusFilter(value: string) {
    clearTimeout(debounce);
    statusFilter.value = value;
    pushFilters();
}

const filterCards = computed(() => [
    { key: '', label: 'Total', count: props.stats.total, icon: LayoutGrid, cls: 'text-blue-500' },
    { key: 'pending', label: 'Pending', count: props.stats.pending, icon: Clock, cls: 'text-amber-500' },
    { key: 'approved', label: 'Approved', count: props.stats.approved, icon: CalendarCheck, cls: 'text-blue-500' },
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

/* ---- New reservation modal ---- */
const showModal = ref(false);
const today = new Date().toISOString().slice(0, 10);

const form = useForm({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    vehicle_id: null as number | null,
    vehicle_name: '',
    vehicle_plate: '',
    pickup_branch: 'Main Branch',
    pickup_date: today,
    return_date: today,
    notes: '',
});

watch(
    () => form.vehicle_id,
    (id) => {
        const v = props.availableVehicles.find((x) => x.id === Number(id));
        if (v) {
            form.vehicle_name = v.name;
            form.vehicle_plate = v.plate_number;
        }
    },
);

const selectedVehicle = computed(() =>
    props.availableVehicles.find((v) => v.id === Number(form.vehicle_id)) ?? null,
);

const days = computed(() => {
    const p = new Date(form.pickup_date).getTime();
    const r = new Date(form.return_date).getTime();
    if (Number.isNaN(p) || Number.isNaN(r)) return 0;
    return Math.max(1, Math.round((r - p) / 86_400_000));
});

const totalPreview = computed(() => days.value * (selectedVehicle.value?.daily_rate ?? 0));

function openCreate() {
    form.reset();
    form.clearErrors();
    form.pickup_date = today;
    form.return_date = today;
    showModal.value = true;
}

function submit() {
    form
        .transform((data) => ({
            ...data,
            daily_rate: selectedVehicle.value?.daily_rate ?? 0,
        }))
        .post('/reservations', {
            preserveScroll: true,
            onSuccess: () => (showModal.value = false),
        });
}
</script>

<template>
    <Head title="Reservations" />

    <AppLayout :breadcrumbs="[{ title: 'Reservations', href: '/reservations' }]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-display text-2xl font-semibold tracking-tight text-foreground">Reservations</h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">Manage bookings through their full rental lifecycle.</p>
                </div>
                <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400" @click="openCreate">
                    <Plus class="h-4 w-4" />
                    New Reservation
                </button>
            </div>

            <!-- Filter cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <button
                    v-for="card in filterCards"
                    :key="card.key"
                    type="button"
                    class="rounded-xl border p-4 text-left shadow-card transition-all"
                    :class="(card.key === '' && !statusFilter) || statusFilter === card.key ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20' : 'border-border bg-card hover:border-amber-200'"
                    @click="applyStatusFilter(card.key)"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ card.label }}</p>
                        <component :is="card.icon" class="h-4 w-4 shrink-0" :class="card.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ card.count }}</p>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                <div class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-3.5">
                    <div class="relative min-w-[220px] flex-1">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search reference, customer or vehicle…"
                            class="h-8 w-full rounded-lg border border-input bg-background pr-3 pl-9 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                        />
                    </div>
                    <span class="ml-auto text-xs text-muted-foreground">{{ formatNumber(rentals.meta.total) }} reservations</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border bg-muted/40">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Reference</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Vehicle</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Dates</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Status</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="r in rentals.data" :key="r.id" class="group transition-colors hover:bg-muted/30">
                                <td class="px-5 py-3.5"><span class="font-mono text-sm font-medium text-foreground">{{ r.reference }}</span></td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm font-medium text-foreground">{{ r.customer_name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ r.customer_phone }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm text-foreground">{{ r.vehicle_name }}</p>
                                    <p class="font-mono text-xs text-muted-foreground">{{ r.vehicle_plate }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-muted-foreground">
                                    {{ formatDate(r.pickup_date) }} → {{ formatDate(r.return_date) }}
                                    <span class="block text-xs">{{ r.days }} day(s)</span>
                                </td>
                                <td class="px-4 py-3.5 text-sm font-medium text-foreground">{{ formatCurrency(r.total) }}</td>
                                <td class="px-4 py-3.5"><span class="badge" :class="badgeClass(r.status)">{{ r.status_label }}</span></td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/reservations/${r.id}`" title="View" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-amber-300 hover:text-amber-600">
                                            <Eye class="h-3.5 w-3.5" />
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="rentals.data.length === 0">
                                <td :colspan="7" class="px-5 py-16 text-center">
                                    <CalendarCheck class="mx-auto h-8 w-8 text-muted-foreground/30" />
                                    <p class="mt-3 text-sm font-medium text-foreground">No reservations found</p>
                                    <p class="mt-1 text-xs text-muted-foreground">Try adjusting your search or filter criteria</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-border px-5 py-3">
                    <p class="text-xs text-muted-foreground">{{ rentals.meta.from ?? 0 }}–{{ rentals.meta.to ?? 0 }} of {{ rentals.meta.total }}</p>
                    <div class="flex items-center gap-1">
                        <Link v-if="rentals.links.prev" :href="rentals.links.prev" preserve-scroll class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300">
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40"><ChevronLeft class="h-4 w-4" /></span>
                        <span class="px-2 text-xs font-medium text-foreground">{{ rentals.meta.current_page }} / {{ rentals.meta.last_page }}</span>
                        <Link v-if="rentals.links.next" :href="rentals.links.next" preserve-scroll class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300">
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40"><ChevronRight class="h-4 w-4" /></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- New reservation modal -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition duration-150" leave-to-class="opacity-0" leave-active-class="transition duration-150">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false" />
                    <div class="relative z-10 mx-4 w-full max-w-2xl rounded-2xl border border-border bg-card shadow-2xl">
                        <div class="flex items-center justify-between border-b border-border px-6 py-4">
                            <h2 class="font-display font-semibold text-foreground">New Reservation</h2>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted" @click="showModal = false">
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <form class="max-h-[70vh] overflow-y-auto" @submit.prevent="submit">
                            <div class="grid gap-4 p-6 sm:grid-cols-2">
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Customer Name</label>
                                    <input v-model="form.customer_name" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.customer_name }" />
                                    <p v-if="form.errors.customer_name" class="text-[11px] text-red-500">{{ form.errors.customer_name }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Phone</label>
                                    <input v-model="form.customer_phone" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Email</label>
                                    <input v-model="form.customer_email" type="email" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.customer_email }" />
                                    <p v-if="form.errors.customer_email" class="text-[11px] text-red-500">{{ form.errors.customer_email }}</p>
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Vehicle</label>
                                    <select v-model="form.vehicle_id" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.vehicle_id }">
                                        <option :value="null" disabled>Select an available vehicle…</option>
                                        <option v-for="v in availableVehicles" :key="v.id" :value="v.id">
                                            {{ v.name }} · {{ v.plate_number }} · {{ formatCurrency(v.daily_rate) }}/day
                                        </option>
                                    </select>
                                    <p v-if="form.errors.vehicle_id" class="text-[11px] text-red-500">{{ form.errors.vehicle_id }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Pickup Date</label>
                                    <input v-model="form.pickup_date" type="date" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.pickup_date }" />
                                    <p v-if="form.errors.pickup_date" class="text-[11px] text-red-500">{{ form.errors.pickup_date }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Return Date</label>
                                    <input v-model="form.return_date" type="date" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.return_date }" />
                                    <p v-if="form.errors.return_date" class="text-[11px] text-red-500">{{ form.errors.return_date }}</p>
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Notes</label>
                                    <textarea v-model="form.notes" rows="2" class="rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>

                                <!-- Live summary -->
                                <div v-if="selectedVehicle" class="rounded-xl border border-amber-200 bg-amber-50 p-4 sm:col-span-2 dark:border-amber-800/50 dark:bg-amber-900/10">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-amber-700 dark:text-amber-300">{{ days }} day(s) × {{ formatCurrency(selectedVehicle.daily_rate) }}</span>
                                        <span class="font-display text-lg font-bold text-amber-700 dark:text-amber-300">{{ formatCurrency(totalPreview) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-border px-6 py-4">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="showModal = false">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="h-9 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">
                                    {{ form.processing ? 'Creating…' : 'Create Reservation' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
