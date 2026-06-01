<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Car,
    CheckCircle2,
    CalendarClock,
    ChevronLeft,
    ChevronRight,
    Eye,
    ImageUp,
    UploadCloud,
    KeyRound,
    LayoutGrid,
    Pencil,
    Plus,
    Search,
    Trash2,
    Wrench,
    X,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatNumber } from '@/lib/format';
import type { Option, Paginated, Vehicle, VehicleFilters, VehicleStats } from '@/types';

const props = defineProps<{
    vehicles: Paginated<Vehicle>;
    stats: VehicleStats;
    filters: VehicleFilters;
    categoryOptions: Option[];
    statusOptions: Option[];
}>();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const categoryFilter = ref(props.filters.category ?? '');
let debounce: ReturnType<typeof setTimeout> | undefined;

function pushFilters() {
    router.get(
        '/vehicles',
        {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            category: categoryFilter.value || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(pushFilters, 300);
});
watch(categoryFilter, pushFilters);

function applyStatusFilter(value: string) {
    clearTimeout(debounce);
    statusFilter.value = value;
    pushFilters();
}

const filterCards = computed(() => [
    { key: '', label: 'Total', count: props.stats.total, icon: LayoutGrid, cls: 'text-blue-500' },
    { key: 'available', label: 'Available', count: props.stats.available, icon: CheckCircle2, cls: 'text-emerald-500' },
    { key: 'reserved', label: 'Reserved', count: props.stats.reserved, icon: CalendarClock, cls: 'text-blue-500' },
    { key: 'rented', label: 'Rented', count: props.stats.rented, icon: KeyRound, cls: 'text-amber-500' },
    { key: 'maintenance', label: 'Maintenance', count: props.stats.maintenance, icon: Wrench, cls: 'text-orange-500' },
    { key: 'inactive', label: 'Inactive', count: props.stats.inactive, icon: XCircle, cls: 'text-slate-400' },
]);

const badgeClass = (status: string) =>
    ({
        available: 'badge-available',
        reserved: 'badge-reserved',
        rented: 'badge-rented',
        maintenance: 'badge-maintenance',
        inactive: 'badge-inactive',
    })[status] ?? 'badge-inactive';

/* ---- Create / edit modal ---- */
const currentYear = new Date().getFullYear();
const showModal = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    make: '',
    model: '',
    year: currentYear,
    plate_number: '',
    category: 'sedan',
    status: 'available',
    branch: 'Main Branch',
    daily_rate: 0,
    seats: 5,
    transmission: 'Automatic',
    fuel_type: 'Gasoline',
    color: '',
    mileage: 0,
    image_url: '',
    image: null as File | null,
    remove_image: false,
    notes: '',
});

const imageInput = ref<HTMLInputElement | null>(null);
const imagePreview = ref<string | null>(null);
// Tracks image URLs that failed to load so we fall back to the icon placeholder.
const failedImages = ref(new Set<string>());

function onImageChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    form.image = file;
    form.remove_image = false;
    imagePreview.value = file ? URL.createObjectURL(file) : form.image_url || null;
}

function removeImage() {
    form.image = null;
    form.remove_image = true;
    imagePreview.value = null;
    if (imageInput.value) imageInput.value.value = '';
}

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
    if (imageInput.value) imageInput.value.value = '';
    showModal.value = true;
}

function openEdit(v: Vehicle) {
    editingId.value = v.id;
    form.clearErrors();
    form.make = v.make;
    form.model = v.model;
    form.year = v.year;
    form.plate_number = v.plate_number;
    form.category = v.category;
    form.status = v.status;
    form.branch = v.branch;
    form.daily_rate = v.daily_rate;
    form.seats = v.seats;
    form.transmission = v.transmission;
    form.fuel_type = v.fuel_type;
    form.color = v.color ?? '';
    form.mileage = v.mileage;
    form.image_url = v.image_url ?? '';
    form.image = null;
    form.remove_image = false;
    imagePreview.value = v.image_url || null;
    if (imageInput.value) imageInput.value.value = '';
    form.notes = v.notes ?? '';
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function submit() {
    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => closeModal(),
    };
    if (editingId.value) {
        form.put(`/vehicles/${editingId.value}`, options);
    } else {
        form.post('/vehicles', options);
    }
}

/* ---- Delete ---- */
const deleteTarget = ref<Vehicle | null>(null);

function confirmDelete() {
    if (!deleteTarget.value) return;
    router.delete(`/vehicles/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => (deleteTarget.value = null),
    });
}

const transmissions = ['Automatic', 'Manual'];
const fuels = ['Gasoline', 'Diesel', 'Electric', 'Hybrid'];
</script>

<template>
    <Head title="Vehicles" />

    <AppLayout :breadcrumbs="[{ title: 'Vehicles', href: '/vehicles' }]">
        <div class="flex min-h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-display text-2xl font-semibold tracking-tight text-foreground">
                        Vehicles
                    </h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        Manage your fleet inventory, availability and status.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400"
                    @click="openCreate"
                >
                    <Plus class="h-4 w-4" />
                    Add Vehicle
                </button>
            </div>

            <!-- Filter cards -->
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <button
                    v-for="card in filterCards"
                    :key="card.key"
                    type="button"
                    class="rounded-xl border p-4 text-left shadow-card transition-all"
                    :class="
                        (card.key === '' && !statusFilter) || statusFilter === card.key
                            ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20'
                            : 'border-border bg-card hover:border-amber-200'
                    "
                    @click="applyStatusFilter(card.key)"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">
                            {{ card.label }}
                        </p>
                        <component :is="card.icon" class="h-4 w-4 shrink-0" :class="card.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ card.count }}</p>
                </button>
            </div>

            <!-- Table card -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                <!-- Toolbar -->
                <div class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-3.5">
                    <div class="relative min-w-[220px] flex-1">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search make, model or plate…"
                            class="h-9 w-full rounded-lg border border-input bg-background pr-3 pl-9 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                        />
                    </div>
                    <select
                        v-model="categoryFilter"
                        class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                    >
                        <option value="">All Categories</option>
                        <option v-for="opt in categoryOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <span class="ml-auto text-xs text-muted-foreground">
                        {{ formatNumber(vehicles.meta.total) }} vehicles
                    </span>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border bg-muted/40">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Vehicle</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Plate</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Category</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Rate / day</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Status</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="v in vehicles.data"
                                :key="v.id"
                                class="group transition-colors hover:bg-muted/30"
                            >
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-muted">
                                            <img v-if="v.image_url && !failedImages.has(v.image_url)" :src="v.image_url" :alt="v.name" class="h-full w-full object-cover" @error="failedImages.add(v.image_url)" />
                                            <Car v-else class="h-4 w-4 text-muted-foreground" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-foreground">{{ v.name }}</p>
                                            <p class="truncate text-xs text-muted-foreground">
                                                {{ v.transmission }} · {{ v.seats }} seats · {{ v.color }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-mono text-sm text-foreground">{{ v.plate_number }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-muted-foreground">{{ v.category_label }}</td>
                                <td class="px-4 py-3.5 text-sm font-medium text-foreground">{{ formatCurrency(v.daily_rate) }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="badge" :class="badgeClass(v.status)">{{ v.status_label }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="`/vehicles/${v.id}`"
                                            title="View"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-amber-300 hover:text-amber-600"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                        </Link>
                                        <button
                                            type="button"
                                            title="Edit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-amber-300 hover:text-amber-600"
                                            @click="openEdit(v)"
                                        >
                                            <Pencil class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            title="Delete"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-red-300 hover:text-red-500"
                                            @click="deleteTarget = v"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="vehicles.data.length === 0">
                                <td :colspan="6" class="px-5 py-16 text-center">
                                    <Car class="mx-auto h-8 w-8 text-muted-foreground/30" />
                                    <p class="mt-3 text-sm font-medium text-foreground">No vehicles found</p>
                                    <p class="mt-1 text-xs text-muted-foreground">Try adjusting your search or filter criteria</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-border px-5 py-3">
                    <p class="text-xs text-muted-foreground">
                        {{ vehicles.meta.from ?? 0 }}–{{ vehicles.meta.to ?? 0 }} of {{ vehicles.meta.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-if="vehicles.links.prev"
                            :href="vehicles.links.prev"
                            preserve-scroll
                            class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40">
                            <ChevronLeft class="h-4 w-4" />
                        </span>
                        <span class="px-2 text-xs font-medium text-foreground">
                            {{ vehicles.meta.current_page }} / {{ vehicles.meta.last_page }}
                        </span>
                        <Link
                            v-if="vehicles.links.next"
                            :href="vehicles.links.next"
                            preserve-scroll
                            class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40">
                            <ChevronRight class="h-4 w-4" />
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create / edit modal -->
        <Teleport to="body">
            <Transition
                enter-from-class="opacity-0"
                enter-active-class="transition duration-150"
                leave-to-class="opacity-0"
                leave-active-class="transition duration-150"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.esc.window="closeModal">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal" />
                    <div class="relative z-10 flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-2xl" role="dialog" aria-modal="true" aria-labelledby="vehicle-modal-title">
                        <div class="flex items-center justify-between border-b border-border px-6 py-4">
                            <h2 id="vehicle-modal-title" class="font-display font-semibold text-foreground">
                                {{ editingId ? 'Edit Vehicle' : 'Add Vehicle' }}
                            </h2>
                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted"
                                @click="closeModal"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <form class="max-h-[70vh] overflow-y-auto" @submit.prevent="submit">
                            <div class="grid gap-4 p-6 sm:grid-cols-2">
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Make</label>
                                    <input v-model="form.make" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.make }" />
                                    <p v-if="form.errors.make" class="text-[11px] text-red-500">{{ form.errors.make }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Model</label>
                                    <input v-model="form.model" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.model }" />
                                    <p v-if="form.errors.model" class="text-[11px] text-red-500">{{ form.errors.model }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Year</label>
                                    <input v-model.number="form.year" type="number" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.year }" />
                                    <p v-if="form.errors.year" class="text-[11px] text-red-500">{{ form.errors.year }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Plate Number</label>
                                    <input v-model="form.plate_number" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm uppercase focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.plate_number }" />
                                    <p v-if="form.errors.plate_number" class="text-[11px] text-red-500">{{ form.errors.plate_number }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Category</label>
                                    <select v-model="form.category" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        <option v-for="opt in categoryOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Status</label>
                                    <select v-model="form.status" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Daily Rate (USD)</label>
                                    <input v-model.number="form.daily_rate" type="number" step="0.01" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.daily_rate }" />
                                    <p v-if="form.errors.daily_rate" class="text-[11px] text-red-500">{{ form.errors.daily_rate }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Seats</label>
                                    <input v-model.number="form.seats" type="number" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Transmission</label>
                                    <select v-model="form.transmission" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        <option v-for="t in transmissions" :key="t" :value="t">{{ t }}</option>
                                    </select>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Fuel Type</label>
                                    <select v-model="form.fuel_type" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        <option v-for="f in fuels" :key="f" :value="f">{{ f }}</option>
                                    </select>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Color</label>
                                    <input v-model="form.color" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Mileage (km)</label>
                                    <input v-model.number="form.mileage" type="number" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Vehicle Photo</label>
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-20 w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-border bg-muted/40">
                                            <img v-if="imagePreview" :src="imagePreview" alt="Vehicle preview" class="h-full w-full object-cover" />
                                            <ImageUp v-else class="h-7 w-7 text-muted-foreground/40" />
                                        </div>
                                        <div class="flex-1">
                                            <input ref="imageInput" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onImageChange" />
                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-3 text-sm font-medium text-foreground transition-colors hover:bg-muted active:scale-95" @click="imageInput?.click()">
                                                    <UploadCloud class="h-4 w-4" /> {{ imagePreview ? 'Change photo' : 'Upload photo' }}
                                                </button>
                                                <button v-if="imagePreview" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-red-200 px-3 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 active:scale-95 dark:border-red-800/50 dark:hover:bg-red-900/10" @click="removeImage">
                                                    <Trash2 class="h-4 w-4" /> Remove
                                                </button>
                                            </div>
                                            <p class="mt-1.5 text-[11px] text-muted-foreground">PNG, JPG or WebP up to 2 MB.</p>
                                            <p v-if="form.errors.image" class="mt-1 text-[11px] text-red-500">{{ form.errors.image }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Notes</label>
                                    <textarea v-model="form.notes" rows="2" class="rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 border-t border-border px-6 py-4">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="closeModal">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="form.processing" class="h-9 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">
                                    {{ form.processing ? 'Saving…' : editingId ? 'Save Changes' : 'Add Vehicle' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete confirmation -->
        <Teleport to="body">
            <Transition
                enter-from-class="opacity-0"
                enter-active-class="transition duration-150"
                leave-to-class="opacity-0"
                leave-active-class="transition duration-150"
            >
                <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.esc.window="deleteTarget = null">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="deleteTarget = null" />
                    <div class="relative z-10 mx-4 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-2xl" role="dialog" aria-modal="true">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                            <Trash2 class="h-5 w-5 text-red-600 dark:text-red-400" />
                        </div>
                        <h2 class="font-display mt-4 font-semibold text-foreground">Remove vehicle?</h2>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ deleteTarget.name }} ({{ deleteTarget.plate_number }}) will be permanently removed from the fleet.
                        </p>
                        <div class="mt-5 flex items-center justify-end gap-3">
                            <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="deleteTarget = null">
                                Cancel
                            </button>
                            <button type="button" class="h-9 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white hover:bg-red-400" @click="confirmDelete">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
