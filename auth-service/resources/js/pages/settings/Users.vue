<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ChevronDown, Pencil, Plus, ShieldCheck, Trash2, UserPlus, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { formatDate } from '@/lib/format';
import type { User as AuthUser } from '@/types';

type ManagedUser = {
    id: number;
    name: string;
    email: string;
    role: string;
    role_label: string;
    phone: string | null;
    status: string;
    created_at: string | null;
};

const props = defineProps<{
    users: ManagedUser[];
    roles: { value: string; label: string }[];
}>();

const page = usePage<{ auth: { user: AuthUser } }>();
const currentUserId = computed(() => page.props.auth.user?.id);

const showModal = ref(false);
const editingId = ref<number | null>(null);
const deleteTarget = ref<ManagedUser | null>(null);

const form = useForm({
    name: '',
    email: '',
    role: 'staff',
    phone: '',
    status: 'active',
    password: '',
});

const isEditing = computed(() => editingId.value !== null);

function openCreate() {
    form.reset();
    form.clearErrors();
    editingId.value = null;
    showModal.value = true;
}

function openEdit(user: ManagedUser) {
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.phone = user.phone ?? '';
    form.status = user.status;
    form.password = '';
    editingId.value = user.id;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function submit() {
    const opts = {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    };
    if (isEditing.value) {
        form.put(`/settings/users/${editingId.value}`, opts);
    } else {
        form.post('/settings/users', opts);
    }
}

function confirmDelete() {
    if (!deleteTarget.value) return;
    router.delete(`/settings/users/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => (deleteTarget.value = null),
    });
}

function initials(name: string) {
    return name
        .split(' ')
        .map((p) => p[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

const roleClasses: Record<string, string> = {
    admin: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    staff: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    maintenance: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
};

const inputCls =
    'h-10 w-full rounded-lg border border-input bg-background px-3 text-sm transition-colors focus:border-amber-400 focus:ring-1 focus:ring-amber-400 focus:outline-none';
const selectCls = inputCls + ' appearance-none pr-9';
const labelCls = 'text-xs font-semibold tracking-wider text-muted-foreground uppercase';
</script>

<template>
    <Head title="System Users" />

    <div class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <Heading title="System Users" description="Create and manage the staff accounts that can access the platform." />
            <Button type="button" @click="openCreate">
                <UserPlus class="h-4 w-4" /> Add user
            </Button>
        </div>

        <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-card">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="border-b border-border text-left text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="hidden px-5 py-3 sm:table-cell">Added</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id" class="border-b border-border/60 last:border-0 transition-colors hover:bg-muted/40">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-500/15 text-xs font-bold text-amber-600 dark:text-amber-400">
                                    {{ initials(user.name) }}
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate font-medium text-foreground">
                                        {{ user.name }}
                                        <span v-if="user.id === currentUserId" class="ml-1 text-[11px] font-normal text-muted-foreground">(you)</span>
                                    </span>
                                    <span class="block truncate text-xs text-muted-foreground">{{ user.email }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="roleClasses[user.role] ?? roleClasses.maintenance">
                                {{ user.role_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="user.status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'"
                            >
                                <span class="h-1.5 w-1.5 rounded-full" :class="user.status === 'active' ? 'bg-emerald-500' : 'bg-zinc-400'" />
                                {{ user.status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="hidden px-5 py-3 text-muted-foreground sm:table-cell">{{ formatDate(user.created_at) }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground" title="Edit" @click="openEdit(user)">
                                    <Pencil class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-red-900/20"
                                    title="Delete"
                                    :disabled="user.id === currentUserId"
                                    @click="deleteTarget = user"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="users.length === 0">
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-muted-foreground">No staff accounts yet.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- Create / Edit modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0"
            leave-active-class="transition ease-in duration-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.esc.window="closeModal">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal" />
                <div class="relative z-10 flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-xl" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
                    <div class="flex items-center justify-between border-b border-border px-6 py-4">
                        <h3 id="user-modal-title" class="font-display text-base font-semibold text-foreground">{{ isEditing ? 'Edit user' : 'Add user' }}</h3>
                        <button type="button" aria-label="Close" class="rounded-lg p-1.5 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground" @click="closeModal">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <form class="min-h-0 flex-1 space-y-4 overflow-y-auto px-6 py-5" @submit.prevent="submit">
                        <div class="grid gap-1.5">
                            <label :class="labelCls">Full name</label>
                            <input v-model="form.name" type="text" :class="inputCls" />
                            <p v-if="form.errors.name" class="text-[11px] text-red-500">{{ form.errors.name }}</p>
                        </div>
                        <div class="grid gap-1.5">
                            <label :class="labelCls">Email</label>
                            <input v-model="form.email" type="email" :class="inputCls" />
                            <p v-if="form.errors.email" class="text-[11px] text-red-500">{{ form.errors.email }}</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-1.5">
                                <label :class="labelCls">Role</label>
                                <div class="relative">
                                    <select v-model="form.role" :class="selectCls">
                                        <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                </div>
                                <p v-if="form.errors.role" class="text-[11px] text-red-500">{{ form.errors.role }}</p>
                            </div>
                            <div class="grid gap-1.5">
                                <label :class="labelCls">Status</label>
                                <div class="relative">
                                    <select v-model="form.status" :class="selectCls">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    <ChevronDown class="pointer-events-none absolute top-1/2 right-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                </div>
                                <p v-if="form.errors.status" class="text-[11px] text-red-500">{{ form.errors.status }}</p>
                            </div>
                        </div>
                        <div class="grid gap-1.5">
                            <label :class="labelCls">Phone <span class="font-normal normal-case text-muted-foreground/70">(optional)</span></label>
                            <input v-model="form.phone" type="text" :class="inputCls" />
                        </div>
                        <div class="grid gap-1.5">
                            <label :class="labelCls">Password <span v-if="isEditing" class="font-normal normal-case text-muted-foreground/70">(leave blank to keep current)</span></label>
                            <input v-model="form.password" type="password" autocomplete="new-password" :class="inputCls" />
                            <p v-if="form.errors.password" class="text-[11px] text-red-500">{{ form.errors.password }}</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" class="inline-flex h-9 items-center rounded-lg border border-border px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted" @click="closeModal">Cancel</button>
                            <Button type="submit" :disabled="form.processing">
                                <Spinner v-if="form.processing" />
                                {{ isEditing ? 'Save changes' : 'Create user' }}
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- Delete confirmation -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0"
            leave-active-class="transition ease-in duration-100"
            leave-to-class="opacity-0"
        >
            <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.esc.window="deleteTarget = null">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="deleteTarget = null" />
                <div class="relative z-10 w-full max-w-sm rounded-2xl border border-border bg-card p-6 shadow-xl" role="dialog" aria-modal="true">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                        <ShieldCheck class="h-5 w-5 text-red-600 dark:text-red-400" />
                    </div>
                    <h3 class="mt-4 font-display text-base font-semibold text-foreground">Delete user</h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Remove <strong class="text-foreground">{{ deleteTarget.name }}</strong>? They will lose access to every service immediately. This cannot be undone.
                    </p>
                    <div class="mt-5 flex items-center justify-end gap-3">
                        <button type="button" class="inline-flex h-9 items-center rounded-lg border border-border px-4 text-sm font-medium text-foreground transition-colors hover:bg-muted" @click="deleteTarget = null">Cancel</button>
                        <button type="button" class="inline-flex h-9 items-center gap-2 rounded-lg bg-red-600 px-4 text-sm font-semibold text-white transition-colors hover:bg-red-700" @click="confirmDelete">
                            <Trash2 class="h-4 w-4" /> Delete
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
