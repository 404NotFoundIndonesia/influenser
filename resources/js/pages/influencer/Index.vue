<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Paginate, Influencer, type InfluencerStatus, Platform } from '@/types/model';
import { FilterMatchMode } from '@primevue/core/api';
import Pagination from '@/components/Pagination.vue';
import {
    Avatar, Column, ConfirmPopup, DataTable,
    Tag, useConfirm, Select, Toolbar
} from 'primevue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Influencer',
        href: route('influencer.index')
    }
];

interface Props {
    items: Paginate<Influencer>;
}

defineProps<Props>();

const selected = ref<Influencer[]>([]);
const filters = ref({});
const confirm = useConfirm();
const initFilter = () => {
    filters.value = {
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
        phone: { value: null, matchMode: FilterMatchMode.CONTAINS },
        status: { value: null, matchMode: FilterMatchMode.EQUALS },
        'key_opinion_leaders.platform': { value: null, matchMode: FilterMatchMode.EQUALS }
    };
};

initFilter();

const clearFilter = () => {
    router.reload({
        only: ['items'],
        data: { filter: null },
        replace: true,
        onSuccess: initFilter,
    });
};

const getSeverity = (status: InfluencerStatus) => {
    switch (status) {
        case 'active':
            return 'success';
        case 'inactive':
            return 'warn';
        case 'banned':
            return 'danger';
    }
};

const destroy = (event: MouseEvent, item: Influencer|null) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        message: 'Are you sure you want to delete?',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger',
        },
        accept: () => {
            const url = item === null ?
                route('influencer.mass-destroy') :
                route('influencer.destroy', item?.id);

            router.delete(url, {
                preserveScroll: true,
                preserveState: true,
                data: { ids: selected.value.map(i => i.id) },
                onSuccess: () => {
                    if (item === null)
                        selected.value = [];
                }
            });
        },
    });
};

watch(filters, (newFilters) => {
    router.reload({
        only: ['items'],
        data: { filter: newFilters },
        replace: true,
    });
}, { deep: true });
</script>

<template>
    <Head title="Influencer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Toolbar>
                <template #start>
                    <div class="flex gap-x-2">
                        <Link :href="route('influencer.create')">
                            <Button size="small" label="New" icon="pi pi-plus"
                                    severity="contrast" />
                        </Link>
                        <Button size="small" label="Delete" icon="pi pi-trash"
                                severity="danger" outlined @click="destroy($event, null)"
                                :disabled="!selected || !selected.length" />
                    </div>
                </template>

                <template #end>
                    <div class="flex gap-x-2">
                        <Button size="small" label="Clear" icon="pi pi-filter-slash"
                                severity="secondary" variant="outlined" @click="clearFilter" />
                    </div>
                </template>
            </Toolbar>

            <DataTable
                :lazy="true"
                show-gridlines
                filter-display="menu"
                v-model:selection="selected"
                v-model:filters="filters"
                :value="items.data"
                table-style="min-width: 50rem">
                <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
                <Column field="name" header="Name">
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
                    </template>
                    <template #body="{ data }">
                        <div class="flex items-start gap-2 whitespace-nowrap">
                            <Avatar :image="data.picture_url" shape="circle" />
                            <div class="flex flex-col">
                                <Link :href="route('influencer.show', data.id)" class="font-medium hover:text-green-600">
                                    {{ data.name }}
                                </Link>
                                <small>{{ data.location }}</small>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column field="phone" header="Phone Number">
                    <template #filter="{ filterModel }">
                        <InputText v-model="filterModel.value" type="text" placeholder="Search by phone number" />
                    </template>
                </Column>
                <Column field="key_opinion_leaders.platform" header="Platform">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <template v-for="keyOpinionLeader in data.key_opinion_leaders" :key="keyOpinionLeader.id">
                                <i
                                    :class="`pi pi-${keyOpinionLeader.platform.toLowerCase()}`"
                                    v-tooltip="keyOpinionLeader.platform"
                                    style="font-size: 1rem"></i>
                            </template>
                        </div>
                    </template>
                    <template #filter="{ filterModel }">
                        <Select
                            v-model="filterModel.value" option-value="name" option-label="code"
                            :options="Object.entries(Platform).map(([key, value]) => ({ code: key, name: value }))"
                            placeholder="Select One" show-clear>
                        </Select>
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="{ data }">
                        <Tag :value="data.status" class="capitalize" :severity="getSeverity(data.status)" />
                    </template>
                    <template #filter="{ filterModel }">
                        <Select v-model="filterModel.value" :options="['active', 'banned', 'inactive']" placeholder="Select One" showClear>
                            <template #option="slotProps">
                                <Tag :value="slotProps.option" class="capitalize" :severity="getSeverity(slotProps.option)" />
                            </template>
                        </Select>
                    </template>
                </Column>
                <Column class="w-24 !text-end">
                    <template #body="{data}">
                        <div class="flex gap-x-2">
                            <Link :href="route('influencer.edit', data.id)">
                                <Button icon="pi pi-pencil" size="small"
                                        variant="outlined"
                                        severity="info" rounded></Button>
                            </Link>
                            <Button icon="pi pi-trash" size="small"
                                    variant="outlined"
                                    @click="destroy($event, data)"
                                    severity="danger" rounded></Button>
                        </div>
                    </template>
                </Column>
                <template #empty> No influencers found.</template>
            </DataTable>

            <Pagination :paginator="items" />

            <ConfirmPopup />
        </div>
    </AppLayout>
</template>
