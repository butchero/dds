<script setup>
import { ref } from 'vue';

const props = defineProps({
    banners: { type: Array, default: () => [] },
});

const current = ref(0);
let timer = null;

function show(index) {
    current.value = index;
}

function start() {
    stop();
    if (props.banners.length < 2) {
        return;
    }
    timer = setInterval(() => {
        current.value = (current.value + 1) % props.banners.length;
    }, 3000);
}

function stop() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}

function choose(index) {
    stop();
    show(index);
}

start();
</script>

<template>
    <div v-if="banners.length" class="flex items-stretch">
        <div id="rotator" class="min-w-0 flex-1">
            <a v-for="(banner, index) in banners" :key="banner.url" :href="banner.link || '#'">
                <img :src="banner.url" :alt="banner.title" :class="{ activ: index === current }">
            </a>
        </div>
        <div class="flex w-[28px] shrink-0 items-center justify-center bg-[url('/images/promo-margine.svg')] bg-[length:100%_100%] bg-no-repeat">
            <div class="w-[16px]">
                <button v-for="(banner, index) in banners" :key="index" type="button" class="mb-[1px] block w-[14px] border text-center text-[12px] font-bold leading-[14px] text-white" :class="index === current ? 'border-white bg-[#f49591]' : 'border-transparent'" @click="choose(index)">{{ index + 1 }}</button>
            </div>
        </div>
    </div>
</template>
