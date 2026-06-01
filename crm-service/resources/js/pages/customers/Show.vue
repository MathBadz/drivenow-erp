<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Ban,
    CalendarCheck,
    Mail,
    MapPin,
    MessageSquarePlus,
    Phone,
    ShieldCheck,
    Star,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatCurrency, formatDate, formatNumber } from '@/lib/format';
import type { Customer, RentalHistoryItem } from '@/types';

const props = defineProps<{
    customer: { data: Customer };
    rentalHistory: RentalHistoryItem[];
}>();

const c = computed(() => props.customer.data);

const tierBadge = computed(
    () =>
        ({
            regular: 'bg-slate-100 text-slate-600 dark:bg-slate-800/40 dark:text-slate-400',
            silver: 'bg-slate-200 text-slate-700 dark:bg-slate-700/40 dark:text-slate-300',
            gold: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            platinum: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-400',
        })[c.value.tier] ?? 'bg-slate-100 text-slate-600',
);

const isBlacklisted = computed(() => c.value.status === 'blacklisted');

/* blacklist modal */
const showBlacklist = ref(false);
const blacklistForm = useForm({ reason: '' });
function submitBlacklist() {
    blacklistForm.post(`/customers/${c.value.id}/blacklist`, { preserveScroll: true, onSuccess: () => (showBlacklist.value = false) });
}
function unblacklist() {
    router.post(`/customers/${c.value.id}/unblacklist`, {}, { preserveScroll: true });
}

/* feedback modal */
const showFeedback = ref(false);
const feedbackForm = useForm({ rating: 5, comment: '' });
function submitFeedback() {
    feedbackForm.post(`/customers/${c.value.id}/feedback`, { preserveScroll: true, onSuccess: () => { showFeedback.value = false; feedbackForm.reset(); } });
}

const kpis = computed(() => [
    { label: 'Loyalty Points', value: formatNumber(c.value.loyalty_points) },
    { label: 'Total Rentals', value: formatNumber(c.value.total_rentals) },
    { label: 'Total Spent', value: formatCurrency(c.value.total_spent) },
]);
</script>

<template>
    <Head :title="c.name" />

    <AppLayout :breadcrumbs="[{ title: 'Customers', href: '/customers' }, { title: c.name, href: `/customers/${c.id}` }]">
        <div class="mx-auto flex min-h-full w-full max-w-5xl flex-1 flex-col gap-6 p-6">
            <Link href="/customers" class="inline-flex w-fit items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
                <ArrowLeft class="h-4 w-4" /> Back to customers
            </Link>

            <!-- Profile header -->
            <div class="rounded-2xl border border-border bg-card p-6 shadow-card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-lg font-bold text-amber-950">{{ c.name.split(' ').map((p) => p[0]).slice(0, 2).join('') }}</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="font-display text-2xl font-bold tracking-tight text-foreground">{{ c.name }}</h1>
                                <span class="badge" :class="tierBadge">{{ c.tier_label }}</span>
                                <span v-if="isBlacklisted" class="badge bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Blacklisted</span>
                            </div>
                            <p class="mt-0.5 text-sm text-muted-foreground">Customer since {{ formatDate(c.joined_at) }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-4 text-sm font-medium text-foreground hover:bg-muted" @click="showFeedback = true">
                            <MessageSquarePlus class="h-4 w-4" /> Feedback
                        </button>
                        <button v-if="!isBlacklisted" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800/50 dark:hover:bg-red-900/10" @click="showBlacklist = true">
                            <Ban class="h-4 w-4" /> Blacklist
                        </button>
                        <button v-else type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-emerald-500 px-4 text-sm font-semibold text-white hover:bg-emerald-400" @click="unblacklist">
                            <ShieldCheck class="h-4 w-4" /> Restore
                        </button>
                    </div>
                </div>

                <div v-if="isBlacklisted && c.blacklist_reason" class="mt-5 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-3.5 dark:border-red-800/50 dark:bg-red-900/10">
                    <Ban class="h-4 w-4 shrink-0 text-red-600 dark:text-red-400" />
                    <p class="text-sm text-red-700 dark:text-red-300">{{ c.blacklist_reason }}</p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 border-t border-border pt-5 sm:grid-cols-3">
                    <div v-for="kpi in kpis" :key="kpi.label">
                        <p class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase">{{ kpi.label }}</p>
                        <p class="font-display mt-1 text-xl font-bold text-foreground">{{ kpi.value }}</p>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 border-t border-border pt-5 text-sm text-muted-foreground">
                    <span class="flex items-center gap-1.5"><Mail class="h-4 w-4" /> {{ c.email }}</span>
                    <span v-if="c.phone" class="flex items-center gap-1.5"><Phone class="h-4 w-4" /> {{ c.phone }}</span>
                    <span v-if="c.address" class="flex items-center gap-1.5"><MapPin class="h-4 w-4" /> {{ c.address }}, {{ c.city }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Rental history -->
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h2 class="font-display text-sm font-semibold text-foreground">Rental History</h2>
                        <CalendarCheck class="h-4 w-4 text-muted-foreground" />
                    </div>
                    <ul class="divide-y divide-border">
                        <li v-for="r in rentalHistory" :key="r.id" class="flex items-center gap-3 px-5 py-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-foreground">{{ r.vehicle_name }}</p>
                                <p class="truncate text-xs text-muted-foreground">{{ r.reference }} · {{ formatDate(r.pickup_date) }}</p>
                            </div>
                            <span class="text-sm font-medium text-foreground">{{ formatCurrency(r.total) }}</span>
                        </li>
                        <li v-if="rentalHistory.length === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">No rental history available.</li>
                    </ul>
                </div>

                <!-- Activity log -->
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                    <div class="border-b border-border px-5 py-3.5"><h2 class="font-display text-sm font-semibold text-foreground">Activity Log</h2></div>
                    <ol class="space-y-0 px-5 py-4">
                        <li v-for="(a, i) in c.activities ?? []" :key="a.id" class="flex gap-3">
                            <div class="flex flex-col items-center">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-amber-500" />
                                <span v-if="i < (c.activities?.length ?? 0) - 1" class="w-0.5 flex-1 bg-border" />
                            </div>
                            <div class="pb-4">
                                <p class="text-sm text-foreground">{{ a.description }}</p>
                                <p class="text-xs text-muted-foreground">{{ formatDate(a.created_at) }}</p>
                            </div>
                        </li>
                        <li v-if="(c.activities?.length ?? 0) === 0" class="py-6 text-center text-sm text-muted-foreground">No activity yet.</li>
                    </ol>
                </div>
            </div>

            <!-- Feedback -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-card">
                <div class="border-b border-border px-5 py-3.5"><h2 class="font-display text-sm font-semibold text-foreground">Feedback</h2></div>
                <ul class="divide-y divide-border">
                    <li v-for="f in c.feedback ?? []" :key="f.id" class="px-5 py-3.5">
                        <div class="flex items-center gap-1">
                            <Star v-for="n in 5" :key="n" class="h-3.5 w-3.5" :class="n <= f.rating ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/30'" />
                            <span class="ml-2 text-xs text-muted-foreground">{{ formatDate(f.created_at) }}</span>
                        </div>
                        <p v-if="f.comment" class="mt-1 text-sm text-foreground">{{ f.comment }}</p>
                    </li>
                    <li v-if="(c.feedback?.length ?? 0) === 0" class="px-5 py-10 text-center text-sm text-muted-foreground">No feedback yet.</li>
                </ul>
            </div>
        </div>

        <!-- Blacklist modal -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition duration-150" leave-to-class="opacity-0" leave-active-class="transition duration-150">
                <div v-if="showBlacklist" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showBlacklist = false" />
                    <div class="relative z-10 mx-4 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-2xl">
                        <div class="flex items-center justify-between">
                            <h2 class="font-display font-semibold text-foreground">Blacklist Customer</h2>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted" @click="showBlacklist = false"><X class="h-4 w-4" /></button>
                        </div>
                        <form class="mt-4" @submit.prevent="submitBlacklist">
                            <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Reason</label>
                            <textarea v-model="blacklistForm.reason" rows="3" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" :class="{ 'border-red-400': blacklistForm.errors.reason }" />
                            <p v-if="blacklistForm.errors.reason" class="mt-1 text-[11px] text-red-500">{{ blacklistForm.errors.reason }}</p>
                            <div class="mt-5 flex items-center justify-end gap-3">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="showBlacklist = false">Cancel</button>
                                <button type="submit" :disabled="blacklistForm.processing" class="h-9 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white hover:bg-red-400 disabled:opacity-60">Blacklist</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Feedback modal -->
        <Teleport to="body">
            <Transition enter-from-class="opacity-0" enter-active-class="transition duration-150" leave-to-class="opacity-0" leave-active-class="transition duration-150">
                <div v-if="showFeedback" class="fixed inset-0 z-50 flex items-center justify-center">
                    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showFeedback = false" />
                    <div class="relative z-10 mx-4 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-2xl">
                        <div class="flex items-center justify-between">
                            <h2 class="font-display font-semibold text-foreground">Add Feedback</h2>
                            <button type="button" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted" @click="showFeedback = false"><X class="h-4 w-4" /></button>
                        </div>
                        <form class="mt-4" @submit.prevent="submitFeedback">
                            <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Rating</label>
                            <div class="mt-1.5 flex items-center gap-1">
                                <button v-for="n in 5" :key="n" type="button" @click="feedbackForm.rating = n">
                                    <Star class="h-6 w-6" :class="n <= feedbackForm.rating ? 'fill-amber-400 text-amber-400' : 'text-muted-foreground/30'" />
                                </button>
                            </div>
                            <label class="mt-4 block text-xs font-semibold tracking-wider text-muted-foreground uppercase">Comment</label>
                            <textarea v-model="feedbackForm.comment" rows="3" class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                            <div class="mt-5 flex items-center justify-end gap-3">
                                <button type="button" class="h-9 rounded-lg border border-border px-4 text-sm font-medium text-muted-foreground hover:bg-muted" @click="showFeedback = false">Cancel</button>
                                <button type="submit" :disabled="feedbackForm.processing" class="h-9 rounded-lg bg-amber-500 px-5 text-sm font-semibold text-amber-950 hover:bg-amber-400 disabled:opacity-60">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
