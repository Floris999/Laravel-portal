# Project Guidelines

## Architecture

- Laravel Livewire starter kit (Flux UI, Livewire v4).
- One route = one controller = one view (standard Laravel MVC), not a full-page Livewire component, unless explicitly asked for interactivity.
- Controllers live in `app/Http/Controllers`, use a single `index()` method per page unless more actions are clearly needed.
- Views live in `resources/views` (one blade file per page, named after the route/page).
- Every page view must be wrapped in `<x-layouts::app :title="__('...')">...</x-layouts::app>` so it automatically gets the sidebar (see [resources/views/home.blade.php](../resources/views/home.blade.php) and [resources/views/dashboard.blade.php](../resources/views/dashboard.blade.php) as examples).
- Routes are defined in `routes/web.php` (or split into dedicated route files like `routes/settings.php`), pointing to `[Controller::class, 'index']`.
- Settings pages are the exception: they use Livewire full-page components via `Route::livewire(...)` and the `pages::` view namespace (`resources/views/pages`) — keep that pattern only for settings.

## Conventions

- Single-action pages (no create/edit form) follow this flat pattern:
    1. Add route in `routes/web.php` → `Route::get('/path', [XController::class, 'index'])->name('name');`
    2. Create `XController` with `index(): View` returning `view('x')`.
    3. Create `resources/views/x.blade.php` using the `<x-layouts::app>` layout.
- Resources needing CRUD (index/create/store/edit/...) use Laravel's resource convention instead:
    1. Route via `Route::resource('things', ThingController::class)->only([...])` (generates `things.index`, `things.create`, `things.store`, etc.).
    2. Controller methods named after the resource actions (`index`, `create`, `store`, `edit`, `update`, `destroy`), each returning its own view.
    3. Views live in a plural subfolder: `resources/views/things/index.blade.php`, `resources/views/things/create.blade.php`, etc. — each still wrapped in `<x-layouts::app>`.
    4. Example: [CustomerController.php](../app/Http/Controllers/CustomerController.php), [resources/views/customers/index.blade.php](../resources/views/customers/index.blade.php), [resources/views/customers/create.blade.php](../resources/views/customers/create.blade.php).
- Routes requiring login should use `->middleware(['auth', 'verified'])` (see `dashboard` route).
- Forms use Flux form components (`flux:input`, `flux:select`, `flux:button`) rather than plain HTML inputs, for consistent styling/validation-error display.
- Dummy/demo data belongs in a model factory (`database/factories`) seeded via `DatabaseSeeder`, not hardcoded in controllers — keeps `fake()`/Faker usage confined to dev-only tooling.

## Copilot behavior for this repo

- After implementing a fix, do not run extensive manual verification (e.g. ad-hoc SQL queries, tinker sessions, extra exploratory prompts/scripts) by default.
- Instead, just check the changed code for syntax/lint errors (e.g. via `get_errors`) and briefly explain the fix.
- Only run deeper tests (artisan commands, database queries, test suite) if the user explicitly asks for it.
- Keep token usage low: prefer concise checks over broad verification.

## Code Style - Comments

- Use **docblocks only** for classes, methods, and properties (PHPDoc standard).
- Avoid inline comments in general; only add comments where code logic is not self-explanatory.
- Prefer descriptive variable and method names over comments explaining what code does.
- Example: use `$isValidCustomer` instead of `// check if customer is valid`.
