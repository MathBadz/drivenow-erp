<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, ChevronDown, Globe2, ImageUp, Palette, Trash2, UploadCloud } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { SystemSettings } from '@/types';

const props = defineProps<{ settings: SystemSettings }>();

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(props.settings.logo_url || null);
const logoError = ref(false);

const form = useForm({
    business_name: props.settings.business_name ?? '',
    business_email: props.settings.business_email ?? '',
    business_phone: props.settings.business_phone ?? '',
    business_address: props.settings.business_address ?? '',
    business_website: props.settings.business_website ?? '',
    business_description: props.settings.business_description ?? '',
    logo: null as File | null,
    remove_logo: false,
    currency: props.settings.currency ?? 'USD',
    currency_symbol: props.settings.currency_symbol ?? '$',
    timezone: props.settings.timezone ?? 'Asia/Manila',
});

function onLogoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    form.logo = file;
    form.remove_logo = false;
    logoError.value = false;
    previewUrl.value = file ? URL.createObjectURL(file) : props.settings.logo_url || null;
}

function removeLogo() {
    form.logo = null;
    form.remove_logo = true;
    previewUrl.value = null;
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    form.put('/settings/system', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.logo = null;
            form.remove_logo = false;
        },
    });
}

const inputCls =
    'h-10 rounded-lg border border-input bg-background px-3 text-sm transition-colors focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none';
const selectCls = inputCls + ' appearance-none pr-9';

// Common ISO 4217 currencies with their display symbols. Picking one fills the
// symbol automatically, but the symbol stays editable.
const currencies = [
    { code: 'PHP', symbol: '₱', label: 'Philippine Peso' },
    { code: 'USD', symbol: '$', label: 'US Dollar' },
    { code: 'EUR', symbol: '€', label: 'Euro' },
    { code: 'GBP', symbol: '£', label: 'British Pound' },
    { code: 'JPY', symbol: '¥', label: 'Japanese Yen' },
    { code: 'AUD', symbol: '$', label: 'Australian Dollar' },
    { code: 'SGD', symbol: '$', label: 'Singapore Dollar' },
];

const timezones = [
    'Asia/Manila',
    'Asia/Singapore',
    'Asia/Hong_Kong',
    'Asia/Tokyo',
    'Asia/Dubai',
    'Europe/London',
    'America/New_York',
    'America/Los_Angeles',
    'UTC',
];

// Only auto-fill the symbol while the admin hasn't hand-edited it, so a custom
// symbol (e.g. 'S$') is never clobbered by re-selecting a currency.
let lastAutoSymbol = form.currency_symbol;
function onCurrencyChange() {
    const match = currencies.find((c) => c.code === form.currency);
    if (match && (form.currency_symbol === lastAutoSymbol || form.currency_symbol.trim() === '')) {
        form.currency_symbol = match.symbol;
        lastAutoSymbol = match.symbol;
    }
}
</script>

<template>
    <Head title="System Settings" />

    <div class="space-y-6">
        <Heading
            title="System Settings"
            description="Branding and configuration broadcast to every service across the platform."
        />

        <form class="space-y-6" @submit.prevent="submit">
            <!-- Business information -->
            <section class="rounded-2xl border border-border bg-card p-6 shadow-card">
                <div class="flex items-center gap-2">
                    <Building2 class="h-4 w-4 text-amber-500" />
                    <h3 class="font-display text-sm font-semibold text-foreground">Business Information</h3>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Business Name</label>
                        <input v-model="form.business_name" type="text" :class="inputCls" />
                        <p v-if="form.errors.business_name" class="text-[11px] text-red-500">{{ form.errors.business_name }}</p>
                    </div>
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Email</label>
                        <input v-model="form.business_email" type="email" :class="inputCls" />
                        <p v-if="form.errors.business_email" class="text-[11px] text-red-500">{{ form.errors.business_email }}</p>
                    </div>
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Phone</label>
                        <input v-model="form.business_phone" type="text" :class="inputCls" />
                    </div>
                    <div class="grid gap-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Address</label>
                        <input v-model="form.business_address" type="text" :class="inputCls" />
                    </div>
                    <div class="grid gap-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Description</label>
                        <textarea v-model="form.business_description" rows="2" class="rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none" />
                    </div>
                </div>
            </section>

            <!-- Branding (logo upload) -->
            <section class="rounded-2xl border border-border bg-card p-6 shadow-card">
                <div class="flex items-center gap-2">
                    <Palette class="h-4 w-4 text-amber-500" />
                    <h3 class="font-display text-sm font-semibold text-foreground">Branding</h3>
                </div>
                <p class="mt-1 text-xs text-muted-foreground">Upload a logo — it appears across every service, the client portal and login screens.</p>

                <div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-center">
                    <!-- Preview -->
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-border bg-muted/40">
                        <img v-if="previewUrl && !logoError" :src="previewUrl" alt="Logo preview" class="h-full w-full object-contain" @error="logoError = true" />
                        <ImageUp v-else class="h-8 w-8 text-muted-foreground/40" />
                    </div>

                    <div class="flex-1">
                        <input ref="fileInput" type="file" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="hidden" @change="onLogoChange" />
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-border px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted active:scale-95" @click="fileInput?.click()">
                                <UploadCloud class="h-4 w-4" /> {{ previewUrl ? 'Change logo' : 'Upload logo' }}
                            </button>
                            <button v-if="previewUrl" type="button" class="inline-flex h-9 items-center gap-2 rounded-lg border border-red-200 px-4 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 active:scale-95 dark:border-red-800/50 dark:hover:bg-red-900/10" @click="removeLogo">
                                <Trash2 class="h-4 w-4" /> Remove
                            </button>
                        </div>
                        <p class="mt-2 text-[11px] text-muted-foreground">PNG, JPG, SVG or WebP up to 2 MB. Square works best.</p>
                        <p v-if="form.errors.logo" class="mt-1 text-[11px] text-red-500">{{ form.errors.logo }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-1.5">
                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Website URL</label>
                    <input v-model="form.business_website" type="url" placeholder="https://…" :class="inputCls" />
                    <p v-if="form.errors.business_website" class="text-[11px] text-red-500">{{ form.errors.business_website }}</p>
                </div>
            </section>

            <!-- Localization -->
            <section class="rounded-2xl border border-border bg-card p-6 shadow-card">
                <div class="flex items-center gap-2">
                    <Globe2 class="h-4 w-4 text-amber-500" />
                    <h3 class="font-display text-sm font-semibold text-foreground">Localization</h3>
                </div>
                <p class="mt-1 text-xs text-muted-foreground">Currency drives money formatting across every service; the timezone is used for timestamps.</p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1.4fr]">
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Currency</label>
                        <div class="relative">
                            <select v-model="form.currency" :class="selectCls" class="w-full" @change="onCurrencyChange">
                                <option v-for="c in currencies" :key="c.code" :value="c.code">{{ c.code }} — {{ c.label }}</option>
                            </select>
                            <ChevronDown class="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        </div>
                        <p v-if="form.errors.currency" class="text-[11px] text-red-500">{{ form.errors.currency }}</p>
                    </div>
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Symbol</label>
                        <input v-model="form.currency_symbol" type="text" maxlength="4" :class="inputCls" class="text-center" />
                        <p class="text-[11px] text-muted-foreground">Shown before amounts.</p>
                    </div>
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Timezone</label>
                        <div class="relative">
                            <select v-model="form.timezone" :class="selectCls" class="w-full">
                                <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz.replace('_', ' ') }}</option>
                            </select>
                            <ChevronDown class="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Save changes
                </Button>
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-show="form.recentlySuccessful" class="text-sm text-emerald-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </div>
</template>
