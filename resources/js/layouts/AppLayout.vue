<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import Toast from 'primevue/toast';
import type { BreadcrumbItemType } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { onUnmounted, ref, watchEffect } from 'vue';
import { useToast } from 'primevue';

const page = usePage();
const toast = useToast();

const timeOutRef = ref<number|null>(null);

watchEffect( () => {
    const flashMessage = page.props.flash;
    timeOutRef.value = setTimeout(() => {
        if (flashMessage?.success) {
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: flashMessage?.success,
                life: 3000,
            });
        } else if (flashMessage?.error) {
            toast.add({
                severity: 'error',
                summary: 'Fail',
                detail: flashMessage?.error,
                life: 3000,
            });
        }
    }, 150);
});

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

onUnmounted(() => {
    if (timeOutRef.value !== null) {
        clearTimeout(timeOutRef.value);
    }
});
</script>

<template>
    <Toast />
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
