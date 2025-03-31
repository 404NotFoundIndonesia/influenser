<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Paginate, type Niche } from '@/types/model';
import { FilterMatchMode } from '@primevue/core/api';
import Pagination from '@/components/Pagination.vue';
import {
    Column, ConfirmPopup, DataTable, useConfirm,
    Toolbar,
} from 'primevue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Campaign',
        href: route('campaign.index')
    }
];

interface Props {
    items: Paginate<Niche>;
}

defineProps<Props>();

const selected = ref<Niche[]>([]);
const filters = ref({});
const confirm = useConfirm();

const initFilter = () => {
    filters.value = {
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
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

const destroy = (event: MouseEvent, item: Niche | null) => {
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
                route('niche.mass-destroy') :
                route('niche.destroy', item?.id);

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
    <Head title="Campaign" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <Toolbar>
                <template #start>
                    <div class="flex gap-x-2">
                        <Button size="small" label="New" icon="pi pi-plus"
                                severity="contrast" @click="null" />
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
                        <InputText v-model="filterModel.value" type="text" placeholder="Search by campaign name" />
                    </template>
                </Column>
                <Column field="description" header="Description"></Column>
                <Column class="w-24 !text-end">
                    <template #body="{data}">
                        <div class="flex gap-x-2">
                            <Button icon="pi pi-trash" size="small"
                                    variant="outlined"
                                    @click="destroy($event, data)"
                                    severity="danger" rounded></Button>
                        </div>
                    </template>
                </Column>
                <template #empty> No campaigns found.</template>
            </DataTable>

            <Pagination :paginator="items" />

            <ConfirmPopup />
        </div>
    </AppLayout>
</template>
