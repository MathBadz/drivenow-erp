<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Printer,
    RotateCcw,
    Wallet,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate } from '@/lib/format';
import type { Invoice, Option, SystemSettings } from '@/types';

const props = defineProps<{
    invoice: { data: Invoice };
    methodOptions: Option[];
}>();

const page = usePage<{ settings: SystemSettings }>();
const settings = computed(() => page.props.settings);
const inv = computed(() => props.invoice.data);

const statusBadge = computed(
    () =>
        ({
            unpaid: 'bg-slate-100 text-slate-600 dark:bg-slate-800/40 dark:text-slate-400',
            partial: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            paid: 'badge-available',
            overdue: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            refunded: 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
        })[inv.value.status] ?? 'badge-inactive',
);

const canPay = computed(() => inv.value.balance > 0 && inv.value.status !== 'refunded');

/* payment modal */
const showPay = ref(false);
const payForm = useForm({ method: 'cash', amount: inv.value.balance, reference: '' });
function openPay() {
    payForm.reset();
    payForm.amount = inv.value.balance;
    payForm.clearErrors();
    showPay.value = true;
}
function submitPay() {
    payForm.post(`/invoices/${inv.value.id}/payments`, { preserveScroll: true, onSuccess: () => (showPay.value = false) });
}
function refund() {
    router.post(`/invoices/${inv.value.id}/refund`, {}, { preserveScroll: true });
}
function printReceipt() {
    window.print();
}
</script>

<template>
    <Head :title="inv.invoice_number" />

    <AppLayout :breadcrumbs="[{ title: 'Invoices', href: '/invoices' }, { title: inv.invoice_number, href: `/invoices/${inv.id}` }]">
        <div class="mx-auto flex h-full w-full max-w-3xl flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between print:hidden">
                <Link href="/invoices" class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
                    <ArrowLeft class="h-4 w-4" /> Back to invoices
                </Link>
                <div class="flex gap-2">
                    <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-4 text-sm font-medium text-foreground hover:bg-muted" @click="printReceipt">
                        <Printer class="h-4 w-4" /> Print
                    </button>
                    <button v-if="inv.amount_paid > 0 && inv.status !== 'refunded'" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-violet-200 px-4 text-sm font-medium text-violet-600 hover:bg-violet-50 dark:border-violet-800/50 dark:hover:bg-violet-900/10" @click="refund">
                        <RotateCcw class="h-4 w-4" /> Refund
                    </button>
                    <button v-if="canPay" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 hover:bg-amber-400" @click="openPay">
                        <Wallet class="h-4 w-4" /> Record Payment
                    </button>
                </div>
            </div>

            <!-- Receipt / invoice document -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
                <div class="flex items-start justify-between gap-4 border-b border-border bg-muted/30 px-8 py-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500 text-amber-950"><Wallet class="h-5 w-5" /></div>
                            <p class="font-display text-lg font-bold text-foreground">{{ settings?.business_name }}</p>
                        </div>
                        <p class="mt-2 max-w-xs text-xs text-muted-foreground">{{ settings?.business_address }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-display text-xl font-bold text-foreground">INVOICE</p>
                        <p class="font-mono text-sm text-muted-foreground">{{ inv.invoice_number }}</p>
                        <span class="badge mt-2" :class="statusBadge">{{ inv.status_label }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 px-8 py-6">
                    <div>
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">Bill To</p>
                        <p class="mt-1 text-sm font-medium text-foreground">{{ inv.customer_name }}</p>
                        <p class="text-sm text-muted-foreground">{{ inv.customer_email }}</p>
                        <p v-if="inv.rental_reference" class="mt-1 font-mono text-xs text-muted-foreground">Rental: {{ inv.rental_reference }}</p>
                    </div>
                    <div class="text-right text-sm">
                        <p class="text-muted-foreground">Issued: <span class="text-foreground">{{ formatDate(inv.issued_at) }}</span></p>
                        <p class="text-muted-foreground">Due: <span class="text-foreground">{{ formatDate(inv.due_date) }}</span></p>
                    </div>
                </div>

                <!-- Charges breakdown -->
                <div class="px-8 pb-6">
                    <div class="overflow-hidden rounded-xl border border-border">
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-border">
                                <tr><td class="px-4 py-2.5 text-muted-foreground">Subtotal</td><td class="px-4 py-2.5 text-right text-foreground">{{ formatCurrency(inv.subtotal) }}</td></tr>
                                <tr v-if="inv.penalty > 0"><td class="px-4 py-2.5 text-muted-foreground">Penalty</td><td class="px-4 py-2.5 text-right text-red-600 dark:text-red-400">+ {{ formatCurrency(inv.penalty) }}</td></tr>
                                <tr v-if="inv.discount > 0"><td class="px-4 py-2.5 text-muted-foreground">Discount</td><td class="px-4 py-2.5 text-right text-emerald-600 dark:text-emerald-400">− {{ formatCurrency(inv.discount) }}</td></tr>
                                <tr class="bg-muted/40 font-semibold"><td class="px-4 py-3 text-foreground">Total</td><td class="font-display px-4 py-3 text-right text-foreground">{{ formatCurrency(inv.total) }}</td></tr>
                                <tr><td class="px-4 py-2.5 text-muted-foreground">Amount Paid</td><td class="px-4 py-2.5 text-right text-emerald-600 dark:text-emerald-400">{{ formatCurrency(inv.amount_paid) }}</td></tr>
                                <tr class="font-semibold"><td class="px-4 py-2.5 text-foreground">Balance Due</td><td class="px-4 py-2.5 text-right" :class="inv.balance > 0 ? 'text-red-600 dark:text-red-400' : 'text-foreground'">{{ formatCurrency(inv.balance) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment history -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card print:hidden">
                <div class="border-b border-border px-5 py-3.5"><h2 class="font-display text-sm font-semibold text-foreground">Payment History</h2></div>
                <ul class="divide-y divide-border">
                    <li v-for="p in inv.payments ?? []" :key="p.id" class="flex items-center gap-3 px-5 py-3">
                        <span class="badge badge-available shrink-0">{{ p.method_label }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm text-foreground">{{ p.reference ?? 'Payment' }}</p>
                            <p class="truncate text-xs text-muted-foreground">{{ formatDate(p.paid_at) }}</p>
                        </div>
                        <span class="text-sm font-medium text-foreground">{{ formatCurrency(p.amount) }}</span>
                    </li>
                    <li v-if="(inv.payments?.length ?? 0) === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">No payments recorded yet.</li>
                </ul>
            </div>
        </div>

        <!-- Payment modal -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition duration-150" leave-to-class="opacity-0" leave-active-class="transition duration-150">
                <div v-if="showPay" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showPay = false" />
                    <div class="relative z-10 mx-4 w-full max-w-md rounded-2xl border border-border bg-card shadow-2xl">
                        <div class="flex items-center justify-between border-b border-border px-6 py-4">
                            <h2 class="font-display font-semibold text-foreground">Record Payment</h2>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted" @click="showPay = false"><X class="h-4 w-4" /></button>
                        </div>
                        <form class="p-6" @submit.prevent="submitPay">
                            <div class="grid gap-4">
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Method</label>
                                    <select v-model="payForm.method" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                                        <option v-for="opt in methodOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                    </select>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Amount</label>
                                    <input v-model.number="payForm.amount" type="number" step="0.01" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': payForm.errors.amount }" />
                                    <p v-if="payForm.errors.amount" class="text-[11px] text-red-500">{{ payForm.errors.amount }}</p>
                                    <p class="text-[11px] text-muted-foreground">Balance due: {{ formatCurrency(inv.balance) }}</p>
                                </div>
                                <div class="grid gap-1.5">
                                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Reference (optional)</label>
                                    <input v-model="payForm.reference" type="text" class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                                </div>
                            </div>
                            <div class="mt-5 flex items-center justify-end gap-3">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="showPay = false">Cancel</button>
                                <button type="submit" :disabled="payForm.processing" class="h-9 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">Record</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
