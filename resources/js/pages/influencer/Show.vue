<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Toolbar, Avatar, Menu } from 'primevue';
import Button from 'primevue/button';
import { Influencer } from '@/types/model';
import { ref } from 'vue';

interface Props {
    item: Influencer;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Influencer',
        href: route('influencer.index')
    },
    {
        title: props.item.name,
        href: route('influencer.show', props.item.id)
    }
];

const menu = ref();
const menuItems = ref([
    {
        label: 'Refresh',
        icon: 'pi pi-refresh',
        command: () => alert('Refresh cuy'),
    }
]);
</script>

<template>
    <Head title="Influencer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Toolbar>
                <template #start>
                    <div class="flex gap-x-2">
                        <Link :href="route('influencer.index')">
                            <Button size="small" label="Back" icon="pi pi-arrow-left"
                                    severity="secondary" variant="outlined" />
                        </Link>
                    </div>
                </template>

                <template #end>
                    <div class="flex gap-x-2">
                        <Link :href="route('influencer.edit', item.id)">
                            <Button size="small" label="Edit" icon="pi pi-pencil"
                                    severity="secondary" variant="outlined" />
                        </Link>
                        <Button
                            type="button" size="small" severity="secondary" variant="outlined"
                            icon="pi pi-ellipsis-v" @click="(e) => menu.toggle(e)"
                            aria-haspopup="true" aria-controls="overlay_menu" />
                        <Menu ref="menu" id="overlay_menu" :model="menuItems" :popup="true" />
                    </div>
                </template>
            </Toolbar>
            <div class="flex gap-x-6">
                <Avatar :image="item.picture_url" size="xlarge" shape="circle" v-if="item.picture_url" />
                <div class="flex flex-col gap-y-2 flex-1">
                    <h1 class="text-xl font-medium">{{ item.name }}</h1>
                    <div class="flex text-sm">
                        <div>{{ item.location }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
