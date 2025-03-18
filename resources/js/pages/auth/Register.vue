<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import FloatLabel from 'primevue/floatlabel';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Message from 'primevue/message';
import Button from 'primevue/button';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <AuthBase title="Create an account" description="Enter your details below to create your account">
        <Head title="Register" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <FloatLabel variant="in">
                        <InputText :fluid="true" :autofocus="true" id="name" v-model="form.name" autocomplete="name" />
                        <label for="name">Name</label>
                    </FloatLabel>
                    <Message v-if="form.errors.name" severity="error" size="small" variant="simple">{{ form.errors.name }}</Message>
                </div>

                <div class="grid gap-2">
                    <FloatLabel variant="in">
                        <InputText :fluid="true" id="email" v-model="form.email" type="email" autocomplete="email" />
                        <label for="email">Email address</label>
                    </FloatLabel>
                    <Message v-if="form.errors.email" severity="error" size="small" variant="simple">{{ form.errors.email }}</Message>
                </div>

                <div class="grid gap-2">
                    <FloatLabel variant="in">
                        <Password v-model="form.password" inputId="password" :fluid="true" toggleMask />
                        <label for="password">Password</label>
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

                <Button type="submit" label="Create account" :loading="form.processing" />
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Log in</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
