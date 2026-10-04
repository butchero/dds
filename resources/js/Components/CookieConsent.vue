<script setup>
import { onMounted, ref } from 'vue';

const STORAGE_KEY = 'dds-cookie-consent';
const visible = ref(false);
const details = ref(false);

function read() {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
    } catch {
        return null;
    }
}

function save(choice) {
    const value = {
        necessary: true,
        statistics: choice === 'all',
        decidedAt: new Date().toISOString(),
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
    document.cookie = `${STORAGE_KEY}=${encodeURIComponent(JSON.stringify(value))}; path=/; max-age=${60 * 60 * 24 * 180}; SameSite=Lax`;
    visible.value = false;
    details.value = false;
}

function open() {
    visible.value = true;
    details.value = true;
}

onMounted(() => {
    if (!read()) {
        visible.value = true;
    }
    window.addEventListener('dds-cookies-open', open);
});
</script>

<template>
    <div v-if="visible" class="fixed inset-x-0 bottom-0 z-50 px-3 pb-3">
        <div class="mx-auto max-w-7xl border border-[#e2e2e2] bg-white p-4 shadow-lg">
            <p class="m-0 font-arial text-[13px] text-portocaliu">Cookie-uri</p>
            <p class="mt-2 mb-0 leading-[16px] text-[#444444]">
                Folosim doar cookie-uri necesare pentru sesiune și securitatea formularelor. Nu încărcăm cookie-uri de statistică sau marketing înainte de acordul tău.
                Detalii în <a href="/politica-de-cookies" class="text-albastru">Politica de cookie-uri</a>
                și <a href="/politica-de-confidentialitate" class="text-albastru">Politica de confidențialitate</a>.
            </p>
            <div v-if="details" class="mt-3 border border-marginelight p-3 leading-[16px]">
                <p class="m-0"><strong>Necesare</strong> — mereu active. Țin sesiunea de autentificare, protecția formularelor și alegerea făcută aici. Nu pot fi oprite.</p>
                <p class="mt-2 mb-0"><strong>Statistici</strong> — momentan nu folosim astfel de cookie-uri. Dacă le vom adăuga, vor porni doar după „Acceptă toate”.</p>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" class="bg-portocaliu px-3 py-1 text-white" @click="save('all')">Acceptă toate</button>
                <button type="button" class="border border-[#a4a4a4] bg-white px-3 py-1" @click="save('necessary')">Doar necesare</button>
                <button type="button" class="px-3 py-1 text-albastru underline" @click="details = !details">{{ details ? 'Ascunde detaliile' : 'Setări' }}</button>
            </div>
        </div>
    </div>
</template>
