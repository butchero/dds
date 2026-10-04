<template>
    <div class="space-y-4">
        <section v-for="(block, index) in blocks" :key="index">
            <h2 v-if="block.type === 'heading'" class="m-0 font-arial text-[13px] text-portocaliu">{{ block.data.text }}</h2>
            <div v-else-if="block.type === 'text'" class="leading-[15px] whitespace-pre-line">{{ block.data.body }}</div>
            <img v-else-if="block.type === 'image'" :src="block.data.url" :alt="block.data.alt || ''" class="max-w-full">
            <div v-else-if="block.type === 'text_image'" class="flex flex-col gap-3 sm:flex-row">
                <img :src="block.data.url" :alt="block.data.alt || ''" class="max-w-full sm:w-1/2">
                <div class="whitespace-pre-line leading-[15px]">{{ block.data.body }}</div>
            </div>
            <a v-else-if="block.type === 'button'" :href="block.data.url" class="inline-flex bg-portocaliu px-4 py-2 text-white no-underline">{{ block.data.label }}</a>
        </section>
    </div>
</template>

<script setup>
defineProps({
    blocks: { type: Array, default: () => [] },
});
</script>
