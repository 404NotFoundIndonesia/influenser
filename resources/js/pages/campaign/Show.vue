<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {
    Toolbar, Button, Image, DataTable, Column,
    Tag, Dialog, FloatLabel, InputText, Select,
    Message, ConfirmPopup, useConfirm,
} from 'primevue';
import { type Campaign, type CampaignKeyOpinionLeader, type Influencer, type KeyOpinionLeader, Platform } from '@/types/model';
import { ref, computed } from 'vue';
import { dateHumanFormat } from '@/lib/utils';

interface Props {
    item: Campaign;
    influencers: Influencer[];
}

const props = defineProps<Props>();
const confirm = useConfirm();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Campaign', href: route('campaign.index') },
    { title: props.item.name, href: route('campaign.show', props.item.id) },
];

// ── Attach KOL dialog ────────────────────────────────────────────────────────

const attachVisible = ref(false);

const attachForm = useForm({
    key_opinion_leader_id: '' as string,
    deliverable: '',
});

const selectedInfluencer = ref<Influencer | null>(null);

const availableKols = computed<KeyOpinionLeader[]>(() => {
    if (!selectedInfluencer.value) return [];
    const current: CampaignKeyOpinionLeader[] = props.item.key_opinion_leaders ?? [];
    const attached = new Set(current.map(k => k.id));
    const all: KeyOpinionLeader[] = selectedInfluencer.value.key_opinion_leaders ?? [];
    return all.filter(k => !attached.has(k.id));
});

const kolOptionLabel = (k: KeyOpinionLeader) => `${k.platform_name} · ${k.username}`;

const openAttach = () => {
    attachVisible.value = true;
    attachForm.reset();
    selectedInfluencer.value = null;
};

const submitAttach = () => {
    attachForm.post(route('campaign.kol.store', props.item.id), {
        preserveScroll: true,
        onSuccess: () => {
            attachVisible.value = false;
            attachForm.reset();
            selectedInfluencer.value = null;
        },
    });
};

// ── Detach KOL ───────────────────────────────────────────────────────────────

const detach = (event: MouseEvent, kol: CampaignKeyOpinionLeader) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        message: `Remove ${kol.platform_name} · ${kol.username} from this campaign?`,
        icon: 'pi pi-exclamation-triangle',
        rejectProps: { label: 'Cancel', severity: 'secondary', outlined: true },
        acceptProps: { label: 'Remove', severity: 'danger' },
        accept: () => {
            router.delete(route('campaign.kol.destroy', { campaign: props.item.id, keyOpinionLeader: kol.id }), {
                preserveScroll: true,
                preserveState: false,
            });
        },
    });
};

// ── Helpers ──────────────────────────────────────────────────────────────────

const platformIcon = (platform: Platform) => `pi pi-${platform.toLowerCase()}`;
</script>

<template>
    <Head title="Campaign" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">

            <!-- Toolbar -->
            <Toolbar>
                <template #start>
                    <Link :href="route('campaign.index')">
                        <Button size="small" label="Back" icon="pi pi-arrow-left"
                                severity="secondary" variant="outlined" />
                    </Link>
                </template>
            </Toolbar>

            <!-- Campaign header -->
            <div class="flex gap-x-6">
                <Image
                    v-if="item.banner_path"
                    :src="item.picture_url || ''"
                    :alt="item.name"
                    width="80"
                    preview />
                <div class="flex flex-col gap-y-1 flex-1">
                    <h1 class="text-xl font-medium">{{ item.name }}</h1>
                    <p v-if="item.description" class="text-sm text-surface-500">{{ item.description }}</p>
                    <div class="flex items-center gap-x-3 text-sm">
                        <Tag :value="item.status" class="capitalize" />
                        <span v-if="item.start_date || item.end_date" class="text-surface-400">
                            {{ dateHumanFormat(item.start_date) }} — {{ dateHumanFormat(item.end_date) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- KOL section -->
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold">KOLs</h2>
                    <Button size="small" label="Add KOL" icon="pi pi-plus"
                            severity="contrast" @click="openAttach" />
                </div>

                <DataTable
                    :value="item.key_opinion_leaders ?? []"
                    show-gridlines
                    size="small"
                    :rows="10">
                    <template #empty>No KOLs attached to this campaign yet.</template>

                    <Column header="Influencer">
                        <template #body="{ data }">
                            <Link :href="route('influencer.show', data.influencer?.id ?? '')"
                                  class="font-medium hover:text-green-600">
                                {{ data.influencer?.name ?? '—' }}
                            </Link>
                        </template>
                    </Column>

                    <Column header="Platform">
                        <template #body="{ data }">
                            <div class="flex items-center gap-x-2">
                                <i :class="platformIcon(data.platform)" style="font-size: 1.1rem"></i>
                                <span>{{ data.platform_name }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column header="Username">
                        <template #body="{ data }">
                            <a :href="data.link" target="_blank"
                               class="text-sm hover:text-teal-600">
                                {{ data.username }}
                            </a>
                        </template>
                    </Column>

                    <Column header="Deliverable">
                        <template #body="{ data }">
                            {{ data.pivot.deliverable || '—' }}
                        </template>
                    </Column>

                    <Column header="Posted">
                        <template #body="{ data }">
                            {{ dateHumanFormat(data.pivot.posted_at) }}
                        </template>
                    </Column>

                    <Column class="w-20 !text-end">
                        <template #body="{ data }">
                            <Button icon="pi pi-times" size="small" variant="outlined"
                                    severity="danger" rounded
                                    v-tooltip.top="'Remove from campaign'"
                                    @click="detach($event, data)" />
                        </template>
                    </Column>
                </DataTable>
            </div>

        </div>
    </AppLayout>

    <!-- Attach KOL dialog -->
    <Dialog v-model:visible="attachVisible" modal header="Add KOL to Campaign"
            :style="{ width: '36rem' }">
        <div class="flex flex-col gap-5 pt-2 pb-6">

            <!-- Influencer picker -->
            <div class="grid gap-2">
                <FloatLabel variant="on">
                    <Select
                        v-model="selectedInfluencer"
                        input-id="influencer"
                        :options="influencers"
                        option-label="name"
                        :fluid="true"
                        filter
                        placeholder="Select an influencer"
                        @change="attachForm.key_opinion_leader_id = ''" />
                    <label for="influencer" class="text-sm">Influencer</label>
                </FloatLabel>
            </div>

            <!-- KOL picker (shown only after influencer selected) -->
            <div v-if="selectedInfluencer" class="grid gap-2">
                <FloatLabel variant="on">
                    <Select
                        v-model="attachForm.key_opinion_leader_id"
                        input-id="kol"
                        :options="availableKols"
                        option-value="id"
                        :option-label="kolOptionLabel"
                        :fluid="true"
                        :placeholder="availableKols.length ? 'Select a platform account' : 'No available KOL accounts'"
                        :disabled="!availableKols.length" />
                    <label for="kol" class="text-sm">Platform account</label>
                </FloatLabel>
                <Message v-if="attachForm.errors.key_opinion_leader_id"
                         severity="error" size="small" variant="simple">
                    {{ attachForm.errors.key_opinion_leader_id }}
                </Message>
                <p v-if="selectedInfluencer && !availableKols.length"
                   class="text-xs text-surface-400">
                    All KOL accounts for this influencer are already attached.
                </p>
            </div>

            <!-- Deliverable -->
            <div v-if="attachForm.key_opinion_leader_id" class="grid gap-2">
                <FloatLabel variant="on">
                    <InputText id="deliverable" v-model="attachForm.deliverable"
                               :fluid="true" placeholder="e.g. 1 TikTok video, 2 IG stories" />
                    <label for="deliverable" class="text-sm">Deliverable (optional)</label>
                </FloatLabel>
                <Message v-if="attachForm.errors.deliverable"
                         severity="error" size="small" variant="simple">
                    {{ attachForm.errors.deliverable }}
                </Message>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" @click="attachVisible = false" />
            <Button label="Add" icon="pi pi-check"
                    :disabled="!attachForm.key_opinion_leader_id || attachForm.processing"
                    @click="submitAttach" />
        </div>
    </Dialog>

    <ConfirmPopup />
</template>
