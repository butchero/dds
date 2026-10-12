<script setup>
import { computed, ref, watch } from "vue";
import { Button } from "@/Components/ui/button";
import { Alert, AlertDescription, AlertTitle } from "@/Components/ui/alert";

const props = defineProps({
  action: { type: String, required: true },
  csrf: { type: String, required: true },
  addresses: { type: Array, required: true },
  equipment: { type: Array, required: true },
  individuals: { type: Array, required: true },
  companies: { type: Array, required: true },
  links: { type: Object, required: true },
  copy: { type: Object, required: true },
});

const selectClass = "border-input text-foreground h-10 w-full min-w-0 rounded-md border bg-white px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50";

const addressId = ref(props.addresses.length === 1 ? String(props.addresses[0].id) : "");
const billingType = ref(props.companies.length && !props.individuals.length ? "company" : "individual");
const profileId = ref("");
const equipmentId = ref("");
const requestedOn = ref("");
const note = ref("");

const profiles = computed(() => (billingType.value === "company" ? props.companies : props.individuals));

const equipmentHere = computed(() => props.equipment.filter((item) => String(item.address_id) === String(addressId.value)));

const notice = computed(() => {
  if (!props.addresses.length) {
    return {
      title: props.copy.noAddressTitle,
      text: props.copy.noAddressText,
      href: props.links.addresses,
      link: props.copy.noAddressLink,
    };
  }

  if (!props.equipment.length) {
    return {
      title: props.copy.noEquipmentTitle,
      text: props.copy.noEquipmentText,
      href: props.links.equipment,
      link: props.copy.noEquipmentLink,
    };
  }

  if (!props.individuals.length && !props.companies.length) {
    return {
      title: props.copy.noBillingTitle,
      text: props.copy.noBillingText,
      href: props.links.billing,
      link: props.copy.noBillingLink,
    };
  }

  if (!profiles.value.length) {
    return {
      title: props.copy.missingProfileTitle,
      text: props.copy.missingProfileText,
      href: props.links.billing,
      link: props.copy.noBillingLink,
    };
  }

  if (addressId.value && !equipmentHere.value.length) {
    return {
      title: props.copy.noEquipmentHereTitle,
      text: props.copy.noEquipmentHereText,
      href: props.links.equipment,
      link: props.copy.noEquipmentLink,
    };
  }

  return null;
});

const canSubmit = computed(() => !notice.value && equipmentId.value !== "");

watch(addressId, () => {
  const match = equipmentHere.value.find((item) => String(item.id) === equipmentId.value);

  if (!match) {
    equipmentId.value = equipmentHere.value.length === 1 ? String(equipmentHere.value[0].id) : "";
  }
}, { immediate: true });

watch(billingType, () => {
  profileId.value = profiles.value.length === 1 ? String(profiles.value[0].id) : "";
}, { immediate: true });
</script>

<template>
  <form class="max-w-sm space-y-3" method="post" :action="action">
    <input type="hidden" name="_token" :value="csrf">
    <Alert v-if="notice">
      <AlertTitle>{{ notice.title }}</AlertTitle>
      <AlertDescription>
        {{ notice.text }}
        <a :href="notice.href">{{ notice.link }}</a>
      </AlertDescription>
    </Alert>
    <label class="block text-sm">
      <span class="mb-1 block">{{ copy.address }}</span>
      <select v-model="addressId" name="address_id" required :class="selectClass" :disabled="!addresses.length">
        <option value="">{{ copy.chooseAddress }}</option>
        <option v-for="address in addresses" :key="address.id" :value="String(address.id)">
          {{ address.label }}
        </option>
      </select>
    </label>
    <label class="block text-sm">
      <span class="mb-1 block">{{ copy.billingType }}</span>
      <select v-model="billingType" name="billing_type" required :class="selectClass">
        <option value="individual">{{ copy.individual }}</option>
        <option value="company">{{ copy.company }}</option>
      </select>
    </label>
    <label v-if="profiles.length > 1" class="block text-sm">
      <span class="mb-1 block">{{ billingType === "company" ? copy.companyLabel : copy.personLabel }}</span>
      <select v-model="profileId" :name="billingType === 'company' ? 'company_profile_id' : 'individual_profile_id'" required :class="selectClass">
        <option value="">{{ copy.choose }}</option>
        <option v-for="profile in profiles" :key="profile.id" :value="String(profile.id)">
          {{ profile.label }}
        </option>
      </select>
    </label>
    <input v-else-if="profileId" type="hidden" :name="billingType === 'company' ? 'company_profile_id' : 'individual_profile_id'" :value="profileId">
    <label class="block text-sm">
      <span class="mb-1 block">{{ copy.equipment }}</span>
      <select v-model="equipmentId" name="equipment_id" required :class="selectClass" :disabled="!equipmentHere.length">
        <option value="">{{ copy.chooseEquipment }}</option>
        <option v-for="item in equipmentHere" :key="item.id" :value="String(item.id)">
          {{ item.label }}
        </option>
      </select>
    </label>
    <label class="block text-sm">
      <span class="mb-1 block">{{ copy.requestedOn }}</span>
      <input v-model="requestedOn" class="border-input text-foreground h-10 w-full rounded-md border bg-white px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50" type="date" name="requested_on">
    </label>
    <label class="block text-sm">
      <span class="mb-1 block">{{ copy.note }}</span>
      <textarea v-model="note" name="note" class="border-input text-foreground min-h-24 w-full rounded-md border bg-white px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50" />
    </label>
    <Button type="submit" :disabled="!canSubmit">
      {{ copy.submit }}
    </Button>
  </form>
</template>
