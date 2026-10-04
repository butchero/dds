# DDS

DDS Services Group is a presentation site and client account for heating boilers, air conditioning, and solar panels. There is no online checkout.

- The public site and the client account are Blade views rendered by Laravel (`resources/views`), with routes in `routes/web.php`. Vue in `resources/js` only enhances the mobile menu, the banner rotator, and the cookie banner. The admin is the Filament panel at `/admin`. Only users with `role = admin` can open it (`User::canAccessPanel`). Clients use `role = client`.
- A client owns equipment and appointments. `Equipment` sets `next_revision_on` from `last_revision_on` plus `interval_months`. `php artisan revisions:remind` emails and texts the client when that date falls inside `config('dds.remind_days')`.
- Services and news are JSON content blocks (`App\Filament\Support\ContentBlocks`, rendered by `resources/views/components/blocks.blade.php`). Slugs come from `HasSlug`.
- Interface copy is Romanian. PHP names, methods, and comments stay English.
- Locally the app runs in Laravel Sail (`compose.yaml`). The host has no `php` binary, so Artisan, Composer, and PHP run inside the `laravel.test` container. The site uses MySQL from `.env`, not the SQLite defaults in `.env.example`, and is served on `APP_PORT` (8088).
- Do not add dependencies or new top-level directories without asking.
