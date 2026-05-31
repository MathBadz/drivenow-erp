<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Building2, Palette, Globe2 } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import type { SystemSettings } from '@/types';

const props = defineProps<{ settings: SystemSettings }>();

const form = useForm({
    business_name: props.settings.business_name ?? '',
    business_email: props.settings.business_email ?? '',
    business_phone: props.settings.business_phone ?? '',
    business_address: props.settings.business_address ?? '',
    business_website: props.settings.business_website ?? '',
    business_description: props.settings.business_description ?? '',
    logo_url: props.settings.logo_url ?? '',
    currency: props.settings.currency ?? 'USD',
    currency_symbol: props.settings.currency_symbol ?? '$',
    timezone: props.settings.timezone ?? 'Asia/Manila',
});

function submit() {
    form.put('/settings/system', { preserveScroll: true });
}
</script>

<template>
    <Head title="System Settings" />

    <div class="space-y-8">
        <Heading
            title="System Settings"
            description="Branding and configuration broadcast to every service across the platform."
        />

        <form class="space-y-8" @submit.prevent="submit">
            <!-- Business information -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <Building2 class="h-4 w-4 text-amber-500" />
                    <h3 class="font-display text-sm font-semibold text-foreground">
                        Business Information
                    </h3>
                </div>

                <div class="grid gap-1.5">
                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                        Business Name
                    </label>
                    <input
                        v-model="form.business_name"
                        type="text"
                        class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                        :class="{ 'border-red-400': form.errors.business_name }"
                    />
                    <p v-if="form.errors.business_name" class="text-[11px] text-red-500">
                        {{ form.errors.business_name }}
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Email
                        </label>
                        <input
                            v-model="form.business_email"
                            type="email"
                            class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                            :class="{ 'border-red-400': form.errors.business_email }"
                        />
                        <p v-if="form.errors.business_email" class="text-[11px] text-red-500">
                            {{ form.errors.business_email }}
                        </p>
                    </div>
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Phone
                        </label>
                        <input
                            v-model="form.business_phone"
                            type="text"
                            class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                        />
                    </div>
                </div>

                <div class="grid gap-1.5">
                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                        Address
                    </label>
                    <input
                        v-model="form.business_address"
                        type="text"
                        class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                    />
                </div>

                <div class="grid gap-1.5">
                    <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                        Description
                    </label>
                    <textarea
                        v-model="form.business_description"
                        rows="2"
                        class="rounded-lg border border-input bg-background px-3 py-2 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                    />
                </div>
            </section>

            <!-- Branding -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <Palette class="h-4 w-4 text-amber-500" />
                    <h3 class="font-display text-sm font-semibold text-foreground">
                        Branding
                    </h3>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Website URL
                        </label>
                        <input
                            v-model="form.business_website"
                            type="url"
                            placeholder="https://…"
                            class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                            :class="{ 'border-red-400': form.errors.business_website }"
                        />
                        <p v-if="form.errors.business_website" class="text-[11px] text-red-500">
                            {{ form.errors.business_website }}
                        </p>
                    </div>
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Logo URL
                        </label>
                        <input
                            v-model="form.logo_url"
                            type="url"
                            placeholder="https://…"
                            class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                            :class="{ 'border-red-400': form.errors.logo_url }"
                        />
                        <p v-if="form.errors.logo_url" class="text-[11px] text-red-500">
                            {{ form.errors.logo_url }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Localization -->
            <section class="space-y-4">
                <div class="flex items-center gap-2">
                    <Globe2 class="h-4 w-4 text-amber-500" />
                    <h3 class="font-display text-sm font-semibold text-foreground">
                        Localization
                    </h3>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Currency
                        </label>
                        <input
                            v-model="form.currency"
                            type="text"
                            class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                        />
                    </div>
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Symbol
                        </label>
                        <input
                            v-model="form.currency_symbol"
                            type="text"
                            class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                        />
                    </div>
                    <div class="grid gap-1.5">
                        <label class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                            Timezone
                        </label>
                        <input
                            v-model="form.timezone"
                            type="text"
                            class="h-9 rounded-lg border border-input bg-background px-3 text-sm focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none"
                        />
                    </div>
                </div>
            </section>

            <div class="flex items-center gap-3 border-t border-border pt-5">
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
                    <p v-show="form.recentlySuccessful" class="text-sm text-emerald-600">
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </div>
</template>
