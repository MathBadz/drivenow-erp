<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitials } from '@/composables/useInitials';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Profile settings',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

const { getInitials } = useInitials();
const avatarInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(user.value.avatar ?? null);
const removeAvatar = ref(false);
const avatarError = ref(false);

// Re-sync the preview when the server avatar changes (e.g. after a successful
// save Inertia refreshes the auth user) so it never shows a stale image.
watch(
    () => user.value.avatar,
    (val) => {
        avatarPreview.value = val ?? null;
        avatarError.value = false;
        removeAvatar.value = false;
    },
);

function onAvatarChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    avatarPreview.value = file ? URL.createObjectURL(file) : (user.value.avatar ?? null);
    avatarError.value = false;
    removeAvatar.value = false;
}

function clearAvatar() {
    avatarPreview.value = null;
    removeAvatar.value = true;
    if (avatarInput.value) avatarInput.value.value = '';
}
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Profile"
            description="Update your name and email address"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label>Profile photo</Label>
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full border border-border bg-muted">
                        <img v-if="avatarPreview && !avatarError" :src="avatarPreview" alt="Avatar" class="h-full w-full object-cover" @error="avatarError = true" />
                        <span v-else class="text-base font-semibold text-muted-foreground">{{ getInitials(user.name) }}</span>
                    </div>
                    <div>
                        <input ref="avatarInput" type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onAvatarChange" />
                        <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'" />
                        <div class="flex flex-wrap items-center gap-2">
                            <Button type="button" variant="outline" size="sm" @click="avatarInput?.click()">
                                {{ avatarPreview ? 'Change photo' : 'Upload photo' }}
                            </Button>
                            <Button v-if="avatarPreview" type="button" variant="ghost" size="sm" class="text-red-600 hover:text-red-700" @click="clearAvatar">
                                Remove
                            </Button>
                        </div>
                        <p class="mt-1.5 text-xs text-muted-foreground">PNG, JPG or WebP up to 2 MB.</p>
                        <InputError class="mt-1" :message="errors.avatar" />
                    </div>
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Full name"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Email address"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <div v-if="page.props.mustVerifyEmail && !user.email_verified_at">
                <p class="-mt-4 text-sm text-muted-foreground">
                    Your email address is unverified.
                    <Link
                        :href="send()"
                        as="button"
                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-if="page.props.status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Save</Button
                >
            </div>
        </Form>
    </div>

    <DeleteUser />
</template>
