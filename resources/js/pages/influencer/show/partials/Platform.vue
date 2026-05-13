<script setup lang="ts">
import { dateHumanFormatWithTime, digitFormatter, shortNumberFormatter } from '@/lib/utils';
import { Influencer, KeyOpinionLeader, Platform } from '@/types/model';
import { router } from '@inertiajs/vue3';
import { Button, ConfirmPopup, Menu, Panel, Tag, useConfirm } from 'primevue';
import type { MenuItem } from 'primevue/menuitem';
import { ref } from 'vue';

interface Props {
    influencer: Influencer;
}

const props = defineProps<Props>();
const confirm = useConfirm();

const apifySupportedPlatforms = [Platform.TikTok, Platform.Instagram, Platform.Youtube, Platform.Facebook];

const syncMenuRef = ref<Record<string, any>>({});

const syncMenuItems = (kol: KeyOpinionLeader): MenuItem[] => {
    const items: MenuItem[] = [
        {
            label: 'Sync via CreatorDB',
            icon: 'pi pi-database',
            command: () => {
                router.post(
                    route('influencer.kol.sync.creator-db', {
                        influencer: props.influencer.id,
                        keyOpinionLeader: kol.id,
                    }),
                    {},
                    { preserveScroll: true },
                );
            },
        },
    ];

    if (apifySupportedPlatforms.includes(kol.platform as Platform)) {
        items.push({
            label: 'Sync via Apify',
            icon: 'pi pi-bolt',
            command: () => {
                router.post(
                    route('influencer.kol.sync.apify', {
                        influencer: props.influencer.id,
                        keyOpinionLeader: kol.id,
                    }),
                    {},
                    { preserveScroll: true },
                );
            },
        });
    }

    return items;
};

const toggleSyncMenu = (event: MouseEvent, kolId: string) => {
    syncMenuRef.value[kolId]?.toggle(event);
};

const destroy = (event: MouseEvent, item: KeyOpinionLeader) => {
    confirm.require({
        target: event.currentTarget as HTMLElement,
        message: 'Are you sure you want to delete?',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true,
        },
        acceptProps: {
            label: 'Delete',
            severity: 'danger',
        },
        accept: () => {
            router.delete(
                route('influencer.key-opinion-leader.destroy', {
                    influencer: props.influencer.id,
                    keyOpinionLeader: item.id,
                }),
                {
                    preserveState: true,
                    preserveScroll: true,
                },
            );
        },
    });
};
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <template v-for="keyOpinionLeader in influencer.key_opinion_leaders" :key="keyOpinionLeader.id">
            <Panel>
                <template #header>
                    <div class="flex items-center gap-x-2 p-2">
                        <div>
                            <i :class="`pi pi-${keyOpinionLeader.platform.toLowerCase()}`" style="font-size: 1.5rem"></i>
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
                        <div class="flex items-center gap-2"></div>
                        <span class="text-surface-500 dark:text-surface-400">Updated {{ dateHumanFormatWithTime(keyOpinionLeader.updated_at) }}</span>
                    </div>
                </template>
                <template #icons>
                    <div class="flex items-center gap-1">
                        <Button
                            icon="pi pi-sync"
                            size="small"
                            variant="outlined"
                            rounded
                            :loading="keyOpinionLeader.is_syncing"
                            :disabled="keyOpinionLeader.is_syncing"
                            v-tooltip.bottom="'Sync'"
                            aria-haspopup="true"
                            :aria-controls="`sync-menu-${keyOpinionLeader.id}`"
                            @click="toggleSyncMenu($event, keyOpinionLeader.id)"
                        />
                        <Menu
                            :ref="
                                (el) => {
                                    if (el) syncMenuRef[keyOpinionLeader.id] = el;
                                }
                            "
                            :id="`sync-menu-${keyOpinionLeader.id}`"
                            :model="syncMenuItems(keyOpinionLeader)"
                            :popup="true"
                        />
                        <Button
                            icon="pi pi-trash"
                            size="small"
                            variant="outlined"
                            @click="destroy($event, keyOpinionLeader)"
                            severity="danger"
                            rounded
                        ></Button>
                    </div>
                </template>

                <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3">
                    <div class="flex flex-col items-center">
                        <h1 class="text-lg font-semibold">{{ shortNumberFormatter(keyOpinionLeader.followers) }}</h1>
                        <small>followers</small>
                    </div>
                    <div class="hidden flex-col items-center md:flex">
                        <h1 class="text-lg font-semibold">{{ shortNumberFormatter(keyOpinionLeader.following) }}</h1>
                        <small>following</small>
                    </div>
                    <div class="flex flex-col items-center">
                        <h1 class="text-lg font-semibold">{{ keyOpinionLeader.engagement_rate.toString().replaceAll('.', ',') }}%</h1>
                        <small>engagement</small>
                    </div>
                </div>

                <div class="mt-5 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                    <Tag
                        v-tooltip.bottom="`${digitFormatter(keyOpinionLeader.views)} views`"
                        class="text-xs"
                        icon="pi pi-eye"
                        severity="secondary"
                        :value="shortNumberFormatter(keyOpinionLeader.views)"
                    ></Tag>
                    <Tag
                        v-tooltip.bottom="`${digitFormatter(keyOpinionLeader.likes)} likes`"
                        class="text-xs"
                        icon="pi pi-thumbs-up"
                        severity="secondary"
                        :value="shortNumberFormatter(keyOpinionLeader.likes)"
                    ></Tag>
                    <Tag
                        v-tooltip.bottom="`${digitFormatter(keyOpinionLeader.comments)} comments`"
                        class="text-xs"
                        icon="pi pi-comments"
                        severity="secondary"
                        :value="shortNumberFormatter(keyOpinionLeader.comments)"
                    ></Tag>
                    <Tag
                        v-tooltip.bottom="`${digitFormatter(keyOpinionLeader.shares)} shares`"
                        class="text-xs"
                        icon="pi pi-share-alt"
                        severity="secondary"
                        :value="shortNumberFormatter(keyOpinionLeader.shares)"
                    ></Tag>
                </div>
            </Panel>
        </template>
    </div>
    <ConfirmPopup />
</template>
