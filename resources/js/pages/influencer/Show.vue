<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {
    Toolbar, Avatar, Menu, Button,
    Tabs, TabList, Tab, TabPanels, TabPanel,
} from 'primevue';
import { Influencer } from '@/types/model';
import { ref } from 'vue';
import { digitFormatter, shortNumberFormatter } from '@/lib/utils';
import { type MenuItem } from 'primevue/menuitem';
import Platform from '@/pages/influencer/show/partials/Platform.vue';
import History from '@/pages/influencer/show/partials/History.vue';

interface TabItem {
    key: number;
    title: string;
    content: any;
}

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
const menuItems = ref<MenuItem[]>([
    {
        label: 'Refresh',
        icon: 'pi pi-refresh',
        command: () => alert('Refresh cuy'),
    }
]);

const tab = ref<number>(0);
const tabs = ref<TabItem[]>([
    {
        key: 0,
        title: 'Platform',
        content: Platform,
    },
    {
        key: 1,
        title: 'History',
        content: History,
    },
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
                            <Button size="small" label="Back" icon="pi pi-arrow-left" severity="secondary" variant="outlined" />
                        </Link>
                    </div>
                </template>

                <template #end>
                    <div class="flex gap-x-2">
                        <Link :href="route('influencer.edit', item.id)">
                            <Button size="small" label="Edit" icon="pi pi-pencil" severity="secondary" variant="outlined" />
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
                    <div class="flex gap-x-2 items-center">
                        <div>{{ item.location }}</div>
                        <template v-if="item.key_opinion_leaders?.length">
                            <span class="text-gray-400">|</span>
                            <div class="flex gap-3">
                                <template v-for="keyOpinionLeader in item.key_opinion_leaders" :key="keyOpinionLeader.id">
                                    <a :href="keyOpinionLeader.link" target="_blank"
                                       v-tooltip.bottom="`${keyOpinionLeader.platform_name} - ${digitFormatter(keyOpinionLeader.followers)} followers`"
                                       style="font-size: 1rem">
                                        <i :class="`pi pi-${keyOpinionLeader.platform.toLowerCase()}`"></i>
                                        {{ shortNumberFormatter(keyOpinionLeader.followers) }}
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <Tabs v-model:value="tab">
                <TabList>
                    <Tab v-for="t in tabs" :key="t.key" :value="t.key">{{ t.title }}</Tab>
                </TabList>
                <TabPanels>
                    <TabPanel v-for="t in tabs" :key="t.key" :value="t.key">
                        <component :is="tabs[tab].content" :influencer="item" />
                    </TabPanel>
                </TabPanels>
            </Tabs>
        </div>
    </AppLayout>
</template>
