<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, Moon, PanelLeft, Sun } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Toaster } from 'vue-sonner';
import ErpSidebar from '@/components/ErpSidebar.vue';
import { useAppearance } from '@/composables/useAppearance';

type Breadcrumb = { title: string; href: string };

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: Breadcrumb[];
}>();

const page = usePage<{ sidebarOpen?: boolean }>();
const currentKey = computed(() => {
    try {
        return new URL(page.url, 'http://localhost').pathname;
    } catch {
        return page.url;
    }
});

const open = ref(page.props.sidebarOpen ?? true);
const { resolvedAppearance, updateAppearance } = useAppearance();

function persist(value: boolean) {
    if (typeof document === 'undefined') return;
    document.cookie = `sidebar_state=${value};path=/;max-age=${60 * 60 * 24 * 365};SameSite=Lax`;
}

function toggleSidebar() {
    open.value = !open.value;
    persist(open.value);
}

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}

onMounted(() => {
    if (typeof window !== 'undefined' && window.innerWidth < 1024) {
        open.value = false;
    }
});
</script>

<template>
    <div class="flex h-screen w-full overflow-hidden bg-background">
        <ErpSidebar :open="open" />

        <Transition
            enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div v-if="open" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="toggleSidebar" />
        </Transition>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-16 shrink-0 items-center gap-3 border-b border-border bg-card/80 px-4 backdrop-blur-sm">
                <button
                    type="button"
                    title="Toggle sidebar"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border text-muted-foreground transition-all hover:border-amber-300 hover:text-amber-600 active:scale-95"
                    @click="toggleSidebar"
                >
                    <PanelLeft class="h-4 w-4" />
                </button>

                <nav v-if="breadcrumbs.length" class="flex min-w-0 items-center gap-1.5 text-sm">
                    <template v-for="(crumb, i) in breadcrumbs" :key="i">
                        <ChevronRight v-if="i > 0" class="h-3.5 w-3.5 shrink-0 text-muted-foreground/50" />
                        <Link
                            v-if="i < breadcrumbs.length - 1"
                            :href="crumb.href"
                            class="truncate text-muted-foreground transition-colors hover:text-foreground"
                        >
                            {{ crumb.title }}
                        </Link>
                        <span v-else class="truncate font-medium text-foreground">{{ crumb.title }}</span>
                    </template>
                </nav>

                <div class="ml-auto flex items-center gap-2">
                    <button
                        type="button"
                        title="Toggle theme"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-border text-muted-foreground transition-all hover:border-amber-300 hover:text-amber-600 active:scale-95"
                        @click="toggleTheme"
                    >
                        <Sun v-if="resolvedAppearance === 'dark'" class="h-4 w-4" />
                        <Moon v-else class="h-4 w-4" />
                    </button>
                </div>
            </header>

            <main class="min-w-0 flex-1 overflow-y-auto">
                <Transition
                    mode="out-in"
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-1.5"
                    leave-active-class="transition duration-100 ease-in"
                    leave-to-class="opacity-0"
                >
                    <div :key="currentKey">
                        <slot />
                    </div>
                </Transition>
            </main>
        </div>

        <Toaster rich-colors position="top-right" />
    </div>
</template>
