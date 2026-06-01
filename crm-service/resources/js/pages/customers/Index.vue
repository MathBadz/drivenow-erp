<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Ban,
    ChevronLeft,
    ChevronRight,
    Eye,
    LayoutGrid,
    Pencil,
    Plus,
    Search,
    Trash2,
    UserCheck,
    UserMinus,
    UsersRound,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatNumber } from '@/lib/format';
import type { Customer, CustomerStats, CustomerFilters, Option, Paginated } from '@/types';

const props = defineProps<{
    customers: Paginated<Customer>;
    stats: CustomerStats;
    filters: CustomerFilters;
    tierOptions: Option[];
}>();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const tierFilter = ref(props.filters.tier ?? '');
let debounce: ReturnType<typeof setTimeout> | undefined;

function pushFilters() {
    router.get('/customers', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        tier: tierFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
}

watch(search, () => {
    clearTimeout(debounce);
    debounce = setTimeout(pushFilters, 300);
});
watch(tierFilter, pushFilters);

function applyStatusFilter(value: string) {
    clearTimeout(debounce);
    statusFilter.value = value;
    pushFilters();
}

const filterCards = computed(() => [
    { key: '', label: 'Total', count: props.stats.total, icon: LayoutGrid, cls: 'text-blue-500' },
    { key: 'active', label: 'Active', count: props.stats.active, icon: UserCheck, cls: 'text-emerald-500' },
    { key: 'inactive', label: 'Inactive', count: props.stats.inactive, icon: UserMinus, cls: 'text-slate-400' },
    { key: 'blacklisted', label: 'Blacklisted', count: props.stats.blacklisted, icon: Ban, cls: 'text-red-500' },
]);

const statusBadge = (s: string) =>
    ({
        active: 'badge-available',
        inactive: 'badge-inactive',
        blacklisted: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    })[s] ?? 'badge-inactive';

const tierBadge = (tier: string) =>
    ({
        regular: 'bg-slate-100 text-slate-600 dark:bg-slate-800/40 dark:text-slate-400',
        silver: 'bg-slate-200 text-slate-700 dark:bg-slate-700/40 dark:text-slate-300',
        gold: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        platinum: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
    })[tier] ?? 'bg-slate-100 text-slate-600';

/* modal */
const showModal = ref(false);
const editingId = ref<number | null>(null);
const form = useForm({
    name: '', email: '', phone: '', address: '', city: 'Bacolod City',
    tier: 'regular', loyalty_points: 0, notes: '',
});

function openCreate() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
}
function openEdit(c: Customer) {
    editingId.value = c.id;
    form.clearErrors();
    form.name = c.name; form.email = c.email; form.phone = c.phone ?? '';
    form.address = c.address ?? ''; form.city = c.city ?? '';
    form.tier = c.tier; form.loyalty_points = c.loyalty_points; form.notes = c.notes ?? '';
    showModal.value = true;
}
function submit() {
    const opts = { preserveScroll: true, onSuccess: () => (showModal.value = false) };
    if (editingId.value) form.put(`/customers/${editingId.value}`, opts);
    else form.post('/customers', opts);
}

const deleteTarget = ref<Customer | null>(null);
function confirmDelete() {
    if (!deleteTarget.value) return;
    router.delete(`/customers/${deleteTarget.value.id}`, { preserveScroll: true, onFinish: () => (deleteTarget.value = null) });
}
</script>

<template>
    <Head title="Customers" />

    <AppLayout :breadcrumbs="[{ title: 'Customers', href: '/customers' }]">
        <div class="flex min-h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-display text-2xl font-semibold tracking-tight text-foreground">Customers</h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">Manage customer profiles, loyalty and standing.</p>
                </div>
                <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400" @click="openCreate">
                    <Plus class="h-4 w-4" /> Add Customer
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <button v-for="card in filterCards" :key="card.key" type="button" class="rounded-xl border p-4 text-left shadow-card transition-all" :class="(card.key === '' && !statusFilter) || statusFilter === card.key ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20' : 'border-border bg-card hover:border-amber-200'" @click="applyStatusFilter(card.key)">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ card.label }}</p>
                        <component :is="card.icon" class="h-4 w-4 shrink-0" :class="card.cls" />
                    </div>
                    <p class="font-display mt-1.5 text-2xl font-bold text-foreground">{{ formatNumber(card.count) }}</p>
                </button>
            </div>

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                <div class="flex flex-wrap items-center gap-3 border-b border-border px-5 py-3.5">
                    <div class="relative min-w-[220px] flex-1">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input v-model="search" type="text" placeholder="Search name, email or phone…" class="h-8 w-full rounded-lg border border-input bg-background pr-3 pl-9 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                    </div>
                    <select v-model="tierFilter" class="h-8 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                        <option value="">All Tiers</option>
                        <option v-for="opt in tierOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <span class="ml-auto text-xs text-muted-foreground">{{ formatNumber(customers.meta.total) }} customers</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border bg-muted/40">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Tier</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Points</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Rentals</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Status</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="c in customers.data" :key="c.id" class="group transition-colors hover:bg-muted/30">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-500/15 text-xs font-bold text-amber-600">{{ c.name.split(' ').map((p) => p[0]).slice(0, 2).join('') }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-medium text-foreground">{{ c.name }}</p>
                                            <p class="truncate text-xs text-muted-foreground">{{ c.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5"><span class="badge" :class="tierBadge(c.tier)">{{ c.tier_label }}</span></td>
                                <td class="px-4 py-3.5 text-sm text-foreground">{{ formatNumber(c.loyalty_points) }}</td>
                                <td class="px-4 py-3.5 text-sm text-muted-foreground">{{ c.total_rentals }}</td>
                                <td class="px-4 py-3.5"><span class="badge" :class="statusBadge(c.status)">{{ c.status_label }}</span></td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/customers/${c.id}`" title="View" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-amber-300 hover:text-amber-600"><Eye class="h-3.5 w-3.5" /></Link>
                                        <button type="button" title="Edit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-amber-300 hover:text-amber-600" @click="openEdit(c)"><Pencil class="h-3.5 w-3.5" /></button>
                                        <button type="button" title="Delete" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-red-300 hover:text-red-500" @click="deleteTarget = c"><Trash2 class="h-3.5 w-3.5" /></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="customers.data.length === 0">
                                <td :colspan="6" class="px-5 py-16 text-center">
                                    <UsersRound class="mx-auto h-8 w-8 text-muted-foreground/30" />
                                    <p class="mt-3 text-sm font-medium text-foreground">No customers found</p>
                                    <p class="mt-1 text-xs text-muted-foreground">Try adjusting your search or filter criteria</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-border px-5 py-3">
                    <p class="text-xs text-muted-foreground">{{ customers.meta.from ?? 0 }}–{{ customers.meta.to ?? 0 }} of {{ customers.meta.total }}</p>
                    <div class="flex items-center gap-1">
                        <Link v-if="customers.links.prev" :href="customers.links.prev" preserve-scroll class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300"><ChevronLeft class="h-4 w-4" /></Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40"><ChevronLeft class="h-4 w-4" /></span>
                        <span class="px-2 text-xs font-medium text-foreground">{{ customers.meta.current_page }} / {{ customers.meta.last_page }}</span>
                        <Link v-if="customers.links.next" :href="customers.links.next" preserve-scroll class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300"><ChevronRight class="h-4 w-4" /></Link>
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
                    <div class="relative z-10 mx-4 w-full max-w-xl rounded-2xl border border-border bg-card shadow-2xl">
                        <div class="flex items-center justify-between border-b border-border px-6 py-4">
                            <h2 class="font-display font-semibold text-foreground">{{ editingId ? 'Edit Customer' : 'Add Customer' }}</h2>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted" @click="showModal = false"><X class="h-4 w-4" /></button>
                        </div>
                        <form class="max-h-[70vh] overflow-y-auto" @submit.prevent="submit">
                            <div class="grid gap-4 p-6 sm:grid-cols-2">
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Full Name</label>
                                    <input v-model="form.name" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.name }" />
                                    <p v-if="form.errors.name" class="text-[11px] text-red-500">{{ form.errors.name }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Email</label>
                                    <input v-model="form.email" type="email" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.email }" />
                                    <p v-if="form.errors.email" class="text-[11px] text-red-500">{{ form.errors.email }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Phone</label>
                                    <input v-model="form.phone" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Address</label>
                                    <input v-model="form.address" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">City</label>
                                    <input v-model="form.city" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Tier</label>
                                    <select v-model="form.tier" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        <option v-for="opt in tierOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Loyalty Points</label>
                                    <input v-model.number="form.loyalty_points" type="number" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Notes</label>
                                    <textarea v-model="form.notes" rows="2" class="rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-3 border-t border-border px-6 py-4">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="showModal = false">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="h-9 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">{{ form.processing ? 'Saving…' : editingId ? 'Save Changes' : 'Add Customer' }}</button>
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
                        <h2 class="font-display mt-4 font-semibold text-foreground">Delete customer?</h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ deleteTarget.name }} and all related history will be permanently removed.</p>
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
