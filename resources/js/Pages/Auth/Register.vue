<script setup>
import Layout from '../Layout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/cont/inregistrare');
}
</script>

<template>
    <Layout>
        <h1 class="mb-[5px] font-arial text-[13px] text-portocaliu">Cont nou</h1>
        <form class="max-w-sm space-y-2" @submit.prevent="submit">
            <input v-model="form.name" required placeholder="Nume" class="block w-full border border-[#a4a4a4] px-2 py-1">
            <input v-model="form.email" type="email" required placeholder="Email" class="block w-full border border-[#a4a4a4] px-2 py-1">
            <input v-model="form.phone" placeholder="Telefon" class="block w-full border border-[#a4a4a4] px-2 py-1">
            <input v-model="form.password" type="password" required placeholder="Parola" class="block w-full border border-[#a4a4a4] px-2 py-1">
            <input v-model="form.password_confirmation" type="password" required placeholder="Confirma parola" class="block w-full border border-[#a4a4a4] px-2 py-1">
            <p v-if="form.errors.email" class="text-portocaliu">{{ form.errors.email }}</p>
            <button class="bg-portocaliu px-3 py-1 text-white" type="submit">Creeaza cont</button>
        </form>
    </Layout>
</template>
