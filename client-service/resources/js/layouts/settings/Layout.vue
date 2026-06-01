<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Palette, ShieldCheck, User, type LucideIcon } from 'lucide-vue-next';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';

type NavEntry = NavItem & { icon: LucideIcon; desc: string };

const items: NavEntry[] = [
    { title: 'Profile', href: editProfile(), icon: User, desc: 'Name & email' },
    { title: 'Security', href: editSecurity(), icon: ShieldCheck, desc: 'Password & 2FA' },
    { title: 'Appearance', href: editAppearance(), icon: Palette, desc: 'Theme & display' },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-6">
        <div class="mb-6">
            <h1 class="font-display text-2xl font-semibold tracking-tight text-foreground">Account Settings</h1>
            <p class="mt-0.5 text-sm text-muted-foreground">Manage your profile and preferences.</p>
        </div>

        <div class="flex flex-col gap-8 lg:flex-row">
            <aside class="w-full shrink-0 lg:w-64">
                <nav class="flex flex-col gap-1" aria-label="Settings">
                    <Link
                        v-for="item in items"
                        :key="toUrl(item.href)"
                        :href="item.href"
                        class="group flex items-center gap-3 rounded-xl border px-3 py-2.5 transition-all"
                        :class="
                            isCurrentOrParentUrl(item.href)
                                ? 'border-amber-300 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20'
                                : 'border-transparent hover:border-border hover:bg-muted/60'
                        "
                    >
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition-colors"
                            :class="isCurrentOrParentUrl(item.href) ? 'bg-amber-500 text-amber-950' : 'bg-muted text-muted-foreground group-hover:text-foreground'"
                        >
                            <component :is="item.icon" class="h-4 w-4" />
                        </span>
                        <span class="min-w-0 leading-tight">
                            <span class="block text-sm font-medium" :class="isCurrentOrParentUrl(item.href) ? 'text-amber-700 dark:text-amber-300' : 'text-foreground'">{{ item.title }}</span>
                            <span class="block truncate text-xs text-muted-foreground">{{ item.desc }}</span>
                        </span>
                    </Link>
                </nav>
            </aside>

            <div class="min-w-0 flex-1">
                <section class="max-w-2xl space-y-10">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
