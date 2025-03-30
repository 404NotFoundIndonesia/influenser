<script setup lang="ts">
import { Influencer, KeyOpinionLeader } from '@/types/model';
import {
    Panel, Button, Tag, useConfirm, ConfirmPopup
} from 'primevue';
import { dateHumanFormatWithTime, digitFormatter, shortNumberFormatter } from '@/lib/utils';
import { router } from '@inertiajs/vue3';


interface Props {
    influencer: Influencer;
}

const props = defineProps<Props>();
const confirm = useConfirm();
const destroy = (event: MouseEvent, item: KeyOpinionLeader) => {
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
            router.delete(route('influencer.key-opinion-leader.destroy', {
                influencer: props.influencer.id,
                keyOpinionLeader: item.id,
            }), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <template v-for="keyOpinionLeader in influencer.key_opinion_leaders" :key="keyOpinionLeader.id">
            <Panel>
                <template #header>
                    <div class="flex items-center gap-x-2 p-2">
                        <div>
                            <i :class="`pi pi-${keyOpinionLeader.platform.toLowerCase()}`"
                               style="font-size: 1.5rem"></i>
                        </div>
                        <div class="flex flex-col">
                            <h1 class="font-bold">{{ keyOpinionLeader.platform_name }}</h1>
                            <a :href="keyOpinionLeader.link" target="_blank" class="text-xs hover:text-teal-600">
                                {{ keyOpinionLeader.username }}
                            </a>
                        </div>
                    </div>
                </template>
                <template #footer>
                    <div class="flex flex-wrap items-end justify-between gap-4 text-xs">
                        <div class="flex items-center gap-2">
                        </div>
                        <span class="text-surface-500 dark:text-surface-400">Updated {{ dateHumanFormatWithTime(keyOpinionLeader.updated_at) }}</span>
                    </div>
                </template>
                <template #icons>
                    <Button icon="pi pi-trash" size="small"
                            variant="outlined"
                            @click="destroy($event, keyOpinionLeader)"
                            severity="danger" rounded></Button>
                </template>

                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-2">
                    <div class="flex flex-col items-center">
                        <h1 class="font-semibold text-lg">{{ shortNumberFormatter(keyOpinionLeader.followers) }}</h1>
                        <small>followers</small>
                    </div>
                    <div class="hidden md:flex flex-col items-center">
                        <h1 class="font-semibold text-lg">{{ shortNumberFormatter(keyOpinionLeader.following) }}</h1>
                        <small>following</small>
                    </div>
                    <div class="flex flex-col items-center">
                        <h1 class="font-semibold text-lg">{{ keyOpinionLeader.engagement_rate.toString().replaceAll('.', ',') }}%</h1>
                        <small>engagement</small>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2 mt-5">
                    <Tag
                        v-tooltip.bottom="`${digitFormatter(keyOpinionLeader.views)} views`"
                        class="text-xs"
                        icon="pi pi-eye"
                        severity="secondary"
                        :value="shortNumberFormatter(keyOpinionLeader.views)"></Tag>
                    <Tag
                        v-tooltip.bottom="`${digitFormatter(keyOpinionLeader.likes)} likes`"
                        class="text-xs"
                        icon="pi pi-thumbs-up"
                        severity="secondary"
                        :value="shortNumberFormatter(keyOpinionLeader.likes)"></Tag>
                    <Tag
                        v-tooltip.bottom="`${digitFormatter(keyOpinionLeader.comments)} comments`"
                        class="text-xs"
                        icon="pi pi-comments"
                        severity="secondary"
                        :value="shortNumberFormatter(keyOpinionLeader.comments)"></Tag>
                    <Tag
                        v-tooltip.bottom="`${digitFormatter(keyOpinionLeader.shares)} shares`"
                        class="text-xs"
                        icon="pi pi-share-alt"
                        severity="secondary"
                        :value="shortNumberFormatter(keyOpinionLeader.shares)"></Tag>
                </div>
            </Panel>
        </template>
    </div>
    <ConfirmPopup />
</template>
