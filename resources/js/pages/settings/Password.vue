<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import FloatLabel from 'primevue/floatlabel';
import Password from 'primevue/password';
import Message from 'primevue/message';
import Button from 'primevue/button';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: '/settings/password',
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors: any) => {
            if (errors.password) {
                form.reset('password', 'password_confirmation');
                if (passwordInput.value instanceof HTMLInputElement) {
                    passwordInput.value.focus();
                }
            }

            if (errors.current_password) {
                form.reset('current_password');
                if (currentPasswordInput.value instanceof HTMLInputElement) {
                    currentPasswordInput.value.focus();
                }
            }
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Update password" description="Ensure your account is using a long, random password to stay secure" />

                <form @submit.prevent="updatePassword" class="space-y-6">
                    <div class="grid gap-2">
                        <FloatLabel variant="in">
                            <Password v-model="form.current_password" :autofocus="true" inputId="current_password" :fluid="true" :feedback="false" toggleMask />
                            <label for="current_password">Current Password</label>
                        </FloatLabel>
                        <Message v-if="form.errors.current_password" severity="error" size="small" variant="simple">{{ form.errors.current_password }}</Message>
                    </div>

                    <div class="grid gap-2">
                        <FloatLabel variant="in">
                            <Password v-model="form.password" inputId="password" :fluid="true" toggleMask />
                            <label for="password">New password</label>
                        </FloatLabel>
                        <Message v-if="form.errors.password" severity="error" size="small" variant="simple">{{ form.errors.password }}</Message>
                    </div>

                    <div class="grid gap-2">
                        <FloatLabel variant="in">
                            <Password
                                :invalid="form.password !== form.password_confirmation"
                                v-model="form.password_confirmation"
                                inputId="password_confirmation" :fluid="true"
                                :feedback="false" toggleMask />
                            <label for="password_confirmation">Confirm password</label>
                        </FloatLabel>
                        <Message v-if="form.errors.password_confirmation" severity="error" size="small" variant="simple">{{ form.errors.password_confirmation }}</Message>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            size="small"
                            type="submit"
                            label="Save password"
                            :loading="form.processing" />

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
