<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { CampaignStatus, Platform } from '@/types/model';
import { Head, Link } from '@inertiajs/vue3';
import Chart from 'primevue/chart';
import { computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

const props = defineProps<{
    totalInfluencers: number;
    totalCampaigns: number;
    activeCampaigns: number;
    totalInvoiced: number;
    totalPaid: number;
    topInfluencers: {
        id: string;
        name: string;
        picture_url: string;
        avg_engagement: number;
        kol_count: number;
        platforms: Platform[];
    }[];
    campaignStatusBreakdown: Record<string, number>;
}>();

function formatRupiah(value: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

const statusColors: Record<string, string> = {
    [CampaignStatus.Draft]: '#94a3b8',
    [CampaignStatus.Ongoing]: '#22c55e',
    [CampaignStatus.Completed]: '#3b82f6',
    [CampaignStatus.Cancelled]: '#ef4444',
};

const chartData = computed(() => {
    const labels = Object.keys(props.campaignStatusBreakdown).map((k) => k.charAt(0).toUpperCase() + k.slice(1));
    const data = Object.values(props.campaignStatusBreakdown);
    const colors = Object.keys(props.campaignStatusBreakdown).map((k) => statusColors[k] ?? '#cbd5e1');

    return {
        labels,
        datasets: [{ data, backgroundColor: colors, borderWidth: 2 }],
    };
});

const chartOptions = {
    responsive: true,
    plugins: {
        legend: { position: 'bottom' as const },
    },
};

const totalCampaignsForChart = computed(() => (Object.values(props.campaignStatusBreakdown) as number[]).reduce((a, b) => a + b, 0));

const platformLabel: Record<string, string> = {
    [Platform.TikTok]: 'TikTok',
    [Platform.Instagram]: 'Instagram',
    [Platform.Youtube]: 'YouTube',
    [Platform.Facebook]: 'Facebook',
    [Platform.Twitter]: 'Twitter',
    [Platform.LinkedIn]: 'LinkedIn',
    [Platform.Twitch]: 'Twitch',
    [Platform.Discord]: 'Discord',
    [Platform.Reddit]: 'Reddit',
    [Platform.Pinterest]: 'Pinterest',
    [Platform.Threads]: 'Threads',
    [Platform.Telegram]: 'Telegram',
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4">
            <!-- Stat cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Total Influencers</p>
                    <p class="mt-1 text-3xl font-bold">{{ totalInfluencers }}</p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Total Campaigns</p>
                    <p class="mt-1 text-3xl font-bold">{{ totalCampaigns }}</p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Active Campaigns</p>
                    <p class="mt-1 text-3xl font-bold text-green-500">{{ activeCampaigns }}</p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Total Invoiced</p>
                    <p class="mt-1 text-2xl font-bold">{{ formatRupiah(totalInvoiced) }}</p>
                </div>
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border">
                    <p class="text-sm text-muted-foreground">Total Paid</p>
                    <p class="mt-1 text-2xl font-bold text-green-500">{{ formatRupiah(totalPaid) }}</p>
                </div>
            </div>

            <!-- Bottom row: top influencers + chart -->
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Top influencers table -->
                <div class="col-span-2 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border">
                    <h2 class="mb-4 text-base font-semibold">Top Influencers by Engagement</h2>

                    <div v-if="topInfluencers.length === 0" class="py-8 text-center text-sm text-muted-foreground">No influencers yet.</div>

                    <table v-else class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-sidebar-border/70 text-left text-xs text-muted-foreground dark:border-sidebar-border">
                                <th class="pb-2 font-medium">#</th>
                                <th class="pb-2 font-medium">Influencer</th>
                                <th class="pb-2 font-medium">Platforms</th>
                                <th class="pb-2 font-medium">KOLs</th>
                                <th class="pb-2 text-right font-medium">Avg Engagement</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(inf, i) in topInfluencers"
                                :key="inf.id"
                                class="border-b border-sidebar-border/40 last:border-0 dark:border-sidebar-border/40"
                            >
                                <td class="py-3 text-muted-foreground">{{ i + 1 }}</td>
                                <td class="py-3">
                                    <Link :href="route('influencer.show', inf.id)" class="flex items-center gap-2 hover:underline">
                                        <img
                                            v-if="inf.picture_url"
                                            :src="inf.picture_url"
                                            :alt="inf.name"
                                            class="h-7 w-7 rounded-full object-cover"
                                        />
                                        <span v-else class="flex h-7 w-7 items-center justify-center rounded-full bg-muted text-xs font-medium">
                                            {{ inf.name.charAt(0).toUpperCase() }}
                                        </span>
                                        <span class="font-medium">{{ inf.name }}</span>
                                    </Link>
                                </td>
                                <td class="py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="p in inf.platforms" :key="p" class="rounded bg-muted px-1.5 py-0.5 text-xs">
                                            {{ platformLabel[p] ?? p }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 text-center">{{ inf.kol_count }}</td>
                                <td class="py-3 text-right font-medium">{{ Number(inf.avg_engagement).toFixed(2) }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Campaign status chart -->
                <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-sm dark:border-sidebar-border">
                    <h2 class="mb-4 text-base font-semibold">Campaign Status</h2>

                    <div v-if="totalCampaignsForChart === 0" class="flex h-48 items-center justify-center text-sm text-muted-foreground">
                        No data.
                    </div>

                    <Chart v-else type="doughnut" :data="chartData" :options="chartOptions" class="mx-auto max-w-xs" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
