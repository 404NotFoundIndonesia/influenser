<script setup lang="ts">
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import FloatLabel from 'primevue/floatlabel';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Message from 'primevue/message';
import Button from 'primevue/button';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout title="Reset password" description="Please enter your new password below">
        <Head title="Reset password" />

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <FloatLabel variant="in">
                        <InputText :fluid="true" id="email" v-model="form.email" type="email" autocomplete="email" />
                        <label for="email">Email</label>
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

                <Button type="submit" :fluid="true" label="Reset password" :loading="form.processing" />
            </div>
        </form>
    </AuthLayout>
</template>
