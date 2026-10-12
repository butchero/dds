<script setup>
import { reactiveOmit } from "@vueuse/core";
import { DialogTitle, useForwardProps } from "reka-ui";
import { cn } from "@/lib/utils";

const props = defineProps({
  class: { type: null, required: false },
});

const forwardedProps = useForwardProps(reactiveOmit(props, "class"));
</script>

<template>
  <DialogTitle
    data-slot="dialog-title"
    v-bind="forwardedProps"
    :class="cn('font-heading text-base leading-none font-medium', props.class)"
  >
    <slot />
  </DialogTitle>
</template>
