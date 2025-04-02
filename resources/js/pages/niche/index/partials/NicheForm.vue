<script setup lang="ts">
import { ref, watch } from 'vue';
import {
    Dialog, Button, FloatLabel, InputText, Message,
    Textarea,
} from 'primevue';
import { useForm } from '@inertiajs/vue3';
import { Niche } from '@/types/model';

const visible = ref<boolean>(false);
const niche = ref<Niche | null>(null);

interface NicheForm {
    [key: string]: any;

    _method: 'POST' | 'PUT';
    name: string;
    description: string;
    active: boolean;
}

const form = useForm<NicheForm>({
    _method: 'POST',
    name: '',
    description: '',
    active: true,
});

const open = (item: Niche | null) => {
    visible.value = true;
    if (item === null)
        return;

    niche.value = item;
    form._method = 'PUT';
    form.name = item.name;
    form.description = item.description ?? '';
    form.active = item.active;
};

const close = () => {
    visible.value = false;
};

const submit = () => {
    const url = niche.value === null ?
        route('niche.store') :
        route('niche.update', niche.value.id);

    form.post(url, {
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
        niche.value = null;
        form.reset();
        form.clearErrors();
    }
});
</script>

<template>
    <Dialog v-model:visible="visible" modal header="Niche" :style="{ width: '50rem' }">
        <div class="flex flex-col gap-6 pt-2 pb-8">
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
