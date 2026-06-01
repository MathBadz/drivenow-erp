<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Car,
    CalendarCheck,
    Gauge,
    Globe,
    LayoutGrid,
    LogOut,
    Receipt,
    UsersRound,
    Wrench,
    type LucideIcon,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { SystemSettings } from '@/types';

defineProps<{ open: boolean }>();

const confirmingLogout = ref(false);

function confirmLogout(): void {
    router.post('/logout');
}

type NavLink = { label: string; href: string; icon: LucideIcon };
type SidebarUser = { name?: string; role?: string; role_label?: string };

const page = usePage<{ settings: SystemSettings; auth: { user: SidebarUser | null }; serviceUrls: Record<string, string> }>();
const settings = computed(() => page.props.settings);
const user = computed(() => page.props.auth?.user ?? null);

const initials = computed(() => {
    const n = user.value?.name ?? '';
    return n.split(' ').map((p) => p[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || 'U';
});
const roleLabel = computed(() =>
    user.value?.role_label ??
    (user.value?.role ? user.value.role.charAt(0).toUpperCase() + user.value.role.slice(1) : 'Staff'),
);

const currentPath = computed(() => {
    try {
        return new URL(page.url, 'http://localhost').pathname;
    } catch {
        return page.url;
    }
});
const isActive = (href: string) => currentPath.value === href || currentPath.value.startsWith(href + '/');

const subtitle = 'Fleet Management';
const sections: { title: string; links: NavLink[] }[] = [
    { title: 'Main', links: [{ label: 'Dashboard', href: '/dashboard', icon: Gauge }] },
    { title: 'Fleet', links: [{ label: 'Vehicles', href: '/vehicles', icon: Car }] },
];
// Sibling links are config-driven (page.props.serviceUrls ← config('services.public'))
// so they resolve correctly behind a gateway / public host, not just localhost.
const serviceNav = computed<NavLink[]>(() =>
    [
        { label: 'Operations Hub', key: 'auth', icon: LayoutGrid },
        { label: 'Rentals', key: 'rental', icon: CalendarCheck },
        { label: 'Customers', key: 'crm', icon: UsersRound },
        { label: 'Billing', key: 'billing', icon: Receipt },
        { label: 'Maintenance', key: 'maintenance', icon: Wrench },
        { label: 'Analytics', key: 'analytics', icon: BarChart3 },
        { label: 'Client Portal', key: 'client', icon: Globe },
    ]
        .map((s) => ({ label: s.label, href: page.props.serviceUrls?.[s.key] ?? '', icon: s.icon }))
        .filter((s) => s.href !== ''),
);
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-40 flex h-screen shrink-0 flex-col overflow-hidden bg-[#0f172a] text-slate-300 transition-all duration-200 lg:static lg:translate-x-0"
        :class="[open ? 'translate-x-0' : '-translate-x-full', open ? 'w-64 lg:w-64' : 'w-64 lg:w-[3.5rem]']"
    >
        <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-slate-700/60 px-3">
            <img v-if="settings?.logo_url" :src="settings.logo_url" alt="logo" class="h-9 w-9 shrink-0 rounded-lg object-contain" />
            <div v-else class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-amber-950 shadow-sm">
                <Car class="h-5 w-5" />
            </div>
            <div v-show="open" class="min-w-0 leading-tight">
                <p class="truncate font-display text-sm font-bold text-white">{{ settings?.business_name ?? 'DriveNow' }}</p>
                <p class="truncate text-[10px] font-medium tracking-widest text-amber-400/80 uppercase">{{ subtitle }}</p>
            </div>
        </div>

        <nav class="flex-1 space-y-6 overflow-y-auto px-2 py-4">
            <div v-for="section in sections" :key="section.title">
                <p v-show="open" class="px-2 pb-2 text-[10px] font-semibold tracking-widest text-slate-500 uppercase">{{ section.title }}</p>
                <ul class="space-y-1">
                    <li v-for="item in section.links" :key="item.href">
                        <Link
                            :href="item.href"
                            class="group/link relative flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors"
                            :class="isActive(item.href) ? 'bg-amber-500/15 text-amber-400' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white'"
                        >
                            <component :is="item.icon" class="h-[18px] w-[18px] shrink-0" />
                            <span v-show="open" class="truncate">{{ item.label }}</span>
                            <span v-if="!open" class="pointer-events-none absolute left-full z-50 ml-2 hidden rounded-md bg-slate-900 px-2.5 py-1 text-xs whitespace-nowrap text-white opacity-0 shadow-lg transition-opacity group-hover/link:opacity-100 lg:block">{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>
            </div>

            <div>
                <p v-show="open" class="px-2 pb-2 text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Other Services</p>
                <ul class="space-y-1">
                    <li v-for="item in serviceNav" :key="item.href">
                        <a
                            :href="item.href"
                            class="group/link relative flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-700/50 hover:text-white"
                        >
                            <component :is="item.icon" class="h-[18px] w-[18px] shrink-0" />
                            <span v-show="open" class="truncate">{{ item.label }}</span>
                            <span v-if="!open" class="pointer-events-none absolute left-full z-50 ml-2 hidden rounded-md bg-slate-900 px-2.5 py-1 text-xs whitespace-nowrap text-white opacity-0 shadow-lg transition-opacity group-hover/link:opacity-100 lg:block">{{ item.label }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="shrink-0 border-t border-slate-700/60 p-2">
            <div class="flex items-center gap-2.5 rounded-lg px-1.5 py-1.5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-amber-950">{{ initials }}</span>
                <span v-show="open" class="min-w-0 flex-1 leading-tight">
                    <span class="block truncate text-sm font-medium text-white">{{ user?.name ?? 'Guest' }}</span>
                    <span class="block truncate text-[11px] text-slate-400">{{ roleLabel }}</span>
                </span>
                <button
                    v-show="open"
                    type="button"
                    title="Sign out"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-700/60 hover:text-white"
                    @click="confirmingLogout = true"
                >
                    <LogOut class="h-4 w-4" />
                </button>
            </div>
        </div>
        <!-- Sign-out confirmation -->
        <Teleport to="body">
            <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0" leave-active-class="transition ease-in duration-100" leave-to-class="opacity-0">
                <div v-if="confirmingLogout" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @keydown.esc.window="confirmingLogout = false">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="confirmingLogout = false" />
                    <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-xl" role="dialog" aria-modal="true">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                            <LogOut class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h2 class="font-display mt-4 text-base font-semibold text-foreground">Sign out?</h2>
                        <p class="mt-1 text-sm text-muted-foreground">You'll need to sign in again to access the platform.</p>
                        <div class="mt-5 flex items-center justify-end gap-3">
                            <button type="button" class="inline-flex h-9 items-center rounded-lg border border-border px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted" @click="confirmingLogout = false">Cancel</button>
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 transition-colors hover:bg-amber-400" @click="confirmLogout">
                                <LogOut class="h-4 w-4" /> Sign out
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </aside>
</template>
