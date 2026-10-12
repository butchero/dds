@php
    $office = \App\Support\ContactOffice::present();
    $operator = trim(implode(', ', array_filter([
        $office['name'] ?: null,
        filled($office['city']) || filled($office['street'])
            ? __('cu sediul în :place', [
                'place' => trim(implode(', ', array_filter([$office['city'], $office['street']]))),
            ])
            : null,
    ]));
@endphp
<x-site-layout :title="__('Politica de confidențialitate')" :breadcrumbs="\App\Support\Breadcrumbs::forPage(__('Politica de confidențialitate'))">
    <h1 class="mb-3 text-lg font-medium tracking-tight text-brand">{{ __('Politica de confidențialitate') }}</h1>
    <div class="space-y-3">
        <p>{{ __(':operator este operatorul datelor cu caracter personal prelucrate prin acest site.', ['operator' => $operator !== '' ? $operator : (string) \App\Models\SiteSetting::get('title_suffix')]) }}</p>
        <p>{{ __('Prelucrăm datele pe care ni le trimiți: nume, e-mail, telefon, mesajele din formularul de contact, adrese, date de facturare pentru persoană fizică sau firmă, echipamentele înregistrate și cererile de programare. Dacă alegi notificarea prin SMS, trimitem mesajul la telefonul din cont. Te poți autentifica și cu Google sau Facebook; de la aceștia primim numele și e-mailul. Dacă navighezi fără cont, păstrăm doar datele tehnice din cookie-urile necesare.') }}</p>
        <p>{{ __('Temeiurile sunt: executarea serviciului cerut, obligațiile legale privind verificările tehnice, interesul legitim de a răspunde la mesaje și consimțământul pentru cookie-urile care nu sunt necesare.') }}</p>
        <p>{{ __('Păstrăm datele din cont atât timp cât contul este activ, iar documentele legate de revizii pe durata cerută de lege. Apoi le ștergem sau le anonimizăm.') }}</p>
        <p>{{ __('Nu vindem datele. Le pot vedea doar persoanele din firmă care lucrează la programări și, dacă legea o cere, autoritățile.') }}</p>
        <p>{{ __('Ai dreptul de acces, rectificare, ștergere, restricționare, portabilitate și opoziție, precum și dreptul de a-ți retrage consimțământul. Ne poți scrie din pagina de Contact. Poți depune o plângere la ANSPDCP,') }} <a href="https://www.dataprotection.ro" class="text-link">dataprotection.ro</a>.</p>
    </div>
</x-site-layout>
