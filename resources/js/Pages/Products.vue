<script setup>
import Layout from './Layout.vue';
import ProductCard from '../Components/ProductCard.vue';

defineProps({
    title: String,
    products: Array,
});
</script>

<template>
    <Layout>
        <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">{{ title }}</h1>
        <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-3 xl:grid-cols-3">
            <ProductCard v-for="product in products" :key="product.slug" :product="product" />
        </div>
        <p v-if="!products.length">Nu am gasit produse.</p>
    </Layout>
</template>
