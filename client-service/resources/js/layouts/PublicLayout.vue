<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Car,
    LayoutDashboard,
    LogOut,
    Mail,
    MapPin,
    Menu,
    Moon,
    Phone,
    Sun,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Toaster } from 'vue-sonner';
import { useAppearance } from '@/composables/useAppearance';
import type { SystemSettings, User } from '@/types';

const page = usePage<{ settings: SystemSettings; auth: { user: User | null } }>();
const settings = computed(() => page.props.settings);
const user = computed(() => page.props.auth?.user ?? null);
const businessName = computed(() => settings.value?.business_name ?? 'DriveNow');

const { resolvedAppearance, updateAppearance } = useAppearance();
function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}

const mobileOpen = ref(false);

const currentPath = computed(() => {
    try {
        return new URL(page.url, 'http://localhost').pathname;
    } catch {
        return page.url;
    }
});
const isActive = (href: string) => currentPath.value === href;

const navLinks = [
    { label: 'Home', href: '/' },
    { label: 'Browse Cars', href: '/vehicles' },
];

const year = new Date().getFullYear();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <!-- Navbar -->
        <header class="sticky top-0 z-40 border-b border-border bg-background/80 backdrop-blur-md">
            <div class="mx-auto flex h-16 w-full max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-amber-950 shadow-sm">
                        <Car class="h-5 w-5" />
                    </span>
                    <span class="font-display text-lg font-bold tracking-tight text-foreground">{{ businessName }}</span>
                </Link>

                <nav class="ml-6 hidden items-center gap-1 md:flex">
                    <Link
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                        :class="isActive(link.href) ? 'text-amber-600' : 'text-muted-foreground hover:text-foreground'"
                    >
                        {{ link.label }}
                    </Link>
                </nav>

                <div class="ml-auto flex items-center gap-2">
                    <button type="button" title="Toggle theme" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground" @click="toggleTheme">
                        <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4" />
                        <Moon v-else class="h-4 w-4" />
                    </button>

                    <template v-if="user">
                        <Link href="/dashboard" class="hidden items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground sm:flex">
                            <LayoutDashboard class="h-4 w-4" /> My Trips
                        </Link>
                        <Link href="/logout" method="post" as="button" class="hidden items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted sm:flex">
                            <LogOut class="h-4 w-4" /> Sign out
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="hidden rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground sm:block">Sign in</Link>
                        <Link href="/register" class="inline-flex h-9 items-center rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 shadow-sm transition-colors hover:bg-amber-400">Sign up</Link>
                    </template>

                    <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted md:hidden" @click="mobileOpen = !mobileOpen">
                        <Menu v-if="!mobileOpen" class="h-5 w-5" />
                        <X v-else class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0 -translate-y-2" leave-active-class="transition duration-150" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="mobileOpen" class="border-t border-border bg-background px-4 py-3 md:hidden">
                    <Link v-for="link in navLinks" :key="link.href" :href="link.href" class="block rounded-lg px-3 py-2 text-sm font-medium text-foreground hover:bg-muted" @click="mobileOpen = false">{{ link.label }}</Link>
                    <div class="mt-2 border-t border-border pt-2">
                        <Link v-if="user" href="/dashboard" class="block rounded-lg px-3 py-2 text-sm font-medium text-foreground hover:bg-muted">My Trips</Link>
                        <Link v-else href="/login" class="block rounded-lg px-3 py-2 text-sm font-medium text-foreground hover:bg-muted">Sign in</Link>
                    </div>
                </div>
            </Transition>
        </header>

        <!-- Page -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="mt-16 bg-[#0f172a] text-slate-300">
            <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-4 lg:px-8">
                <div class="md:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-amber-950"><Car class="h-5 w-5" /></span>
                        <span class="font-display text-lg font-bold text-white">{{ businessName }}</span>
                    </div>
                    <p class="mt-3 max-w-xs text-sm text-slate-400">{{ settings?.business_description }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Explore</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><Link href="/" class="text-slate-400 transition-colors hover:text-white">Home</Link></li>
                        <li><Link href="/vehicles" class="text-slate-400 transition-colors hover:text-white">Browse Cars</Link></li>
                        <li><Link href="/dashboard" class="text-slate-400 transition-colors hover:text-white">My Trips</Link></li>
                    </ul>
                </div>
                <div>
                    <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Account</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><Link href="/login" class="text-slate-400 transition-colors hover:text-white">Sign in</Link></li>
                        <li><Link href="/register" class="text-slate-400 transition-colors hover:text-white">Create account</Link></li>
                    </ul>
                </div>
                <div>
                    <p class="text-[11px] font-semibold tracking-widest text-amber-400/80 uppercase">Contact</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-400">
                        <li v-if="settings?.business_address" class="flex items-start gap-2"><MapPin class="mt-0.5 h-4 w-4 shrink-0 text-amber-400/70" /> {{ settings.business_address }}</li>
                        <li v-if="settings?.business_phone" class="flex items-center gap-2"><Phone class="h-4 w-4 shrink-0 text-amber-400/70" /> {{ settings.business_phone }}</li>
                        <li v-if="settings?.business_email" class="flex items-center gap-2"><Mail class="h-4 w-4 shrink-0 text-amber-400/70" /> {{ settings.business_email }}</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-700/60">
                <div class="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-slate-500 sm:flex-row sm:px-6 lg:px-8">
                    <p>© {{ year }} {{ businessName }}. All rights reserved.</p>
                    <p>Drive happy. Drive now.</p>
                </div>
            </div>
        </footer>

        <Toaster rich-colors position="top-right" />
    </div>
</template>
