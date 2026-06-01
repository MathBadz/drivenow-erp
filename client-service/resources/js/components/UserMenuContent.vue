<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

const confirmingLogout = ref(false);

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <!-- Keep the menu open (@select.prevent) so the confirm modal stays mounted. -->
    <DropdownMenuItem data-test="logout-button" @select.prevent="confirmingLogout = true">
        <LogOut class="mr-2 h-4 w-4" />
        Log out
    </DropdownMenuItem>

    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0"
            leave-active-class="transition ease-in duration-100"
            leave-to-class="opacity-0"
        >
            <div v-if="confirmingLogout" class="fixed inset-0 z-[80] flex items-center justify-center p-4" @keydown.esc.window="confirmingLogout = false">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="confirmingLogout = false" />
                <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-xl" role="dialog" aria-modal="true">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                        <LogOut class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h2 class="mt-4 text-base font-semibold text-foreground">Sign out?</h2>
                    <p class="mt-1 text-sm text-muted-foreground">You'll need to sign in again to access your account.</p>
                    <div class="mt-5 flex items-center justify-end gap-3">
                        <button type="button" class="inline-flex h-9 items-center rounded-lg border border-border px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted" @click="confirmingLogout = false">Cancel</button>
                        <Link
                            :href="logout()"
                            as="button"
                            class="inline-flex h-9 items-center gap-2 rounded-lg bg-amber-500 px-4 text-sm font-semibold text-amber-950 transition-colors hover:bg-amber-400"
                            @click="handleLogout"
                        >
                            <LogOut class="h-4 w-4" /> Sign out
                        </Link>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
