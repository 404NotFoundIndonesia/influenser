<script setup lang="ts">
import { Paginate } from '@/types/model';
import Paginator, { PageState } from 'primevue/paginator';
import { router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface Props {
    paginator: Paginate<any>
}

const props = defineProps<Props>();
const first = ref(0);

const pageChange = (event: PageState) => {
    const url = new URL(window.location.href);
    url.searchParams.set("page", (event.page + 1).toString());
    url.searchParams.set("size", event.rows.toString());
    router.get(url.toString(), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

onMounted(() => {
    first.value = (props.paginator.current_page - 1) * props.paginator.per_page
});
</script>

<template>
    <Paginator
        @page="pageChange"
        :first
        :rowsPerPageOptions="[10, 20, 30, 40, 50]"
        :totalRecords="paginator.total"
        :rows="paginator.per_page" >
        <template #start>
            Showing {{ paginator.from }} to {{ paginator.to }} of {{ paginator.total }}
        </template>
    </Paginator>
</template>
