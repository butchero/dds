<script setup>
import Layout from './Layout.vue';
import Blocks from '../Components/Blocks.vue';

defineProps({
    title: String,
    blocks: Array,
    excerpt: String,
});
</script>

<template>
    <Layout>
        <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">{{ title }}</h1>
        <p v-if="excerpt" class="leading-[15px]">{{ excerpt }}</p>
        <Blocks :blocks="blocks" />
    </Layout>
</template>
