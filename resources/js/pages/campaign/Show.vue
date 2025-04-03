<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {
    Toolbar, Button, Image
} from 'primevue';
import { Campaign } from '@/types/model';

interface Props {
    item: Campaign;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Campaign',
        href: route('campaign.index')
    },
    {
        title: props.item.name,
        href: route('campaign.show', props.item.id)
    }
];
</script>

<template>
    <Head title="Campaign" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Toolbar>
                <template #start>
                    <div class="flex gap-x-2">
                        <Link :href="route('campaign.index')">
                            <Button size="small" label="Back" icon="pi pi-arrow-left" severity="secondary" variant="outlined" />
                        </Link>
                    </div>
                </template>

                <template #end>
                    <div class="flex gap-x-2">

                    </div>
                </template>
            </Toolbar>
            <div class="flex gap-x-6">
                <Image
                    :src="item.picture_url || ''"
                    :alt="item.name"
                    v-if="item.banner_path"
                    width="40"
                    preview />
                <div class="flex flex-col gap-y-2 flex-1">
                    <h1 class="text-xl font-medium">{{ item.name }}</h1>

                </div>
            </div>

        </div>
    </AppLayout>
</template>
