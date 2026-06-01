<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    CalendarClock,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleDot,
    Eye,
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
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import type { FleetVehicle, MaintenanceFilters, MaintenanceRecord, MaintenanceStats, Option, Paginated } from '@/types';

const props = defineProps<{
    records: Paginated<MaintenanceRecord>;
    stats: MaintenanceStats;
    filters: MaintenanceFilters;
    vehicles: FleetVehicle[];
    typeOptions: Option[];
    severityOptions: Option[];
}>();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const typeFilter = ref(props.filters.type ?? '');
let debounce: ReturnType<typeof setTimeout> | undefined;

function pushFilters() {
    router.get('/maintenance', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        type: typeFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}
watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(pushFilters, 300);
});
watch(typeFilter, pushFilters);
function applyStatusFilter(value: string) {
    clearTimeout(debounce);
    statusFilter.value = value;
    pushFilters();
}

const filterCards = computed(() => [
    { key: '', label: 'Total', count: props.stats.total, icon: LayoutGrid, cls: 'text-blue-500' },
    { key: 'scheduled', label: 'Scheduled', count: props.stats.scheduled, icon: CalendarClock, cls: 'text-blue-500' },
    { key: 'in_progress', label: 'In Progress', count: props.stats.in_progress, icon: CircleDot, cls: 'text-orange-500' },
    { key: 'completed', label: 'Completed', count: props.stats.completed, icon: CheckCircle2, cls: 'text-emerald-500' },
    { key: 'cancelled', label: 'Cancelled', count: props.stats.cancelled, icon: XCircle, cls: 'text-slate-400' },
]);

const statusBadge = (s: string) =>
    ({ scheduled: 'badge-reserved', in_progress: 'badge-maintenance', completed: 'badge-available', cancelled: 'badge-inactive' })[s] ?? 'badge-inactive';
const severityBadge = (s: string) =>
    ({
        low: 'bg-slate-100 text-slate-600 dark:bg-slate-800/40 dark:text-slate-400',
        medium: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        high: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[s] ?? 'bg-slate-100 text-slate-600';

/* modal */
const showModal = ref(false);
const editingId = ref<number | null>(null);
const today = new Date().toISOString().slice(0, 10);
const form = useForm({
    vehicle_id: null as number | null,
    vehicle_name: '', vehicle_plate: '',
    type: 'inspection', severity: 'low', title: '', description: '',
    cost: 0, odometer: 0, scheduled_date: today, notes: '',
});

watch(() => form.vehicle_id, (id) => {
    const v = props.vehicles.find((x) => x.id === Number(id));
    if (v) { form.vehicle_name = v.name; form.vehicle_plate = v.plate_number; }
});

function openCreate() {
    editingId.value = null;
    form.reset();
    form.scheduled_date = today;
    form.clearErrors();
    showModal.value = true;
}
function openEdit(r: MaintenanceRecord) {
    editingId.value = r.id;
    form.clearErrors();
    form.vehicle_id = r.vehicle_id;
    form.vehicle_name = r.vehicle_name; form.vehicle_plate = r.vehicle_plate;
    form.type = r.type; form.severity = r.severity; form.title = r.title;
    form.description = r.description ?? ''; form.cost = r.cost; form.odometer = r.odometer ?? 0;
    form.scheduled_date = r.scheduled_date ?? today; form.notes = r.notes ?? '';
    showModal.value = true;
}
function submit() {
    const opts = { preserveScroll: true, onSuccess: () => (showModal.value = false) };
    if (editingId.value) form.put(`/maintenance/${editingId.value}`, opts);
    else form.post('/maintenance', opts);
}

const deleteTarget = ref<MaintenanceRecord | null>(null);
function confirmDelete() {
    if (!deleteTarget.value) return;
    router.delete(`/maintenance/${deleteTarget.value.id}`, { preserveScroll: true, onFinish: () => (deleteTarget.value = null) });
}
</script>

<template>
    <Head title="Maintenance" />

    <AppLayout :breadcrumbs="[{ title: 'Maintenance', href: '/maintenance' }]">
        <div class="flex min-h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-display text-2xl font-semibold tracking-tight text-foreground">Maintenance Records</h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">Schedule inspections, repairs and track vehicle downtime.</p>
                </div>
                <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400" @click="openCreate">
                    <Plus class="h-4 w-4" /> New Record
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <button v-for="card in filterCards" :key="card.key" type="button" class="rounded-xl border p-4 text-left shadow-card transition-all" :class="(card.key === '' && !statusFilter) || statusFilter === card.key ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20' : 'border-border bg-card hover:border-amber-200'" @click="applyStatusFilter(card.key)">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ card.label }}</p>
                        <component :is="card.icon" class="h-4 w-4 shrink-0" :class="card.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ card.count }}</p>
                </button>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                <div class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-3.5">
                    <div class="relative min-w-[220px] flex-1">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="search" type="text" placeholder="Search reference, vehicle or title…" class="h-8 w-full rounded-lg border border-input bg-background pr-3 pl-9 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                    </div>
                    <select v-model="typeFilter" class="h-8 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                        <option value="">All Types</option>
                        <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <span class="ml-auto text-xs text-muted-foreground">{{ formatNumber(records.meta.total) }} records</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border bg-muted/40">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Record</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Vehicle</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Type</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Cost</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Severity</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Status</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="r in records.data" :key="r.id" class="group transition-colors hover:bg-muted/30">
                                <td class="px-5 py-3.5">
                                    <p class="text-sm font-medium text-foreground">{{ r.title }}</p>
                                    <p class="font-mono text-xs text-muted-foreground">{{ r.reference }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm text-foreground">{{ r.vehicle_name }}</p>
                                    <p class="font-mono text-xs text-muted-foreground">{{ r.vehicle_plate }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-sm text-muted-foreground">{{ r.type_label }}</td>
                                <td class="px-4 py-3.5 text-sm font-medium text-foreground">{{ formatCurrency(r.cost) }}</td>
                                <td class="px-4 py-3.5"><span class="badge" :class="severityBadge(r.severity)">{{ r.severity_label }}</span></td>
                                <td class="px-4 py-3.5"><span class="badge" :class="statusBadge(r.status)">{{ r.status_label }}</span></td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/maintenance/${r.id}`" title="View" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-amber-300 hover:text-amber-600"><Eye class="h-3.5 w-3.5" /></Link>
                                        <button type="button" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-amber-300 hover:text-amber-600" @click="openEdit(r)"><Pencil class="h-3.5 w-3.5" /></button>
                                        <button type="button" title="Delete" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-red-300 hover:text-red-500" @click="deleteTarget = r"><Trash2 class="h-3.5 w-3.5" /></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="records.data.length === 0">
                                <td :colspan="7" class="px-5 py-16 text-center">
                                    <Wrench class="mx-auto h-8 w-8 text-muted-foreground/30" />
                                    <p class="mt-3 text-sm font-medium text-foreground">No records found</p>
                                    <p class="mt-1 text-xs text-muted-foreground">Try adjusting your search or filter criteria</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-border px-5 py-3">
                    <p class="text-xs text-muted-foreground">{{ records.meta.from ?? 0 }}–{{ records.meta.to ?? 0 }} of {{ records.meta.total }}</p>
                    <div class="flex items-center gap-1">
                        <Link v-if="records.links.prev" :href="records.links.prev" preserve-scroll class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300"><ChevronLeft class="h-4 w-4" /></Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40"><ChevronLeft class="h-4 w-4" /></span>
                        <span class="px-2 text-xs font-medium text-foreground">{{ records.meta.current_page }} / {{ records.meta.last_page }}</span>
                        <Link v-if="records.links.next" :href="records.links.next" preserve-scroll class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300"><ChevronRight class="h-4 w-4" /></Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40"><ChevronRight class="h-4 w-4" /></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create / edit modal -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition duration-150" leave-to-class="opacity-0" leave-active-class="transition duration-150">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false" />
                    <div class="relative z-10 mx-4 w-full max-w-2xl rounded-2xl border border-border bg-card shadow-2xl">
                        <div class="flex items-center justify-between border-b border-border px-6 py-4">
                            <h2 class="font-display font-semibold text-foreground">{{ editingId ? 'Edit Record' : 'New Maintenance Record' }}</h2>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted" @click="showModal = false"><X class="h-4 w-4" /></button>
                        </div>
                        <form class="max-h-[70vh] overflow-y-auto" @submit.prevent="submit">
                            <div class="grid gap-4 p-6 sm:grid-cols-2">
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Vehicle</label>
                                    <select v-model="form.vehicle_id" :disabled="!!editingId" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none disabled:opacity-60" :class="{ 'border-red-400': form.errors.vehicle_id }">
                                        <option :value="null" disabled>Select a vehicle…</option>
                                        <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.name }} · {{ v.plate_number }}</option>
                                    </select>
                                    <p v-if="form.errors.vehicle_id" class="text-[11px] text-red-500">{{ form.errors.vehicle_id }}</p>
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Title</label>
                                    <input v-model="form.title" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.title }" />
                                    <p v-if="form.errors.title" class="text-[11px] text-red-500">{{ form.errors.title }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Type</label>
                                    <select v-model="form.type" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Severity</label>
                                    <select v-model="form.severity" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        <option v-for="opt in severityOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Cost (USD)</label>
                                    <input v-model.number="form.cost" type="number" step="0.01" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Scheduled Date</label>
                                    <input v-model="form.scheduled_date" type="date" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.scheduled_date }" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Odometer (km)</label>
                                    <input v-model.number="form.odometer" type="number" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Description</label>
                                    <textarea v-model="form.description" rows="2" class="rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-3 border-t border-border px-6 py-4">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="showModal = false">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="h-9 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">{{ form.processing ? 'Saving…' : editingId ? 'Save Changes' : 'Create Record' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition duration-150" leave-to-class="opacity-0" leave-active-class="transition duration-150">
                <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="deleteTarget = null" />
                    <div class="relative z-10 mx-4 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-2xl">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30"><Trash2 class="h-5 w-5 text-red-600 dark:text-red-400" /></div>
                        <h2 class="font-display mt-4 font-semibold text-foreground">Delete record?</h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ deleteTarget.reference }} will be permanently removed.</p>
                        <div class="mt-5 flex items-center justify-end gap-3">
                            <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="deleteTarget = null">Cancel</button>
                            <button type="button" class="h-9 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white hover:bg-red-400" @click="confirmDelete">Delete</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
