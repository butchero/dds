<script setup>
import Layout from '../Layout.vue';
</script>

<template>
    <Layout>
        <h1 class="mb-[10px] font-arial text-[13px] text-portocaliu">Politica de cookie-uri</h1>
        <div class="space-y-3 leading-[16px]">
            <p>Cookie-urile sunt fișiere mici păstrate în browser. Le folosim ca să funcționeze autentificarea și formularele, și ca să reținem alegerea ta privind cookie-urile.</p>
            <p><strong>Necesare</strong></p>
            <ul class="list-disc pl-[40px]">
                <li>laravel_session — sesiunea de autentificare, pe durata vizitei</li>
                <li>XSRF-TOKEN — protejează formularele împotriva cererilor false, pe durata vizitei</li>
                <li>dds_cookie_consent — reține dacă ai acceptat toate cookie-urile sau doar pe cele necesare, 6 luni</li>
            </ul>
            <p>Acestea nu cer acordul tău, pentru că site-ul nu poate funcționa fără ele.</p>
            <p><strong>Statistici și marketing</strong></p>
            <p>Nu folosim acum cookie-uri de statistică sau de publicitate. Dacă le vom introduce, vor porni doar după ce apeși „Acceptă toate” și politica aceasta va fi actualizată.</p>
            <p>Poți schimba alegerea oricând din linkul „Setări cookie-uri” din subsol. Poți șterge cookie-urile și din setările browserului.</p>
        </div>
    </Layout>
</template>
