# DDS

Site de prezentare și cont de client pentru DDS Services Group: centrale termice, aer condiționat și panouri solare. Nu există comandă online.

Site-ul public este Inertia și Vue. Administrarea este panoul Filament de la `/admin`, deschis doar utilizatorilor cu rolul `admin`. Clienții își văd echipamentele și pot cere o programare. Data următoarei revizii se calculează din ultima revizie și intervalul în luni. Comanda `revisions:remind` trimite email și SMS înainte de termen.

## Pornire locală

Aplicația rulează în Laravel Sail:

```bash
./vendor/bin/sail up -d
```

Site-ul este la [http://localhost:8088](http://localhost:8088), administrarea la [http://localhost:8088/admin](http://localhost:8088/admin). Portul vine din `APP_PORT` în `.env`. Baza de date este MySQL din container, nu SQLite-ul din `.env.example`.

Pe acest WSL nu există `php` pe host. Artisan, Composer și PHP se rulează în containerul `laravel.test`.

Conturile locale de demo sunt create de `database/seeders/DatabaseSeeder.php`.
