<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/Components/ui/dialog';

const props = defineProps({
    name: { type: String, required: true },
    images: { type: Array, default: () => [] },
    description: { type: String, default: '' },
    specifications: { type: Array, default: () => [] },
    labels: { type: Object, required: true },
    price: { type: String, default: '' },
});

const current = ref(0);
const lightboxOpen = ref(false);
const tab = ref(props.description ? 'description' : 'specifications');

const priceHtml = computed(() => props.price.trim());
const currentImage = computed(() => props.images[current.value] ?? '');
const hasTabs = computed(() => props.description !== '' || props.specifications.length > 0);

function show(index) {
    const count = props.images.length;

    if (count === 0) {
        return;
    }

    current.value = (index + count) % count;
}

function onKeydown(event) {
    if (! lightboxOpen.value || props.images.length < 2) {
        return;
    }

    if (event.key === 'ArrowLeft') {
        show(current.value - 1);
    }

    if (event.key === 'ArrowRight') {
        show(current.value + 1);
    }
}

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
    <div>
        <div
            v-if="images.length || priceHtml"
            class="flex flex-col gap-4 sm:flex-row sm:items-start"
        >
            <div v-if="images.length" class="min-w-0 flex-1">
                <button
                    type="button"
                    class="block w-full cursor-zoom-in border border-[#e3e3e3] bg-white p-[10px]"
                    :aria-label="labels.enlarge"
                    @click="lightboxOpen = true"
                >
                    <img
                        :src="currentImage"
                        :alt="name"
                        class="mx-auto max-h-[28rem] w-full object-contain"
                    >
                </button>
                <div v-if="images.length > 1" class="mt-2 flex flex-wrap gap-2">
                    <button
                        v-for="(image, index) in images"
                        :key="index"
                        type="button"
                        class="border bg-white p-1"
                        :class="index === current ? 'border-brand' : 'border-[#e3e3e3]'"
                        :aria-label="name"
                        :aria-current="index === current ? 'true' : undefined"
                        @click="current = index"
                    >
                        <img :src="image" alt="" class="h-14 w-14 object-contain">
                    </button>
                </div>
            </div>
            <div v-if="priceHtml" class="shrink-0" v-html="priceHtml" />
        </div>

        <div v-if="hasTabs" class="mt-4">
            <div class="flex flex-wrap gap-2" role="tablist">
                <button
                    v-if="description"
                    id="product-tab-description"
                    type="button"
                    role="tab"
                    class="inline-flex h-8 items-center border px-3 text-sm font-medium"
                    :class="tab === 'description' ? 'border-brand bg-brand text-white' : 'border-[#e3e3e3] bg-white text-[#444444]'"
                    :aria-selected="tab === 'description'"
                    aria-controls="product-panel-description"
                    @click="tab = 'description'"
                >
                    {{ labels.description }}
                </button>
                <button
                    v-if="specifications.length"
                    id="product-tab-specifications"
                    type="button"
                    role="tab"
                    class="inline-flex h-8 items-center border px-3 text-sm font-medium"
                    :class="tab === 'specifications' ? 'border-brand bg-brand text-white' : 'border-[#e3e3e3] bg-white text-[#444444]'"
                    :aria-selected="tab === 'specifications'"
                    aria-controls="product-panel-specifications"
                    @click="tab = 'specifications'"
                >
                    {{ labels.specifications }}
                </button>
            </div>
            <div
                v-if="description"
                v-show="tab === 'description'"
                id="product-panel-description"
                role="tabpanel"
                class="mt-4 whitespace-pre-line"
                aria-labelledby="product-tab-description"
            >
                {{ description }}
            </div>
            <div
                v-if="specifications.length"
                v-show="tab === 'specifications'"
                id="product-panel-specifications"
                role="tabpanel"
                class="mt-4"
                aria-labelledby="product-tab-specifications"
            >
                <table class="w-full border-collapse text-sm">
                    <tbody>
                        <tr
                            v-for="row in specifications"
                            :key="row.name"
                            class="border-b border-[#e3e3e3]"
                        >
                            <th class="w-1/3 py-1 pr-3 text-left font-semibold text-[#444444]">{{ row.name }}</th>
                            <td class="py-1">{{ row.value }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-model:open="lightboxOpen">
            <DialogContent class="max-h-[calc(100vh-2rem)] bg-white sm:max-w-5xl">
                <DialogTitle class="sr-only">{{ name }}</DialogTitle>
                <DialogDescription class="sr-only">{{ labels.enlarge }}</DialogDescription>
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-2">
                        <Button
                            v-if="images.length > 1"
                            type="button"
                            variant="outline"
                            size="icon"
                            :aria-label="labels.previous"
                            @click="show(current - 1)"
                        >
                            <ChevronLeft />
                        </Button>
                        <img
                            :src="currentImage"
                            :alt="name"
                            class="mx-auto min-w-0 flex-1 object-contain"
                            :class="images.length > 1 ? 'max-h-[calc(85vh-6rem)]' : 'max-h-[85vh]'"
                        >
                        <Button
                            v-if="images.length > 1"
                            type="button"
                            variant="outline"
                            size="icon"
                            :aria-label="labels.next"
                            @click="show(current + 1)"
                        >
                            <ChevronRight />
                        </Button>
                    </div>
                    <div v-if="images.length > 1" class="flex flex-wrap justify-center gap-2">
                        <button
                            v-for="(image, index) in images"
                            :key="index"
                            type="button"
                            class="border bg-white p-1"
                            :class="index === current ? 'border-brand' : 'border-[#e3e3e3]'"
                            :aria-label="name"
                            :aria-current="index === current ? 'true' : undefined"
                            @click="current = index"
                        >
                            <img :src="image" alt="" class="h-14 w-14 object-contain">
                        </button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
