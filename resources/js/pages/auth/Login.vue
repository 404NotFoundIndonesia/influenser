<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Checkbox } from '@/components/ui/checkbox';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import FloatLabel from 'primevue/floatlabel';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Message from 'primevue/message';
import Button from 'primevue/button';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Log in to your account" description="Enter your email and password below to log in">
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <FloatLabel variant="in">
                        <InputText :fluid="true" :autofocus="true" id="email" v-model="form.email" type="email" autocomplete="off" />
                        <label for="email">Email address</label>
                    </FloatLabel>
                    <Message v-if="form.errors.email" severity="error" size="small" variant="simple">{{ form.errors.email }}</Message>
                </div>

                <div class="grid gap-2">
                    <FloatLabel variant="in">
                        <Password v-model="form.password" inputId="password" :fluid="true" :feedback="false" toggleMask />
                        <label for="password">Password</label>
                    </FloatLabel>
                    <Message v-if="form.errors.password" severity="error" size="small" variant="simple">{{ form.errors.password }}</Message>
                </div>

                <div class="flex items-center justify-between" :tabindex="3">
                    <div class="flex items-center gap-2">
                        <Checkbox v-model="form.remember" inputId="remember" value="true" />
                        <label for="remember">Remember me</label>
                    </div>
                    <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                        Forgot password?
                    </TextLink>
                </div>

                <Button type="submit" label="Log in" :loading="form.processing" />
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Don't have an account?
                <TextLink :href="route('register')" :tabindex="5">Sign up</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
