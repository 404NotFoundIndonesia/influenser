<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import {
    Toolbar, Image, Textarea, Dialog, Button, Fieldset,
    useConfirm, ConfirmPopup, FloatLabel, InputText, InputNumber,
    FileUpload, Message, InputGroup, InputGroupAddon
} from 'primevue';
import { formatBytes } from '@/lib/utils';
import { Platform } from '@/types/model';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Influencer',
        href: route('influencer.index')
    },
    {
        title: 'New',
        href: route('influencer.create')
    }
];

interface KeyOpinionLeaderForm {
    username: string,
    platform: Platform,
    link: string,
    engagement_rate: number,
    followers: number,
    following: number,
    views: number,
    likes: number,
    shares: number,
    comments: number,
    avg_views: number,
    avg_likes: number,
    avg_shares: number,
    avg_comments: number,
    endorsement_rate: number,
}

interface InfluencerForm {
    [key: string]: any;

    name: string,
    bio: string | null,
    location: string | null,
    phone: string | null,
    whatsapp: string | null,
    email: string | null,
    status: 'active' | 'inactive' | 'banned',
    photo: File | null,
    keyOpinionLeaders: KeyOpinionLeaderForm[],
}

const form = useForm<InfluencerForm>({
    name: '',
    bio: null,
    location: 'Indonesia',
    phone: null,
    whatsapp: null,
    email: null,
    status: 'active',
    photo: null,
    keyOpinionLeaders: []
});

const newKOLDialog = ref<boolean>(false);
const confirm = useConfirm();

const addKOL = (platform: Platform) => {
    form.keyOpinionLeaders.push(<KeyOpinionLeaderForm>{
        username: '',
        platform: platform,
        link: '',
        engagement_rate: 0,
        followers: 0,
        following: 0,
        views: 0,
        likes: 0,
        shares: 0,
        comments: 0,
        avg_views: 0,
        avg_likes: 0,
        avg_shares: 0,
        avg_comments: 0,
        endorsement_rate: 0,
    });
    newKOLDialog.value = false;
};

const removeKOL = (event: MouseEvent, index: number) => {
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
            severity: 'danger'
        },
        accept: () => {
            setTimeout(() => {
                form.keyOpinionLeaders = form.keyOpinionLeaders.filter((_, i: number) => i !== index);
            }, 100);
        }
    });
};

const removePhoto = (length: number, callback: (index: number) => void): void => {
    form.photo = null;
    for (let i = length - 1; i >= 0; i--)
        callback(i);
};

const submit = () => {
    form.post(route('influencer.store'), {
        preserveState: true,
        preserveScroll: true,
    });
};
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
                    <Button size="small" label="Submit"
                            :disabled="form.processing || form.keyOpinionLeaders.length === 0" @click="submit" />
                </template>
            </Toolbar>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="flex flex-col gap-6 md:col-span-2">
                    <div class="grid gap-2">
                        <FloatLabel variant="on">
                            <InputText :fluid="true" :autofocus="true" id="name" v-model="form.name" type="text"
                                       autocomplete="off" />
                            <label for="name" class="text-sm">Name</label>
                        </FloatLabel>
                        <Message v-if="form.errors.name" severity="error" size="small" variant="simple">
                            {{ form.errors.name }}
                        </Message>
                    </div>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="grid gap-2">
                            <FloatLabel variant="on">
                                <InputText :fluid="true" id="phone" v-model="form.phone" type="tel"
                                           autocomplete="off" />
                                <label for="phone" class="text-sm">Phone Number (optional)</label>
                            </FloatLabel>
                            <Message v-if="form.errors.phone" severity="error" size="small" variant="simple">
                                {{ form.errors.phone }}
                            </Message>
                        </div>
                        <div class="grid gap-2">
                            <FloatLabel variant="on">
                                <InputText :fluid="true" id="whatsapp" v-model="form.whatsapp" type="tel"
                                           autocomplete="off" />
                                <label for="whatsapp" class="text-sm">WhatsApp (optional)</label>
                            </FloatLabel>
                            <Message v-if="form.errors.whatsapp" severity="error" size="small" variant="simple">
                                {{ form.errors.whatsapp }}
                            </Message>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <FloatLabel variant="on">
                            <InputText :fluid="true" id="email" v-model="form.email" type="email" autocomplete="off" />
                            <label for="email" class="text-sm">Email address (optional)</label>
                        </FloatLabel>
                        <Message v-if="form.errors.email" severity="error" size="small" variant="simple">
                            {{ form.errors.email }}
                        </Message>
                    </div>
                    <div class="grid gap-2">
                        <FloatLabel variant="on">
                            <InputText :fluid="true" id="location" v-model="form.location" type="text"
                                       autocomplete="off" />
                            <label for="location" class="text-sm">Location</label>
                        </FloatLabel>
                        <Message v-if="form.errors.location" severity="error" size="small" variant="simple">
                            {{ form.errors.location }}
                        </Message>
                    </div>
                    <div class="grid gap-2">
                        <FloatLabel variant="on">
                            <Textarea id="bio" v-model="form.bio" rows="5" cols="30" style="resize: none"
                                      :fluid="true" />
                            <label for="bio" class="text-sm">Bio (optional)</label>
                        </FloatLabel>
                        <Message v-if="form.errors.bio" severity="error" size="small" variant="simple">
                            {{ form.errors.bio }}
                        </Message>
                    </div>
                    <div class="flex justify-between items-center">
                        <h1 class="font-semibold">Platform</h1>
                        <Button
                            label="New Platform" size="small" @click="() => newKOLDialog = true"
                            severity="secondary" variant="outlined" />
                    </div>
                    <div class="flex flex-col gap-y-5">
                        <Fieldset v-for="(keyOpinionLeader, index) in form.keyOpinionLeaders" :key="index">
                            <template #legend>
                                <div class="flex items-center pl-2 gap-x-2">
                                    <div class="flex items-center">
                                        <i :class="`pi pi-${keyOpinionLeader.platform.toLowerCase()}`"
                                           style="font-size: 1.5rem"></i>
                                        <span class="font-bold p-2">{{ keyOpinionLeader.platform }}</span>
                                    </div>
                                    <Button icon="pi pi-trash" size="small"
                                            variant="outlined"
                                            @click="removeKOL($event, index)"
                                            severity="danger" rounded></Button>
                                </div>
                            </template>

                            <div class="flex flex-col gap-6">
                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div class="grid gap-2 lg:col-span-2">
                                        <InputGroup>
                                            <InputGroupAddon>
                                                <span class="px-2">https://</span>
                                            </InputGroupAddon>
                                            <FloatLabel variant="on">
                                                <InputText :fluid="true" :id="`${index}_link`" type="text"
                                                           v-model="form.keyOpinionLeaders[index].link"
                                                           autocomplete="off" size="small" />
                                                <label :for="`${index}_link`" class="text-sm">Link</label>
                                            </FloatLabel>
                                        </InputGroup>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.link']"
                                                 severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.link'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputText :fluid="true" :id="`${index}_username`" type="text"
                                                       v-model="form.keyOpinionLeaders[index].username"
                                                       autocomplete="off" />
                                            <label :for="`${index}_username`" class="text-sm">Username</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.username']"
                                                 severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.username'] }}
                                        </Message>
                                    </div>
                                </div>
                                <div class="grid md:grid-cols-3 gap-6">
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_followers`" locale="id-ID" :minFractionDigits="0" v-model="form.keyOpinionLeaders[index].followers" />
                                            <label :for="`${index}_followers`" class="text-sm">Followers</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.followers']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.followers'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_following`" locale="id-ID" :minFractionDigits="0" v-model="form.keyOpinionLeaders[index].following" />
                                            <label :for="`${index}_following`" class="text-sm">Following</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.following']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.following'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber
                                                :fluid="true" :inputId="`${index}_engagement_rate`"
                                                locale="id-ID" :minFractionDigits="2" v-model="form.keyOpinionLeaders[index].engagement_rate"
                                                :min="0" :max="100" suffix="%" />
                                            <label :for="`${index}_engagement_rate`" class="text-sm">Engagement Rate</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.engagement_rate']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.engagement_rate'] }}
                                        </Message>
                                    </div>
                                </div>
                                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_views`" locale="id-ID" :minFractionDigits="0" v-model="form.keyOpinionLeaders[index].views" />
                                            <label :for="`${index}_views`" class="text-sm">Total Views</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.views']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.views'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_likes`" locale="id-ID" :minFractionDigits="0" v-model="form.keyOpinionLeaders[index].likes" />
                                            <label :for="`${index}_likes`" class="text-sm">Total Likes</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.likes']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.likes'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_shares`" locale="id-ID" :minFractionDigits="0" v-model="form.keyOpinionLeaders[index].shares" />
                                            <label :for="`${index}_shares`" class="text-sm">Total Shares</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.shares']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.shares'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_comments`" locale="id-ID" :minFractionDigits="0" v-model="form.keyOpinionLeaders[index].comments" />
                                            <label :for="`${index}_comments`" class="text-sm">Total Comments</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.comments']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.comments'] }}
                                        </Message>
                                    </div>
                                </div>
                                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_avg_views`" locale="id-ID" v-model="form.keyOpinionLeaders[index].avg_views" :min-fraction-digits="2" />
                                            <label :for="`${index}_avg_views`" class="text-sm">Average Views</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.avg_views']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.avg_views'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_avg_likes`" locale="id-ID" v-model="form.keyOpinionLeaders[index].avg_likes" :min-fraction-digits="2" />
                                            <label :for="`${index}_avg_likes`" class="text-sm">Average Likes</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.avg_likes']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.avg_likes'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_avg_shares`" locale="id-ID" v-model="form.keyOpinionLeaders[index].avg_shares" :min-fraction-digits="2" />
                                            <label :for="`${index}_avg_shares`" class="text-sm">Average Shares</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.avg_shares']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.avg_shares'] }}
                                        </Message>
                                    </div>
                                    <div class="grid gap-2">
                                        <FloatLabel variant="on">
                                            <InputNumber :fluid="true" :min="0" :inputId="`${index}_avg_comments`" locale="id-ID" v-model="form.keyOpinionLeaders[index].avg_comments" :min-fraction-digits="2" />
                                            <label :for="`${index}_avg_comments`" class="text-sm">Average Comments</label>
                                        </FloatLabel>
                                        <Message v-if="form.errors['keyOpinionLeaders.' + index + '.avg_comments']" severity="error" size="small" variant="simple">
                                            {{ form.errors['keyOpinionLeaders.' + index + '.avg_comments'] }}
                                        </Message>
                                    </div>
                                </div>
                                <div class="grid gap-2">
                                    <FloatLabel variant="on">
                                        <InputNumber
                                            :fluid="true" :min="0" :max-fraction-digits="0"
                                            :inputId="`${index}_endorsement_rate`"
                                            mode="currency" currency="IDR" locale="id-ID"
                                            v-model="form.keyOpinionLeaders[index].endorsement_rate" />
                                        <label :for="`${index}_endorsement_rate`" class="text-sm">Endorsement Rate</label>
                                    </FloatLabel>
                                    <Message v-if="form.errors['keyOpinionLeaders.' + index + '.endorsement_rate']" severity="error" size="small" variant="simple">
                                        {{ form.errors['keyOpinionLeaders.' + index + '.endorsement_rate'] }}
                                    </Message>
                                </div>
                            </div>
                        </Fieldset>
                    </div>
                </div>
                <div>
                    <FileUpload
                        accept="image/*"
                        @select="event => form.photo = event.files[event.files.length - 1]"
                        :show-cancel-button="false"
                        :show-upload-button="false"
                        :maxFileSize="1_000_000">
                        <template #header="{ chooseCallback }">
                            <div class="flex flex-wrap justify-between items-center flex-1 gap-4">
                                <div class="flex gap-2">
                                    <Button @click="chooseCallback()" icon="pi pi-images" rounded outlined
                                            severity="secondary" :disabled="form.photo !== null"></Button>
                                </div>
                            </div>
                        </template>
                        <template #content="{ files, removeFileCallback, messages }">
                            <div class="flex flex-col gap-8 pt-4">
                                <Message v-for="message in messages ?? []" :key="message" severity="error" size="small"
                                         variant="simple">
                                    {{ message }}
                                </Message>
                                <div v-if="form.photo">
                                    <div
                                        class="p-8 rounded rounded-border flex flex-col border border-surface items-center gap-2">
                                        <div class="relative">
                                            <Image :src="form.photo?.objectURL" :alt="form.photo?.name" width="250"
                                                   preview />
                                            <div class="absolute top-2 right-2">
                                                <Button
                                                    size="small"
                                                    @click="() => removePhoto(files.length, removeFileCallback)"
                                                    icon="pi pi-trash" severity="danger" rounded />
                                            </div>
                                        </div>
                                        <span
                                            class="text-sm font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{ form.photo?.name
                                            }}</span>
                                        <small class="text-xs">{{ formatBytes(form.photo?.size) }}</small>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template #empty>
                            <div class="flex items-center justify-center flex-col">
                                <i class="pi pi-cloud-upload !border-2 !rounded-full !p-8 !text-xl !text-muted-color" />
                                <p class="mt-6 mb-0">Drag and drop files to here to upload.</p>
                            </div>
                        </template>
                    </FileUpload>
                </div>
            </div>
        </div>

        <Dialog v-model:visible="newKOLDialog" modal header="Choose platform" :style="{ width: '50rem' }">
            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                <template v-for="platform in Object.keys(Platform)" :key="platform">
                    <Button variant="outlined" class="!border-2 cursor-pointer"
                            :disabled="form.keyOpinionLeaders.map(kol => kol.platform).includes(platform as Platform)"
                            @click="addKOL(platform as Platform)">
                        <i :class="`pi pi-${platform.toLowerCase()}`" style="font-size: 1.5rem"></i>
                        <span>{{ platform }}</span>
                    </Button>
                </template>
            </div>
        </Dialog>

        <ConfirmPopup />
    </AppLayout>
</template>
