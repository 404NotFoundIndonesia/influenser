<script setup lang="ts">
import { ref, watch } from 'vue';
import {
    Dialog, Button, FloatLabel, InputText, Message,
    Textarea, DatePicker,
} from 'primevue';
import { useForm } from '@inertiajs/vue3';
import { Campaign } from '@/types/model';

const visible = ref<boolean>(false);
const campaign = ref<Campaign | null>(null);

interface CampaignForm {
    [key: string]: any;

    _method: 'POST' | 'PUT';
    name: string;
    description: string;
    start_date: Date | null;
    end_date: Date | null;
    period: Date[];
}

const form = useForm<CampaignForm>({
    _method: 'POST',
    name: '',
    description: '',
    start_date: null,
    end_date: null,
    period: [],
});

const open = (item: Campaign | null) => {
    visible.value = true;
    if (item === null)
        return;

    campaign.value = item;
    form._method = 'PUT';
    form.name = item.name;
    form.description = item.description ?? '';
    form.start_date = item.start_date;
    form.end_date = item.end_date;
    if (item.start_date) {
        form.period[0] = new Date(item.start_date);
    }
    if (item.end_date) {
        form.period[1] = new Date(item.end_date);
    }
};

const close = () => {
    visible.value = false;
};

const submit = () => {
    const url = campaign.value === null ?
        route('campaign.store') :
        route('campaign.update', campaign.value.id);

    form.transform(data => ({
        ...data,
        start_date: data.period[0] ? new Date(data.period[0]) : null,
        end_date: data.period[1] ? new Date(data.period[1]) : null,
    })).post(url, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: close,
    });
};

defineExpose({
    open,
});

watch(visible, (visibility: boolean) => {
    if (!visibility) {
        campaign.value = null;
        form.reset();
        form.clearErrors();
    }
});
</script>

<template>
    <Dialog v-model:visible="visible" modal header="Campaign" :style="{ width: '50rem' }">
        <div class="flex flex-col gap-6 pt-2 pb-8">
            <div class="grid md:grid-cols-2 gap-6">
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
                <div class="grid gap-2">
                    <FloatLabel variant="on">
                        <DatePicker fluid v-model="form.period" input-id="start_date"
                                    selection-mode="range" :manual-input="false" />
                        <label for="start_date" class="text-sm">Period</label>
                    </FloatLabel>
                    <Message v-if="form.errors.start_date" severity="error" size="small" variant="simple">
                        {{ form.errors.start_date }}
                    </Message>
                </div>
            </div>
            <div class="grid gap-2">
                <FloatLabel variant="on">
                    <Textarea fluid id="description"
                              v-model="form.description" rows="3" cols="30"
                              style="resize: none" />
                    <label for="description" class="text-sm">Description</label>
                </FloatLabel>
                <Message v-if="form.errors.description" severity="error" size="small" variant="simple">
                    {{ form.errors.description }}
                </Message>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <Button type="button" label="Cancel" severity="secondary" @click="close"></Button>
            <Button type="button" label="Submit" :disabled="form.processing" @click="submit"></Button>
        </div>
    </Dialog>
</template>
