<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleSlash,
    Clock,
    Eye,
    LayoutGrid,
    Plus,
    Receipt,
    RotateCcw,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import type { Invoice, InvoiceFilters, InvoiceStats, Paginated } from '@/types';

const props = defineProps<{
    invoices: Paginated<Invoice>;
    stats: InvoiceStats;
    filters: InvoiceFilters;
}>();

const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
let debounce: ReturnType<typeof setTimeout> | undefined;

function pushFilters() {
    router.get('/invoices', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true, replace: true });
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
    { key: 'unpaid', label: 'Unpaid', count: props.stats.unpaid, icon: Clock, cls: 'text-slate-400' },
    { key: 'partial', label: 'Partial', count: props.stats.partial, icon: CircleSlash, cls: 'text-amber-500' },
    { key: 'paid', label: 'Paid', count: props.stats.paid, icon: CheckCircle2, cls: 'text-emerald-500' },
    { key: 'overdue', label: 'Overdue', count: props.stats.overdue, icon: AlertTriangle, cls: 'text-red-500' },
    { key: 'refunded', label: 'Refunded', count: props.stats.refunded, icon: RotateCcw, cls: 'text-violet-500' },
]);

const statusBadge = (s: string) =>
    ({
        unpaid: 'bg-slate-100 text-slate-600 dark:bg-slate-800/40 dark:text-slate-400',
        partial: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        paid: 'badge-available',
        overdue: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        refunded: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
    })[s] ?? 'badge-inactive';

/* create modal */
const showModal = ref(false);
const today = new Date().toISOString().slice(0, 10);
const dueDefault = new Date(Date.now() + 14 * 86400000).toISOString().slice(0, 10);
const form = useForm({
    customer_name: '', customer_email: '', rental_reference: '',
    subtotal: 0, penalty: 0, discount: 0, due_date: dueDefault, notes: '',
});
const totalPreview = computed(() => Math.max(0, Number(form.subtotal) + Number(form.penalty) - Number(form.discount)));

function openCreate() {
    form.reset();
    form.due_date = dueDefault;
    form.clearErrors();
    showModal.value = true;
}
function submit() {
    form.post('/invoices', { preserveScroll: true, onSuccess: () => (showModal.value = false) });
}

const deleteTarget = ref<Invoice | null>(null);
function confirmDelete() {
    if (!deleteTarget.value) return;
    router.delete(`/invoices/${deleteTarget.value.id}`, { preserveScroll: true, onFinish: () => (deleteTarget.value = null) });
}
</script>

<template>
    <Head title="Invoices" />

    <AppLayout :breadcrumbs="[{ title: 'Invoices', href: '/invoices' }]">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="font-display text-2xl font-semibold tracking-tight text-foreground">Invoices</h1>
                    <p class="mt-0.5 text-sm text-muted-foreground">Generate invoices and track payments.</p>
                </div>
                <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400" @click="openCreate">
                    <Plus class="h-4 w-4" /> New Invoice
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
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
                        <input v-model="search" type="text" placeholder="Search invoice #, customer or rental…" class="h-8 w-full rounded-lg border border-input bg-background pr-3 pl-9 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                    </div>
                    <span class="ml-auto text-xs text-muted-foreground">{{ formatNumber(invoices.meta.total) }} invoices</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border bg-muted/40">
                                <th class="px-5 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Invoice</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Total</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Balance</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Due</th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Status</th>
                                <th class="px-5 py-3 text-right text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="inv in invoices.data" :key="inv.id" class="group transition-colors hover:bg-muted/30">
                                <td class="px-5 py-3.5"><span class="font-mono text-sm font-medium text-foreground">{{ inv.invoice_number }}</span></td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm font-medium text-foreground">{{ inv.customer_name }}</p>
                                    <p class="font-mono text-xs text-muted-foreground">{{ inv.rental_reference }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-sm font-medium text-foreground">{{ formatCurrency(inv.total) }}</td>
                                <td class="px-4 py-3.5 text-sm" :class="inv.balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-muted-foreground'">{{ formatCurrency(inv.balance) }}</td>
                                <td class="px-4 py-3.5 text-sm text-muted-foreground">{{ formatDate(inv.due_date) }}</td>
                                <td class="px-4 py-3.5"><span class="badge" :class="statusBadge(inv.status)">{{ inv.status_label }}</span></td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link :href="`/invoices/${inv.id}`" title="View" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-amber-300 hover:text-amber-600"><Eye class="h-3.5 w-3.5" /></Link>
                                        <button type="button" title="Delete" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:border-red-300 hover:text-red-500" @click="deleteTarget = inv"><Trash2 class="h-3.5 w-3.5" /></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="invoices.data.length === 0">
                                <td :colspan="7" class="px-5 py-16 text-center">
                                    <Receipt class="mx-auto h-8 w-8 text-muted-foreground/30" />
                                    <p class="mt-3 text-sm font-medium text-foreground">No invoices found</p>
                                    <p class="mt-1 text-xs text-muted-foreground">Try adjusting your search or filter criteria</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-border px-5 py-3">
                    <p class="text-xs text-muted-foreground">{{ invoices.meta.from ?? 0 }}–{{ invoices.meta.to ?? 0 }} of {{ invoices.meta.total }}</p>
                    <div class="flex items-center gap-1">
                        <Link v-if="invoices.links.prev" :href="invoices.links.prev" preserve-scroll class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300"><ChevronLeft class="h-4 w-4" /></Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40"><ChevronLeft class="h-4 w-4" /></span>
                        <span class="px-2 text-xs font-medium text-foreground">{{ invoices.meta.current_page }} / {{ invoices.meta.last_page }}</span>
                        <Link v-if="invoices.links.next" :href="invoices.links.next" preserve-scroll class="flex h-7 w-7 items-center justify-center rounded-md border border-border text-muted-foreground hover:border-amber-300"><ChevronRight class="h-4 w-4" /></Link>
                        <span v-else class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-md border border-border text-muted-foreground opacity-40"><ChevronRight class="h-4 w-4" /></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create modal -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition duration-150" leave-to-class="opacity-0" leave-active-class="transition duration-150">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false" />
                    <div class="relative z-10 mx-4 w-full max-w-xl rounded-2xl border border-border bg-card shadow-2xl">
                        <div class="flex items-center justify-between border-b border-border px-6 py-4">
                            <h2 class="font-display font-semibold text-foreground">New Invoice</h2>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted" @click="showModal = false"><X class="h-4 w-4" /></button>
                        </div>
                        <form class="max-h-[70vh] overflow-y-auto" @submit.prevent="submit">
                            <div class="grid gap-4 p-6 sm:grid-cols-2">
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Customer Name</label>
                                    <input v-model="form.customer_name" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.customer_name }" />
                                    <p v-if="form.errors.customer_name" class="text-[11px] text-red-500">{{ form.errors.customer_name }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Email</label>
                                    <input v-model="form.customer_email" type="email" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5 sm:col-span-2">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Rental Reference</label>
                                    <input v-model="form.rental_reference" type="text" placeholder="RNT-…" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Subtotal</label>
                                    <input v-model.number="form.subtotal" type="number" step="0.01" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.subtotal }" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Penalty</label>
                                    <input v-model.number="form.penalty" type="number" step="0.01" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Discount</label>
                                    <input v-model.number="form.discount" type="number" step="0.01" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Due Date</label>
                                    <input v-model="form.due_date" type="date" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': form.errors.due_date }" />
                                </div>
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 sm:col-span-2 dark:border-amber-800/50 dark:bg-amber-900/10">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-amber-700 dark:text-amber-300">Invoice Total</span>
                                        <span class="font-display text-lg font-bold text-amber-700 dark:text-amber-300">{{ formatCurrency(totalPreview) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-3 border-t border-border px-6 py-4">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="showModal = false">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="h-9 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">{{ form.processing ? 'Creating…' : 'Create Invoice' }}</button>
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
                        <h2 class="font-display mt-4 font-semibold text-foreground">Delete invoice?</h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ deleteTarget.invoice_number }} and its payments will be permanently removed.</p>
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
