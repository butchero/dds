<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    product: { type: Object, required: true },
});

const logos = {
    ARISTON: '/images/producatori/ariston.jpg',
    BUDERUS: '/images/producatori/buderus.jpg',
};
</script>

<template>
    <div class="min-w-0 p-[2px] text-center">
        <div class="inline-flex items-start gap-[4px] border border-[#e3e3e3] bg-white p-[4px]">
            <Link :href="`/produs/${product.slug}`" :title="product.name" class="flex h-[80px] w-[95px] items-center justify-center">
                <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="max-h-[80px] max-w-[95px]">
            </Link>
            <span v-if="logos[product.manufacturer]"><img :src="logos[product.manufacturer]" :alt="product.manufacturer"></span>
        </div>
        <div class="mx-auto mt-[5px] flex h-[30px] items-start justify-center">
            <Link :href="`/produs/${product.slug}`" :title="product.name" class="leading-[13px] text-black no-underline hover:text-submeniu hover:underline">{{ product.name }}</Link>
        </div>
        <Link v-if="product.category_slug" :href="`/categorie/${product.category_slug}`" class="text-estompat underline">Mai multe produse din <br> aceasta categorie</Link>
    </div>
</template>
