---
name: laravel-modules
description: "Creates and modifies code inside the Modules/ directory structure. Activates when adding a new module, adding a model/factory/migration/resource/service to an existing module, registering a module with Filament, or when the user mentions modules, modular, or a specific module name (clients, invoices, payments, etc.)."
license: MIT
metadata:
  author: project
---

# Laravel Modules

This project uses `nwidart/laravel-modules` (≥ v12). Modules live under `Modules/`.

## Directory Layout

Every module follows this exact layout (note uppercase `Database/` and `Tests/`):

```
Modules/{Name}/
  Database/
    Factories/        ← Eloquent factories
    Migrations/       ← Module-specific migrations
    Seeders/          ← Module seeders (extend AbstractSeeder)
  Filament/
    Admin/Resources/  ← Admin panel resources (if any)
    Company/
      Resources/
        {Model}/
          {Model}Resource.php     ← extends BaseResource
          Pages/
            List{Model}.php
            Create{Model}.php
            Edit{Model}.php
          Schemas/
            {Model}Form.php
          Tables/
            {Model}sTable.php
  Models/             ← Eloquent models
  Enums/
  Events/
  Listeners/
  Observers/
  Providers/          ← {Name}ServiceProvider
  Services/           ← {Name}Service
  Traits/
  Tests/
    Feature/          ← PHPUnit feature tests
    Unit/             ← PHPUnit unit tests
  resources/
    views/            ← Blade views namespaced as '{name}::'
  composer.json
  module.json
```

## Namespace Convention

```
Modules\{Name}\
Modules\{Name}\Models\
Modules\{Name}\Filament\Company\Resources\{Model}\{Model}Resource
Modules\{Name}\Database\Factories\{Model}Factory
Modules\{Name}\Database\Seeders\{Model}Seeder
Modules\{Name}\Services\{Model}Service
Modules\{Name}\Tests\Feature\{Model}Test
```

## Service Provider

Every module has a `{Name}ServiceProvider` registered in `config/app.php`:

```php
class ClientsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'clients');
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
        Client::observe(ClientObserver::class); // only if observer exists
    }
}
```

## Registering a New Module

1. Create the directory tree above.
2. Add `{Name}ServiceProvider` to `config/app.php` providers array.
3. Register the factory namespace in `app/Providers/AppServiceProvider.php`
   following the pattern used by existing modules (`Factory::guessFactoryNamesUsing`).
4. Add `->discoverResources(...)` to `CompanyPanelProvider`:

```php
->discoverResources(
    in: base_path('Modules/Mymodule/Filament/Company/Resources'),
    for: 'Modules\\Mymodule\\Filament\\Company\\Resources'
)
```

## Test Auto-Discovery

`phpunit.xml` discovers tests automatically — no registration needed:

```xml
<directory>Modules/*/Tests/Unit</directory>
<directory>Modules/*/Tests/Feature</directory>
```

Just put tests under `Modules/{Name}/Tests/Feature/` or `Tests/Unit/`.
