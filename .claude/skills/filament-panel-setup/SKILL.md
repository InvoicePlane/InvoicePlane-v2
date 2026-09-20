---
name: filament-panel-setup
description: "Configures Filament panel providers. Activates when adding a new panel, registering module resources in a panel, configuring tenant middleware, adjusting auth or theme settings, or when the user mentions PanelProvider, viteTheme, discoverResources, or panel configuration."
license: MIT
metadata:
  author: project
---

# Filament Panel Setup

This app has three panels:

| Panel | Provider | Id | Default? | Tenant | Access |
|-------|----------|----|----------|--------|--------|
| Company | `CompanyPanelProvider` | `company` | Yes (root) | `Company::class` | `client_admin`, `client` |
| Admin | `AdminPanelProvider` | `admin` | No | None | `super_admin`, `admin`, `assist` |
| User | `UserPanelProvider` | `user` | No | None | minimal, future use |

All three providers live at `Modules/Core/Providers/`.

## Registering Module Resources

Add a `->discoverResources()` call per module in `CompanyPanelProvider`:

```php
->discoverResources(
    in: base_path('Modules/mymodule/src/Filament/Resources'),
    for: 'Modules\\Mymodule\\Filament\\Resources'
)
```

The `in` path is a filesystem path, `for` is the PHP namespace prefix. Both must
match the module's actual directory and namespace exactly.

## Tenant Panel Required Config

```php
->tenant(Company::class)           // sets the tenant model
->tenantMenu(false)                // hides the built-in tenant switcher
->tenantMiddleware([...], isPersistent: true)
```

## Auth Flow

```php
->login(Login::class)      // custom login page
->registration()
->passwordReset()
->emailVerification()
```

## Colors and Font

All three panels use Filament's stock default color palette (no custom `->colors()` override,
no custom `->viteTheme()`) and `Poppins` via `GoogleFontProvider`.

## SPA Mode

The admin panel enables SPA mode for fast navigation:
```php
->spa()
```

Do NOT add SPA mode to the company panel — it causes issues with tenant middleware
and full-page redirects required for company switching.

## Panel Responsibilities

Panels configure:

- authentication
- navigation
- resources
- middleware
- appearance

Panels must not contain business logic.

Business logic belongs in services.

---

## Resource Registration

If one module registers resources via discoverResources(),
all modules should follow the same convention.

Avoid mixing manual registration and discovery.
