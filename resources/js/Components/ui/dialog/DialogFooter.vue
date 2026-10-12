<script setup>
import { DialogClose } from "reka-ui";
import { cn } from "@/lib/utils";
import { Button } from "@/Components/ui/button";

const props = defineProps({
  class: { type: null, required: false },
  showCloseButton: { type: Boolean, default: false },
  closeLabel: { type: String, default: "Close" },
});
</script>

<template>
  <div
    data-slot="dialog-footer"
    :class="cn('bg-muted/50 -mx-4 -mb-4 flex flex-col-reverse gap-2 rounded-b-xl border-t p-4 sm:flex-row sm:justify-end', props.class)"
  >
    <slot />
    <DialogClose v-if="showCloseButton" as-child>
      <Button variant="outline">
        {{ closeLabel }}
      </Button>
    </DialogClose>
  </div>
</template>
