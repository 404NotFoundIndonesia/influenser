<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import FloatLabel from 'primevue/floatlabel';
import InputText from 'primevue/inputtext';
import FileUpload from 'primevue/fileupload';
import Message from 'primevue/message';
import { Toolbar, Image } from 'primevue';
import Button from 'primevue/button';
import { formatBytes } from '@/lib/utils';
import { Influencer } from '@/types/model';

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
    },
    {
        title: 'Edit',
        href: route('influencer.edit', props.item.id)
    },
];

const form = useForm({
    _method: 'PUT',
    name: props.item.name,
    bio: props.item.bio,
    location: props.item.location,
    phone: props.item.phone,
    whatsapp: props.item.whatsapp,
    email: props.item.email,
    status: props.item.status,
    photo: null,
});

const removePhoto = (length: number, callback: (index:number) => void): void => {
    form.photo = null;
    for (let i = length - 1; i >= 0; i--)
        callback(i);
};

const submit = () => {
    form.post(route('influencer.update', props.item.id), {
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
                    <Button size="small" label="Submit" :disabled="form.processing" @click="submit" />
                </template>
            </Toolbar>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-6">
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
                </div>
                <div>
                    <FileUpload
                        accept="image/*"
                        @select="event => form.photo = event.files[event.files.length - 1]"
                        :show-cancel-button="false"
                        :show-upload-button="false"
                        :maxFileSize="1_000_000">
                        <template #content="{ files, removeFileCallback, messages }">
                            <div class="flex flex-col gap-8 pt-4">
                                <Message v-for="message in messages ?? []" :key="message" severity="error" size="small" variant="simple">
                                    {{ message }}
                                </Message>
                                <div v-if="form.photo">
                                    <div class="p-8 rounded rounded-border flex flex-col border border-surface items-center gap-2">
                                        <div class="relative">
                                            <Image :src="form.photo?.objectURL" :alt="form.photo?.name" width="250" preview />
                                            <div class="absolute top-2 right-2">
                                                <Button
                                                    size="small" @click="() => removePhoto(files.length, removeFileCallback)"
                                                    icon="pi pi-times" severity="danger" rounded />
                                            </div>
                                        </div>
                                        <span class="text-sm font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{ form.photo?.name }}</span>
                                        <small class="text-xs">{{ formatBytes(form.photo?.size) }}</small>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template #empty>
                            <template v-if="item.profile_picture_path">
                                <div class="p-8 rounded rounded-border flex flex-col border border-surface items-center gap-2">
                                    <div>
                                        <Image :src="item.picture_url || ''" :alt="item.name" width="250" preview />
                                    </div>
                                </div>
                            </template>
                            <div class="flex items-center justify-center flex-col" v-else>
                                <i class="pi pi-cloud-upload !border-2 !rounded-full !p-8 !text-xl !text-muted-color" />
                                <p class="mt-6 mb-0">Drag and drop files to here to upload.</p>
                            </div>
                        </template>
                    </FileUpload>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
