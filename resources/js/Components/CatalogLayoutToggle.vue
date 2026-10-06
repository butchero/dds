<script setup>
import { LayoutGrid, List } from '@lucide/vue';

defineProps({
    gridUrl: { type: String, required: true },
    listUrl: { type: String, required: true },
    layout: { type: String, required: true },
    gridLabel: { type: String, required: true },
    listLabel: { type: String, required: true },
});
</script>

<template>
    <div class="flex gap-2">
        <a
            :href="gridUrl"
            :aria-label="gridLabel"
            :aria-current="layout !== 'list' ? 'true' : undefined"
            class="inline-flex size-8 items-center justify-center border no-underline"
            :class="layout !== 'list' ? 'border-brand bg-brand text-white' : 'border-[#e3e3e3] bg-white text-[#444444]'"
        >
            <LayoutGrid class="size-4" />
        </a>
        <a
            :href="listUrl"
            :aria-label="listLabel"
            :aria-current="layout === 'list' ? 'true' : undefined"
            class="inline-flex size-8 items-center justify-center border no-underline"
            :class="layout === 'list' ? 'border-brand bg-brand text-white' : 'border-[#e3e3e3] bg-white text-[#444444]'"
        >
            <List class="size-4" />
        </a>
    </div>
</template>
