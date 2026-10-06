<x-site-layout :title="__('Politica de cookie-uri')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Politica de cookie-uri'))">
    <h1 class="mb-3 text-lg font-medium tracking-tight text-brand">{{ __('Politica de cookie-uri') }}</h1>
    <div class="space-y-3">
        <p>{{ __('Cookie-urile sunt fișiere mici păstrate în browser. Le folosim ca să funcționeze autentificarea și formularele, ca să reținem alegerea ta privind cookie-urile și ca să păstrăm modul de afișare din catalog.') }}</p>
        <p><strong>{{ __('Necesare') }}</strong></p>
        <ul class="list-disc pl-[40px]">
            <li>{{ __('laravel_session — sesiunea de autentificare, pe durata vizitei') }}</li>
            <li>{{ __('XSRF-TOKEN — protejează formularele împotriva cererilor false, pe durata vizitei') }}</li>
            <li>{{ __('dds_cookie_consent — reține dacă ai acceptat toate cookie-urile sau doar pe cele necesare, 6 luni') }}</li>
            <li>{{ __('dds_catalog_layout — reține dacă produsele din catalog sunt afișate în grilă sau unul sub altul, 6 luni') }}</li>
        </ul>
        <p>{{ __('Acestea nu cer acordul tău, pentru că site-ul nu poate funcționa fără ele.') }}</p>
        <p><strong>{{ __('Statistici și marketing') }}</strong></p>
        <p>{{ __('Nu folosim acum cookie-uri de statistică sau de publicitate. Dacă le vom introduce, vor porni doar după ce apeși „Acceptă toate” și politica aceasta va fi actualizată.') }}</p>
        <p>{{ __('Poți schimba alegerea oricând din linkul „Setări cookie-uri” din subsol. Poți șterge cookie-urile și din setările browserului.') }}</p>
    </div>
</x-site-layout>
