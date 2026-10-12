<laravel-boost-guidelines>
=== .ai/dds rules ===

# DDS

Presentation site with a product catalog and a client account for booking service. The catalog can hold any product lines; there is no online checkout. The codebase is business-agnostic: client-specific names, addresses, phones, emails, brand color, currency suffix, SMS prefix, founding year, and similar values live in `site_settings` (edited in Filament), not in templates or PHP string literals. Cookie and storage keys use generic names (`catalog_layout`, `cookie_consent`), not the client brand. Defaults in `SiteSetting::definitions()` seed the current deploy; a new client changes those rows (or defaults before first migrate). Interface copy goes through `__()` with the Romanian sentence as the key. Text stored in the database stays as stored.

- The public site and the client account are Blade views rendered by Laravel (`resources/views`), with routes in `routes/web.php`. Vue in `resources/js` only enhances the mobile menu, the banner rotator, the cookie banner, the catalog layout icons, and the product gallery with its lightbox and tabs. The admin is the Filament panel at `/dds-panel`. Only users with `role = admin` can open it (`User::canAccessPanel`). Clients use `role = client`. Public pages other than the home page show a breadcrumb trail with BreadcrumbList JSON-LD, on a full-width row under the search bar. The home page heading is the `home_heading` row in `site_settings`, shown only as that page's h1, and edited on the Filament site settings page. Public pages publish a document title, meta description, canonical URL, and Open Graph and Twitter tags from the page content, falling back to the `meta_description`, `share_image`, and `title_suffix` site settings. The footer copyright starts at the `copyright_start_year` row in `site_settings`, edited on the Filament site settings page, and ends with the current year. Sign-in and account pages are `noindex`.
- The catalog is categories and products. Categories can have a parent and a description. A product has a cover image and a gallery; the cover is the first gallery image. The product page shows that gallery large, and a click opens a lightbox. The description and the characteristics sit in separate tabs. A category page lists that category and its child categories. The categories box is titled Categorii and lists every category with its subcategories. On a parent category page that full list stays, with only the open category in bold, together with the products and without filters. On a subcategory page the box is titled Subcategorii and lists the parent and that parent's subcategories, with the open subcategory in bold, and any filters for that subcategory sit under the list. Filters appear on a category that has no children: manufacturer, price and stock when its products have them, and only the filterable characteristics assigned to that category. The page heading is the category name, in the same color as the selected subcategory in the left menu, without a product count. The catalog layout switch is labeled Afișează. Each characteristic is an open group of checkboxes, one per value present in the category, with a product count beside the value. A value that matches no product after the other active filters is omitted. Checking a box applies the filter. Narrow screens show the same panel above the products. The catalog listing is a grid or one product per row; that choice is stored in the `catalog_layout` cookie. A product card is a bordered box with a large photo, the name, up to three filterable characteristics, and the price. Search by name is separate from category filters. A manufacturer page lists that manufacturer's published products at `/producator/{slug}`; the home-page logos and the producer list link there, and the old search address redirects to it. Manufacturers are a catalog in the admin. A product belongs to one manufacturer. A manufacturer can have a logo, and the home page shows the manufacturers that have one.
- Product characteristics are defined once, then assigned to categories. A product uses the characteristics of its own category and of its ancestor categories. On a category page, filters come only from characteristics assigned to the category being viewed, and only when that category has no children. Several values of one characteristic match with OR. Different characteristics match with AND. Types are text, number, yes/no, a single-choice list, and a multi-choice list. A characteristic is a filter only when an admin marks it as one. The importer leaves that mark off.
- Price and quantity are optional. An empty product VAT rate uses the global rate from the `vat_rate` row in `site_settings` (`SiteSetting::get()`). An explicit `0` stays `0`. The `prices_include_vat` row records whether the entered price already includes VAT. The storefront shows the price with VAT as the main amount and the price without VAT underneath. A quantity of `0` means out of stock. A null quantity is not shown.
- Catalog settings in the admin (global VAT rate and whether entered prices include VAT) are separate from the main-menu position, which stays on the existing site-settings screen.
- A client owns addresses, equipment, billing profiles and appointments. The account is the public Blade site: the header links to login or registration, and after sign-in to the account. The account is split into separate pages for profile, addresses, equipment, billing and appointments. On those pages the left column is only the account menu, in the same box as the catalog categories, and the category, service and manufacturer lists are hidden. Filament at `/dds-panel` is only for the office, which can edit the same client data. An equipment stays at one address. A client can keep several addresses and several billing profiles, each either a person or a company. An appointment records the address and the billing profile; its status is requested, confirmed, done or cancelled, and the client sees the history. Marking it done sets that equipment's last revision. CSV import writes name and phone into `users`, without email or password. Registering with that phone, after the SMS code, completes the imported row, and the client then confirms the email. Google and Facebook sign-in is available; a later confirmed phone links the account to an unclaimed imported row. `Equipment` sets `next_revision_on` from `last_revision_on` plus `interval_months`. `php artisan revisions:remind` emails and texts only when the client opted in, and the email only if the address is verified. New appointments are mailed to the `company_email` site setting.
- The contact page is `/contact`, without the catalog sidebars. A Google map of the office fills the left half, with the address and phones on the map, and the form sits beside it. The office name, address, phones, public email and map coordinates are rows in `site_settings`, edited on the Filament site settings page, together with `title_suffix`, `founded_year`, `brand_primary`, `currency_suffix`, and `sms_sender_prefix`. The form emails the `company_email` site setting. The Contact menu item points there, and `/servicii/contact` redirects to it.
- Services and news are JSON content blocks (`App\Filament\Support\ContentBlocks`, rendered by `resources/views/components/blocks.blade.php`). Slugs come from `HasSlug`.
- Vue interface components come from shadcn-vue in `resources/js/Components/ui` (Reka Nova). The admin primary color comes from the `brand_primary` site setting (default `#fe5013`) with white text. Storefront Tailwind still uses the `brand` / `text-link` tokens (`#fe5013`, `#0bb2f7`). Vue copy is passed from Blade/PHP via props or JSON payloads that already went through `__()`.
- Interface copy is Romanian and must go through `__()` (Filament labels, flashes, mail/SMS, Blade, and strings handed to Vue). Visible text uses a hyphen (-), never an em dash or en dash. PHP names, CSS class names, theme token names, and comments stay English. Filament `brandName` uses `title_suffix`; SMS bodies use `sms_sender_prefix`; prices use `currency_suffix`.
- Locally the site runs in Laravel Sail (`compose.yaml`) on `APP_PORT` (8088), with MySQL from `.env`, not the SQLite defaults in `.env.example`. Inside Sail the database host is `mysql`. From the Ubuntu host it is `127.0.0.1` on `FORWARD_DB_PORT`. The host PHP is `/usr/bin/php`. In Cursor's terminal, `php` may still be the Windows binary; use `/usr/bin/php` or `vendor/bin/sail` instead.
- Do not add dependencies or new top-level directories without asking.
- When a change adds a feature or changes behavior described here, update this file in the same change. Keep it a short overview. Then refresh `AGENTS.md` with `/usr/bin/php artisan boost:install --guidelines --no-interaction`.

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
