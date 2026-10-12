<script setup>
import { cn } from "@/lib/utils";

const props = defineProps({
  class: { type: null, required: false },
});
</script>

<template>
  <div data-slot="alert-description" :class="cn('text-muted-foreground text-sm [&_a]:underline [&_a]:underline-offset-3', props.class)">
    <slot />
  </div>
</template>
