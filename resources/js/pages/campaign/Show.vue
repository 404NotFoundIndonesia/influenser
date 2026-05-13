<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dateHumanFormat, digitFormatter } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import {
    type Campaign,
    type CampaignKeyOpinionLeader,
    type Influencer,
    type Invoice,
    type KeyOpinionLeader,
    InvoiceStatus,
    Platform,
} from '@/types/model';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Button,
    Column,
    ConfirmPopup,
    DataTable,
    DatePicker,
    Dialog,
    FileUpload,
    FloatLabel,
    Image,
    InputNumber,
    InputText,
    Message,
    Select,
    TabPanel,
    TabView,
    Tag,
    Textarea,
    Toolbar,
    useConfirm,
} from 'primevue';
import { computed, ref } from 'vue';

interface Props {
    item: Campaign;
    influencers: Influencer[];
    invoices: Invoice[];
}

const props = defineProps<Props>();
const confirm = useConfirm();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Campaign', href: route('campaign.index') },
    { title: props.item.name, href: route('campaign.show', props.item.id) },
];

// ── Summary card computed ─────────────────────────────────────────────────────

const kols = computed<CampaignKeyOpinionLeader[]>(() => props.item.key_opinion_leaders ?? []);

const totalKols = computed(() => kols.value.length);
const postedCount = computed(() => kols.value.filter((k: CampaignKeyOpinionLeader) => k.pivot.posted_at !== null).length);
const totalViews = computed(() => kols.value.reduce((s: number, k: CampaignKeyOpinionLeader) => s + (k.pivot.actual_views ?? 0), 0));
const totalLikes = computed(() => kols.value.reduce((s: number, k: CampaignKeyOpinionLeader) => s + (k.pivot.actual_likes ?? 0), 0));
const totalComments = computed(() => kols.value.reduce((s: number, k: CampaignKeyOpinionLeader) => s + (k.pivot.actual_comments ?? 0), 0));
const totalShares = computed(() => kols.value.reduce((s: number, k: CampaignKeyOpinionLeader) => s + (k.pivot.actual_shares ?? 0), 0));

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
    const attached = new Set(current.map((k) => k.id));
    const all: KeyOpinionLeader[] = selectedInfluencer.value.key_opinion_leaders ?? [];
    return all.filter((k) => !attached.has(k.id));
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

// ── Engagement dialog ─────────────────────────────────────────────────────────

const engageVisible = ref(false);
const engageKol = ref<CampaignKeyOpinionLeader | null>(null);

interface EngageForm {
    [key: string]: any;
    actual_views: number | null;
    actual_likes: number | null;
    actual_comments: number | null;
    actual_shares: number | null;
    posted_at: Date | null;
}

const engageForm = useForm<EngageForm>({
    actual_views: null,
    actual_likes: null,
    actual_comments: null,
    actual_shares: null,
    posted_at: null,
});

const openEngage = (kol: CampaignKeyOpinionLeader) => {
    engageKol.value = kol;
    engageForm.actual_views = kol.pivot.actual_views;
    engageForm.actual_likes = kol.pivot.actual_likes;
    engageForm.actual_comments = kol.pivot.actual_comments;
    engageForm.actual_shares = kol.pivot.actual_shares;
    engageForm.posted_at = kol.pivot.posted_at ? new Date(kol.pivot.posted_at) : null;
    engageVisible.value = true;
};

const submitEngage = () => {
    if (!engageKol.value) return;
    engageForm
        .transform((data: EngageForm) => ({
            ...data,
            posted_at: data.posted_at ? (data.posted_at as Date).toISOString().split('T')[0] : null,
        }))
        .put(route('campaign.kol.update', { campaign: props.item.id, keyOpinionLeader: engageKol.value.id }), {
            preserveScroll: true,
            onSuccess: () => {
                engageVisible.value = false;
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

// ── Invoice: create dialog ────────────────────────────────────────────────────

const invoiceVisible = ref(false);
const selectedKolForInvoice = ref<CampaignKeyOpinionLeader | null>(null);

interface InvoiceCreateForm {
    [key: string]: any;
    influencer_id: string;
    key_opinion_leader_id: string;
    amount: number | null;
    notes: string;
}

const invoiceForm = useForm<InvoiceCreateForm>({
    influencer_id: '',
    key_opinion_leader_id: '',
    amount: null,
    notes: '',
});

const invoiceKolLabel = (k: CampaignKeyOpinionLeader) => `${k.platform_name} · ${k.username}`;

const onInvoiceKolChange = () => {
    if (!selectedKolForInvoice.value) return;
    invoiceForm.influencer_id = selectedKolForInvoice.value.influencer?.id ?? '';
    invoiceForm.key_opinion_leader_id = selectedKolForInvoice.value.id;
    invoiceForm.amount = selectedKolForInvoice.value.endorsement_rate;
};

const openCreateInvoice = () => {
    invoiceForm.reset();
    selectedKolForInvoice.value = null;
    invoiceVisible.value = true;
};

const submitInvoice = () => {
    invoiceForm.post(route('campaign.invoice.store', props.item.id), {
        preserveScroll: true,
        onSuccess: () => {
            invoiceVisible.value = false;
        },
    });
};

// ── Invoice: status/proof update dialog ──────────────────────────────────────

const invoiceUpdateVisible = ref(false);
const editingInvoice = ref<Invoice | null>(null);

interface InvoiceUpdateForm {
    [key: string]: any;
    status: InvoiceStatus;
    amount: number | null;
    notes: string;
    proof: File | null;
}

const invoiceUpdateForm = useForm<InvoiceUpdateForm>({
    status: InvoiceStatus.Unpaid,
    amount: null,
    notes: '',
    proof: null,
});

const statusOptions = [
    { label: 'Unpaid', value: InvoiceStatus.Unpaid },
    { label: 'Pending', value: InvoiceStatus.Pending },
    { label: 'Paid', value: InvoiceStatus.Paid },
];

const openInvoiceUpdate = (inv: Invoice) => {
    editingInvoice.value = inv;
    invoiceUpdateForm.status = inv.status;
    invoiceUpdateForm.amount = inv.amount ? parseFloat(inv.amount) : null;
    invoiceUpdateForm.notes = inv.notes ?? '';
    invoiceUpdateForm.proof = null;
    invoiceUpdateVisible.value = true;
};

const submitInvoiceUpdate = () => {
    if (!editingInvoice.value) return;
    invoiceUpdateForm
        .transform((data: InvoiceUpdateForm) => ({ ...data, _method: 'PUT' }))
        .post(route('campaign.invoice.update', { campaign: props.item.id, invoice: editingInvoice.value.id }), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                invoiceUpdateVisible.value = false;
            },
        });
};

const destroyInvoice = (event: MouseEvent, inv: Invoice) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        message: 'Delete this invoice?',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: { label: 'Cancel', severity: 'secondary', outlined: true },
        acceptProps: { label: 'Delete', severity: 'danger' },
        accept: () => {
            router.delete(route('campaign.invoice.destroy', { campaign: props.item.id, invoice: inv.id }), {
                preserveScroll: true,
            });
        },
    });
};

const invoiceStatusSeverity = (status: InvoiceStatus) => {
    if (status === InvoiceStatus.Paid) return 'success';
    if (status === InvoiceStatus.Pending) return 'info';
    return 'warn';
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
                        <Button size="small" label="Back" icon="pi pi-arrow-left" severity="secondary" variant="outlined" />
                    </Link>
                </template>
            </Toolbar>

            <!-- Campaign header -->
            <div class="flex gap-x-6">
                <Image v-if="item.banner_path" :src="item.picture_url || ''" :alt="item.name" width="80" preview />
                <div class="flex flex-1 flex-col gap-y-1">
                    <h1 class="text-xl font-medium">{{ item.name }}</h1>
                    <p v-if="item.description" class="text-surface-500 text-sm">{{ item.description }}</p>
                    <div class="flex items-center gap-x-3 text-sm">
                        <Tag :value="item.status" class="capitalize" />
                        <span v-if="item.start_date || item.end_date" class="text-surface-400">
                            {{ dateHumanFormat(item.start_date) }} — {{ dateHumanFormat(item.end_date) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Summary cards -->
            <div v-if="totalKols > 0" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <div class="border-surface-200 dark:border-surface-700 flex flex-col gap-1 rounded-lg border p-3">
                    <span class="text-surface-400 text-xs uppercase tracking-wide">KOLs</span>
                    <span class="text-2xl font-semibold">{{ totalKols }}</span>
                </div>
                <div class="border-surface-200 dark:border-surface-700 flex flex-col gap-1 rounded-lg border p-3">
                    <span class="text-surface-400 text-xs uppercase tracking-wide">Posted</span>
                    <span class="text-2xl font-semibold">{{ postedCount }}</span>
                </div>
                <div class="border-surface-200 dark:border-surface-700 flex flex-col gap-1 rounded-lg border p-3">
                    <span class="text-surface-400 text-xs uppercase tracking-wide">Views</span>
                    <span class="text-2xl font-semibold">{{ digitFormatter(totalViews) }}</span>
                </div>
                <div class="border-surface-200 dark:border-surface-700 flex flex-col gap-1 rounded-lg border p-3">
                    <span class="text-surface-400 text-xs uppercase tracking-wide">Likes</span>
                    <span class="text-2xl font-semibold">{{ digitFormatter(totalLikes) }}</span>
                </div>
                <div class="border-surface-200 dark:border-surface-700 flex flex-col gap-1 rounded-lg border p-3">
                    <span class="text-surface-400 text-xs uppercase tracking-wide">Comments</span>
                    <span class="text-2xl font-semibold">{{ digitFormatter(totalComments) }}</span>
                </div>
                <div class="border-surface-200 dark:border-surface-700 flex flex-col gap-1 rounded-lg border p-3">
                    <span class="text-surface-400 text-xs uppercase tracking-wide">Shares</span>
                    <span class="text-2xl font-semibold">{{ digitFormatter(totalShares) }}</span>
                </div>
            </div>

            <!-- Tabs: KOLs / Invoices -->
            <TabView>
                <!-- KOL tab -->
                <TabPanel header="KOLs">
                    <div class="flex flex-col gap-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-surface-500 text-sm">{{ totalKols }} KOL{{ totalKols !== 1 ? 's' : '' }} attached</span>
                            <Button size="small" label="Add KOL" icon="pi pi-plus" severity="contrast" @click="openAttach" />
                        </div>

                        <DataTable :value="kols" show-gridlines size="small" :rows="10">
                            <template #empty>No KOLs attached to this campaign yet.</template>

                            <Column header="Influencer">
                                <template #body="{ data }">
                                    <Link :href="route('influencer.show', data.influencer?.id ?? '')" class="font-medium hover:text-green-600">
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
                                    <a :href="data.link" target="_blank" class="text-sm hover:text-teal-600">
                                        {{ data.username }}
                                    </a>
                                </template>
                            </Column>

                            <Column header="Deliverable">
                                <template #body="{ data }">{{ data.pivot.deliverable || '—' }}</template>
                            </Column>

                            <Column header="Posted">
                                <template #body="{ data }">
                                    <Tag v-if="data.pivot.posted_at" severity="success" :value="dateHumanFormat(data.pivot.posted_at)" />
                                    <span v-else class="text-surface-400 text-sm">—</span>
                                </template>
                            </Column>

                            <Column header="Views" class="text-end">
                                <template #body="{ data }">
                                    <span :class="data.pivot.actual_views !== null ? '' : 'text-surface-400 text-sm'">
                                        {{ data.pivot.actual_views !== null ? digitFormatter(data.pivot.actual_views) : '—' }}
                                    </span>
                                </template>
                            </Column>

                            <Column header="Likes" class="text-end">
                                <template #body="{ data }">
                                    <span :class="data.pivot.actual_likes !== null ? '' : 'text-surface-400 text-sm'">
                                        {{ data.pivot.actual_likes !== null ? digitFormatter(data.pivot.actual_likes) : '—' }}
                                    </span>
                                </template>
                            </Column>

                            <Column class="w-24 !text-end">
                                <template #body="{ data }">
                                    <div class="flex justify-end gap-1">
                                        <Button
                                            icon="pi pi-pencil"
                                            size="small"
                                            variant="outlined"
                                            severity="secondary"
                                            rounded
                                            v-tooltip.top="'Update engagement'"
                                            @click="openEngage(data)"
                                        />
                                        <Button
                                            icon="pi pi-times"
                                            size="small"
                                            variant="outlined"
                                            severity="danger"
                                            rounded
                                            v-tooltip.top="'Remove from campaign'"
                                            @click="detach($event, data)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </TabPanel>

                <!-- Invoices tab -->
                <TabPanel header="Invoices">
                    <div class="flex flex-col gap-3 pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-surface-500 text-sm">{{ invoices.length }} invoice{{ invoices.length !== 1 ? 's' : '' }}</span>
                            <Button
                                size="small"
                                label="Create Invoice"
                                icon="pi pi-plus"
                                severity="contrast"
                                @click="openCreateInvoice"
                                :disabled="kols.length === 0"
                            />
                        </div>

                        <DataTable :value="invoices" show-gridlines size="small" :rows="10">
                            <template #empty>No invoices for this campaign yet.</template>

                            <Column header="Influencer">
                                <template #body="{ data }">{{ data.influencer?.name ?? '—' }}</template>
                            </Column>

                            <Column header="KOL">
                                <template #body="{ data }">
                                    <span v-if="data.key_opinion_leader">
                                        {{ data.key_opinion_leader.platform_name }} · {{ data.key_opinion_leader.username }}
                                    </span>
                                    <span v-else class="text-surface-400 text-sm">—</span>
                                </template>
                            </Column>

                            <Column header="Amount" class="text-end">
                                <template #body="{ data }"> Rp {{ digitFormatter(parseFloat(data.amount)) }} </template>
                            </Column>

                            <Column header="Status">
                                <template #body="{ data }">
                                    <Tag :severity="invoiceStatusSeverity(data.status)" :value="data.status" class="capitalize" />
                                </template>
                            </Column>

                            <Column header="Paid">
                                <template #body="{ data }">
                                    <span v-if="data.paid_at">{{ dateHumanFormat(data.paid_at) }}</span>
                                    <span v-else class="text-surface-400 text-sm">—</span>
                                </template>
                            </Column>

                            <Column class="w-28 !text-end">
                                <template #body="{ data }">
                                    <div class="flex justify-end gap-1">
                                        <a :href="route('campaign.invoice.pdf', { campaign: item.id, invoice: data.id })" target="_blank">
                                            <Button
                                                icon="pi pi-file-pdf"
                                                size="small"
                                                variant="outlined"
                                                severity="secondary"
                                                rounded
                                                v-tooltip.top="'Download PDF'"
                                            />
                                        </a>
                                        <Button
                                            icon="pi pi-pencil"
                                            size="small"
                                            variant="outlined"
                                            severity="secondary"
                                            rounded
                                            v-tooltip.top="'Edit invoice'"
                                            @click="openInvoiceUpdate(data)"
                                        />
                                        <Button
                                            icon="pi pi-trash"
                                            size="small"
                                            variant="outlined"
                                            severity="danger"
                                            rounded
                                            v-tooltip.top="'Delete invoice'"
                                            @click="destroyInvoice($event, data)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </TabPanel>
            </TabView>
        </div>
    </AppLayout>

    <!-- Attach KOL dialog -->
    <Dialog v-model:visible="attachVisible" modal header="Add KOL to Campaign" :style="{ width: '36rem' }">
        <div class="flex flex-col gap-5 pb-6 pt-2">
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
                        @change="attachForm.key_opinion_leader_id = ''"
                    />
                    <label for="influencer" class="text-sm">Influencer</label>
                </FloatLabel>
            </div>
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
                        :disabled="!availableKols.length"
                    />
                    <label for="kol" class="text-sm">Platform account</label>
                </FloatLabel>
                <Message v-if="attachForm.errors.key_opinion_leader_id" severity="error" size="small" variant="simple">
                    {{ attachForm.errors.key_opinion_leader_id }}
                </Message>
                <p v-if="selectedInfluencer && !availableKols.length" class="text-surface-400 text-xs">
                    All KOL accounts for this influencer are already attached.
                </p>
            </div>
            <div v-if="attachForm.key_opinion_leader_id" class="grid gap-2">
                <FloatLabel variant="on">
                    <InputText id="deliverable" v-model="attachForm.deliverable" :fluid="true" placeholder="e.g. 1 TikTok video, 2 IG stories" />
                    <label for="deliverable" class="text-sm">Deliverable (optional)</label>
                </FloatLabel>
                <Message v-if="attachForm.errors.deliverable" severity="error" size="small" variant="simple">
                    {{ attachForm.errors.deliverable }}
                </Message>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" @click="attachVisible = false" />
            <Button label="Add" icon="pi pi-check" :disabled="!attachForm.key_opinion_leader_id || attachForm.processing" @click="submitAttach" />
        </div>
    </Dialog>

    <!-- Engagement metrics dialog -->
    <Dialog
        v-model:visible="engageVisible"
        modal
        :header="engageKol ? `${engageKol.platform_name} · ${engageKol.username}` : 'Engagement'"
        :style="{ width: '34rem' }"
    >
        <div class="flex flex-col gap-5 pb-6 pt-2">
            <div class="grid gap-2">
                <FloatLabel variant="on">
                    <DatePicker v-model="engageForm.posted_at" input-id="posted_at" :fluid="true" :manual-input="false" date-format="dd/mm/yy" />
                    <label for="posted_at" class="text-sm">Posted date</label>
                </FloatLabel>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <FloatLabel variant="on">
                        <InputNumber
                            v-model="engageForm.actual_views"
                            input-id="actual_views"
                            :fluid="true"
                            :min="0"
                            locale="id-ID"
                            :min-fraction-digits="0"
                        />
                        <label for="actual_views" class="text-sm">Actual views</label>
                    </FloatLabel>
                </div>
                <div class="grid gap-2">
                    <FloatLabel variant="on">
                        <InputNumber
                            v-model="engageForm.actual_likes"
                            input-id="actual_likes"
                            :fluid="true"
                            :min="0"
                            locale="id-ID"
                            :min-fraction-digits="0"
                        />
                        <label for="actual_likes" class="text-sm">Actual likes</label>
                    </FloatLabel>
                </div>
                <div class="grid gap-2">
                    <FloatLabel variant="on">
                        <InputNumber
                            v-model="engageForm.actual_comments"
                            input-id="actual_comments"
                            :fluid="true"
                            :min="0"
                            locale="id-ID"
                            :min-fraction-digits="0"
                        />
                        <label for="actual_comments" class="text-sm">Actual comments</label>
                    </FloatLabel>
                </div>
                <div class="grid gap-2">
                    <FloatLabel variant="on">
                        <InputNumber
                            v-model="engageForm.actual_shares"
                            input-id="actual_shares"
                            :fluid="true"
                            :min="0"
                            locale="id-ID"
                            :min-fraction-digits="0"
                        />
                        <label for="actual_shares" class="text-sm">Actual shares</label>
                    </FloatLabel>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" @click="engageVisible = false" />
            <Button label="Save" icon="pi pi-check" :disabled="engageForm.processing" @click="submitEngage" />
        </div>
    </Dialog>

    <!-- Create Invoice dialog -->
    <Dialog v-model:visible="invoiceVisible" modal header="Create Invoice" :style="{ width: '36rem' }">
        <div class="flex flex-col gap-5 pb-6 pt-2">
            <div class="grid gap-2">
                <FloatLabel variant="on">
                    <Select
                        v-model="selectedKolForInvoice"
                        input-id="inv-kol"
                        :options="kols"
                        :option-label="invoiceKolLabel"
                        :fluid="true"
                        placeholder="Select a KOL"
                        @change="onInvoiceKolChange"
                    />
                    <label for="inv-kol" class="text-sm">KOL</label>
                </FloatLabel>
                <Message v-if="invoiceForm.errors.key_opinion_leader_id" severity="error" size="small" variant="simple">
                    {{ invoiceForm.errors.key_opinion_leader_id }}
                </Message>
            </div>
            <div v-if="selectedKolForInvoice" class="grid gap-2">
                <FloatLabel variant="on">
                    <InputNumber v-model="invoiceForm.amount" input-id="inv-amount" :fluid="true" :min="0" locale="id-ID" :min-fraction-digits="0" />
                    <label for="inv-amount" class="text-sm">Amount (Rp)</label>
                </FloatLabel>
                <Message v-if="invoiceForm.errors.amount" severity="error" size="small" variant="simple">
                    {{ invoiceForm.errors.amount }}
                </Message>
            </div>
            <div v-if="selectedKolForInvoice" class="grid gap-2">
                <FloatLabel variant="on">
                    <Textarea id="inv-notes" v-model="invoiceForm.notes" :fluid="true" rows="3" style="resize: none" />
                    <label for="inv-notes" class="text-sm">Notes (optional)</label>
                </FloatLabel>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" @click="invoiceVisible = false" />
            <Button label="Create" icon="pi pi-check" :disabled="!selectedKolForInvoice || invoiceForm.processing" @click="submitInvoice" />
        </div>
    </Dialog>

    <!-- Update Invoice dialog -->
    <Dialog v-model:visible="invoiceUpdateVisible" modal header="Update Invoice" :style="{ width: '34rem' }">
        <div class="flex flex-col gap-5 pb-6 pt-2">
            <div class="grid gap-2">
                <FloatLabel variant="on">
                    <Select
                        v-model="invoiceUpdateForm.status"
                        input-id="inv-status"
                        :options="statusOptions"
                        option-label="label"
                        option-value="value"
                        :fluid="true"
                    />
                    <label for="inv-status" class="text-sm">Status</label>
                </FloatLabel>
                <Message v-if="invoiceUpdateForm.errors.status" severity="error" size="small" variant="simple">
                    {{ invoiceUpdateForm.errors.status }}
                </Message>
            </div>
            <div class="grid gap-2">
                <FloatLabel variant="on">
                    <InputNumber
                        v-model="invoiceUpdateForm.amount"
                        input-id="inv-upd-amount"
                        :fluid="true"
                        :min="0"
                        locale="id-ID"
                        :min-fraction-digits="0"
                    />
                    <label for="inv-upd-amount" class="text-sm">Amount (Rp)</label>
                </FloatLabel>
            </div>
            <div class="grid gap-2">
                <FloatLabel variant="on">
                    <Textarea id="inv-upd-notes" v-model="invoiceUpdateForm.notes" :fluid="true" rows="3" style="resize: none" />
                    <label for="inv-upd-notes" class="text-sm">Notes</label>
                </FloatLabel>
            </div>
            <div class="grid gap-2">
                <label class="text-surface-500 text-sm">Payment proof (optional)</label>
                <FileUpload
                    mode="basic"
                    :auto="false"
                    accept="image/*,.pdf"
                    :max-file-size="5242880"
                    choose-label="Choose proof file"
                    @select="
                        (e) => {
                            invoiceUpdateForm.proof = e.files[0];
                        }
                    "
                />
                <Message v-if="invoiceUpdateForm.errors.proof" severity="error" size="small" variant="simple">
                    {{ invoiceUpdateForm.errors.proof }}
                </Message>
            </div>
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Cancel" severity="secondary" @click="invoiceUpdateVisible = false" />
            <Button label="Save" icon="pi pi-check" :disabled="invoiceUpdateForm.processing" @click="submitInvoiceUpdate" />
        </div>
    </Dialog>

    <ConfirmPopup />
</template>
