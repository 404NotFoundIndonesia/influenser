<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Paginate, type Campaign, CampaignStatus } from '@/types/model';
import { FilterMatchMode } from '@primevue/core/api';
import CampaignForm from '@/pages/campaign/index/partials/CampaignForm.vue';
import Pagination from '@/components/Pagination.vue';
import {
    Column, ConfirmPopup, DataTable, useConfirm,
    Toolbar, Select, Tag, Image,
} from 'primevue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { ref, watch } from 'vue';
import { dateHumanFormat } from '@/lib/utils';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Campaign',
        href: route('campaign.index')
    }
];

interface Props {
    items: Paginate<Campaign>;
}

defineProps<Props>();

const selected = ref<Campaign[]>([]);
const filters = ref({});
const confirm = useConfirm();
const campaignForm = ref();

const initFilter = () => {
    filters.value = {
        name: { value: null, matchMode: FilterMatchMode.CONTAINS },
        status: { value: null, matchMode: FilterMatchMode.EQUALS },
        start_date: { value: null, matchMode: FilterMatchMode.DATE_AFTER },
        end_date: { value: null, matchMode: FilterMatchMode.DATE_BEFORE },
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

const getSeverity = (status: CampaignStatus) => {
    switch (status) {
        case CampaignStatus.Draft:
            return 'secondary';
        case CampaignStatus.Ongoing:
            return 'info';
        case CampaignStatus.Completed:
            return 'success';
        case CampaignStatus.Cancelled:
            return 'warn';
    }
};

const destroy = (event: MouseEvent, item: Campaign | null) => {
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
                route('campaign.mass-destroy') :
                route('campaign.destroy', item?.id);

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
                                severity="contrast" @click="campaignForm?.open(null)" />
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
                    <template #body="{ data }">
                        <div class="flex gap-x-2">
                            <Image
                                :src="data.picture_url || ''"
                                :alt="data.name"
                                v-if="data.banner_path"
                                width="40"
                                preview />
                            <div>
                                <Link :href="route('campaign.show', data.id)"
                                      class="text-lg font-medium block hover:text-green-600">{{ data.name }}</Link>
                                <div class="text-xs">
                                    <template v-if="data.start_date && data.end_date">
                                        {{ dateHumanFormat(data.start_date) }} - {{ dateHumanFormat(data.end_date) }}
                                    </template>
                                    <template v-else-if="!data.start_date && !data.end_date">
                                        -
                                    </template>
                                    <template v-else-if="data.start_date && !data.end_date">
                                        <span class="text-slate-400">started at</span> {{ dateHumanFormat(data.start_date) }}
                                    </template>
                                    <template v-else-if="!data.start_date && data.end_date">
                                        <span class="text-slate-400">until</span> {{ dateHumanFormat(data.end_date) }}
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </Column>
                <Column field="status" header="Status">
                    <template #body="{ data }">
                        <Tag :value="data.status" class="capitalize" :severity="getSeverity(data.status)" />
                    </template>
                    <template #filter="{ filterModel }">
                        <Select
                            v-model="filterModel.value" option-value="name" :option-label="(opt) => opt.name.replaceAll('_', ' ')"
                            :options="Object.entries(CampaignStatus).map(([key, value]) => ({ code: key, name: value }))"
                            placeholder="Select One" show-clear>
                        </Select>
                    </template>
                </Column>
                <Column class="w-24 !text-end">
                    <template #body="{data}">
                        <div class="flex gap-x-2">
                            <Button icon="pi pi-pencil" size="small"
                                    variant="outlined"
                                    @click="campaignForm?.open(data)"
                                    severity="info" rounded></Button>
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

            <CampaignForm ref="campaignForm" />

            <ConfirmPopup />
        </div>
    </AppLayout>
</template>
