<script setup>
import Layout from '../Layout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

defineProps({
    equipment: Array,
    appointments: Array,
});

const page = usePage();
const logout = useForm({});
const form = useForm({
    equipment_id: '',
    requested_on: '',
    note: '',
});

function submit() {
    form.post('/cont/programare');
}

function exit() {
    logout.post('/cont/iesire');
}
</script>

<template>
    <Layout>
        <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">Contul meu</h1>
        <p v-if="page.props.flash.status" class="text-albastru">{{ page.props.flash.status }}</p>
        <h2 class="mt-3 font-arial text-[13px] text-portocaliu">Echipamente</h2>
        <ul>
            <li v-for="item in equipment" :key="item.id">
                {{ item.name }} — ultima revizie {{ item.last_revision_on || 'necunoscuta' }}, urmatoarea {{ item.next_revision_on || 'neprogramata' }}
            </li>
        </ul>
        <h2 class="mt-3 font-arial text-[13px] text-portocaliu">Programeaza o revizie</h2>
        <form class="max-w-sm space-y-2" @submit.prevent="submit">
            <select v-model="form.equipment_id" class="block w-full border border-[#a4a4a4] px-2 py-1">
                <option value="">Alege echipamentul</option>
                <option v-for="item in equipment" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
            <input v-model="form.requested_on" type="date" class="block w-full border border-[#a4a4a4] px-2 py-1">
            <textarea v-model="form.note" placeholder="Observatii" class="block w-full border border-[#a4a4a4] px-2 py-1"></textarea>
            <button class="bg-portocaliu px-3 py-1 text-white" type="submit">Trimite cererea</button>
        </form>
        <h2 class="mt-3 font-arial text-[13px] text-portocaliu">Cereri</h2>
        <ul>
            <li v-for="item in appointments" :key="item.id">{{ item.requested_on }} — {{ item.status }}</li>
        </ul>
        <button class="mt-4 underline" type="button" @click="exit">Iesire</button>
    </Layout>
</template>
