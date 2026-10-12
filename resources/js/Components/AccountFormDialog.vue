<script setup>
import { ref } from "vue";
import { Button } from "@/Components/ui/button";
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/Components/ui/dialog";

const props = defineProps({
  initiallyOpen: { type: Boolean, default: false },
  label: { type: String, required: true },
  title: { type: String, required: true },
  triggerClass: { type: String, default: "" },
  body: { type: String, required: true },
  cancelLabel: { type: String, required: true },
  closeLabel: { type: String, required: true },
});

const open = ref(props.initiallyOpen);
</script>

<template>
  <Dialog v-model:open="open">
    <DialogTrigger as-child>
      <Button
        v-if="triggerClass"
        type="button"
        variant="link"
        :class="triggerClass"
      >
        {{ label }}
      </Button>
      <Button v-else type="button">
        {{ label }}
      </Button>
    </DialogTrigger>
    <DialogContent class="max-h-[calc(100vh-2rem)] overflow-y-auto sm:max-w-md" :close-label="closeLabel">
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
      </DialogHeader>
      <div class="grid gap-3" v-html="body" />
      <DialogFooter class="sm:justify-between">
        <DialogClose as-child>
          <Button variant="outline" type="button">
            {{ cancelLabel }}
          </Button>
        </DialogClose>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
