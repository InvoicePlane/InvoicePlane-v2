# InvoicePlane v2 — Claude Context

## What this is

Laravel 13 + Filament v5 + Livewire v4 invoicing app. Modular architecture via `nwidart/laravel-modules` v13. PHP 8.3+ (dev box: 8.4.23), Spatie roles/permissions, multi-tenancy via Filament's built-in tenant system scoped to `Company`.

**Resolved versions** (from `composer.lock`):
- `laravel/framework` 13.15.0 (PHP ^8.3)
- `filament/filament` 5.6.7 (+ 9 sub-packages @ 5.6.7)
- `livewire/livewire` 4.3.1
- `nwidart/laravel-modules` 13.0.0
- `spatie/laravel-permission` 8.0.0
- `danharrin/livewire-rate-limiting` 2.2.0

---

## Directory map

```
app/                        # Thin root (Http/Controllers/ empty, Providers/AppServiceProvider.php)
Modules/                    # All business logic lives here (8 modules)
bootstrap/                  # app.php
config/                     # modules.php, ip.php, filament.php, database.php, app.php
database/migrations/        # Only the exports table — all other migrations are per-module
database/seeders/           # DatabaseSeeder.php — seeds ivplv2 company at id=22
resources/css/filament/     # Per-panel Vite themes (company/nord.css, admin/*, user/*)
routes/                     # Empty — routing is handled entirely by Filament panel providers
Modules/Core/Providers/     # All three Filament panel providers live here
```

### Module inventory

| Module | Key models |
|--------|-----------|
| Core | User, Company, CompanyUser, TaxRate, Numbering, EmailTemplate, CustomField, Upload, Note, AuditLog, Setting, MailQueue |
| Clients | Relation (table: `relations`), Contact, Address, Communication |
| Invoices | Invoice, InvoiceItem, RecurringInvoice |
| Quotes | Quote, QuoteItem |
| Payments | Payment |
| Products | Product, ProductUnit, ProductCategory |
| Projects | Project, Task |
| Expenses | Expense, ExpenseCategory, ExpenseItem |

Every module follows:
```
Modules/<Name>/
  Database/{Factories,Migrations,Seeders}/
  Models/
  Filament/{Admin,Company}/Resources/<Resource>/{Pages,Tables,Schemas}/
  Services/
  Enums/
  Events/ Listeners/ Observers/
  Traits/ Helpers/
  Http/{Controllers,Requests,Middleware}/
  Providers/
  Tests/{Unit,Feature}/
```

---

## Filament panels

Three providers at `Modules/Core/Providers/`:

| Provider | Panel ID | URL path | Tenanted | Access roles |
|----------|----------|----------|----------|--------------|
| AdminPanelProvider | `admin` | `/admin` | No | SUPER_ADMIN, ADMIN, ASSIST |
| CompanyPanelProvider | `company` | `` (root, default) | Yes — `Company` by `search_code` | CUSTOMER_ADMIN, CUSTOMER |
| UserPanelProvider | `user` | `/user` | No | (minimal, for future use) |

### Company panel tenant config (critical)

```php
->tenant(Company::class, slugAttribute: 'search_code')
->tenantMiddleware([
    SetTenantFromQueryString::class,  // reads ?tenant=<search_code>, sets session + Filament tenant
    ConfigureTenant::class,           // resolves tenant from route/query/session/user fallback
    EnsureUserCanAccessCompany::class,// enforces access; 403 if regular user not in company_user
], isPersistent: true)
```

URLs look like `/ivplv2/dashboard` (search_code as route segment).

---

## Auth & tenancy flow

1. Login page: `Modules/Core/Filament/Pages/Auth/Login.php`
   - Rate-limited (5 attempts), checks `$user->is_active`, regenerates session
   - Returns `app(LoginResponse::class)`
2. `Modules/Core/Filament/Responses/LoginResponse.php`
   - Elevated users (super_admin/admin/assist): find company globally → `filament()->setTenant()`
   - Regular users: find company from `$user->companies()` → set session + Filament tenant
   - Redirects to `route('filament.company.pages.dashboard', ['tenant' => Str::lower($company->search_code)])`
3. Company panel middleware chain then enforces access on every subsequent request.
4. Logout: `Modules/Core/Filament/Responses/LogoutResponse.php` → redirects to `filament.company.auth.login`

### Key relationships

```php
// User ↔ Company — BelongsToMany via company_user pivot (id, company_id, user_id — no timestamps)
$user->companies()      // returns Collection<Company>
$company->users()

// All business models are scoped to a company via BelongsToCompany trait
// Trait auto-injects company_id on create (from Filament tenant / session / user's first company)
// Trait adds global scope — queries are always filtered by current company
```

---

## Roles & permissions (Spatie)

```php
// Modules/Core/Enums/UserRole.php
UserRole::SUPER_ADMIN  = 'super_admin'   // full system access
UserRole::ADMIN        = 'admin'
UserRole::ASSIST       = 'assist'
UserRole::CUSTOMER_ADMIN = 'client_admin'  // company-scoped admin
UserRole::CUSTOMER     = 'client'

UserRole::elevated()   // ['super_admin', 'admin', 'assist'] — can access any panel
UserRole::nonAdmin()   // ['client_admin', 'client'] — company panel only

// Usage
$user->hasRole(UserRole::SUPER_ADMIN->value)
$user->assignRole(UserRole::ADMIN->value)
$user->isSuperAdmin()  // shorthand
```

---

## Testing

> **This project uses PHPUnit exclusively. Pest is NOT installed and must NOT be used.**
> Never write `it()`, `test()`, `describe()`, `uses()`, or Pest `expect()` chains.
> All tests are class-based, extend one of the base classes below, and use `#[Test]` attributes.

### Base classes

| Class | Extends | Sets up | Use for |
|-------|---------|---------|---------|
| `AbstractAdminPanelTestCase` | TestCase + RefreshDatabase | Company factory + superAdmin user; panel='admin'; Carbon frozen 2026-01-01 | Admin panel Livewire tests |
| `AbstractCompanyPanelTestCase` | TestCase + RefreshDatabase | User with company (search_code='IVPLV2'); Filament tenant set quietly; session current_company_id | Company panel Livewire tests |
| `AbstractTestCase` | TestCase (no RefreshDatabase) | Nothing | Pure unit tests |

All live at `Modules/Core/Tests/`.

### Patterns

```php
// Company panel test
class FooTest extends AbstractCompanyPanelTestCase {
    public function it_does_something(): void {
        $response = Livewire::actingAs($this->user)
            ->test(ListFoo::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertSuccessful();
    }
    // Or use the helper:
    $this->testLivewire(ListFoo::class)->assertSuccessful();
}

// Admin panel test
class BarTest extends AbstractAdminPanelTestCase {
    public function it_does_something(): void {
        Livewire::actingAs($this->superAdmin())->test(ListBar::class)->assertSuccessful();
    }
}

// Create a role in tests (Spatie needs DB record)
Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
$user->assignRole(UserRole::SUPER_ADMIN->value);
```

### Factory patterns

```php
// User with a specific company attached
User::factory()->withCompany(['search_code' => 'IVPLV2', 'name' => '...'])->create()

// Company-scoped model — pass company via ->for() or via session/tenant
Invoice::factory()->for($company)->create()

// Active user
User::factory()->create(['is_active' => true, 'email_verified_at' => now()])
```

### PHPUnit test discovery

```xml
<!-- phpunit.xml -->
<testsuite name="Unit">   <directory>Modules/*/Tests/Unit</directory>  </testsuite>
<testsuite name="Feature"><directory>Modules/*/Tests/Feature</directory></testsuite>
```

### DB for tests

Tests need a real MariaDB DB — matching CI (MariaDB 11) — not SQLite. SQLite's lenient identifier
quoting has silently masked real bugs before (e.g. `->latest()` defaulting to a nonexistent
`created_at` column on `$timestamps = false` models passed locally, failed on CI). Run via the
`cli` compose service, which points at the stack's `db` service automatically:
```
docker compose run --rm cli php artisan test --exclude-group failing,troubleshooting
```
No `.env.testing` edits needed — the `cli` service injects `DB_CONNECTION=mysql`/`DB_HOST=db` etc.
itself. Use `php artisan test`, not `vendor/bin/phpunit` directly — the two have been observed to
behave differently for this app's Livewire form tests; `artisan test` is the reliable one.
**Known issue:** a freshly-rebuilt `cli` image has reproduced false Livewire-form failures at
scale even under `artisan test`, for reasons not yet isolated — see
[#689](https://github.com/InvoicePlane/InvoicePlane-v2/issues/689) and sanity-check with
`--filter=ContactsTest` (should be 11/11 passing) before trusting a full run from a rebuilt image.
See `.github/DOCKER.md`.

### AAA phase comment style

Phase labels (`Arrange`, `Act`, `Assert`) inside test methods **must** use block comments. Line comments (`//`) are **prohibited** for phase labels.

```php
/* Arrange */
...
/* Act */
...
/* Assert */

/* Act & Assert */   ← combined phase label, same rule
```

**Never** write `// Arrange`, `// Act`, or `// Assert`.

---

## Observers

All model lifecycle business logic (duplicate checks, validation, side effects on save/create/update/delete) goes in `Observers/`, **not** inline `booted()` methods on the model.

### Convention

- Create `Modules/<Name>/Observers/<Model>Observer.php` extending `Modules\Core\Observers\AbstractObserver`
- Register in the module's ServiceProvider `boot()`:
  ```php
  Model::observe(ModelObserver::class);
  ```
- Always use `Model::withoutGlobalScopes()` inside observers — global scopes (e.g. `BelongsToCompany`) may not be active in all contexts (unauthenticated, queued jobs, tests)
- **Do not** add `static::creating()` / `static::saving()` callbacks inside `booted()` on the model — the Observer handles this

### Example

```php
// Modules/Invoices/Observers/InvoiceObserver.php
class InvoiceObserver extends AbstractObserver {
    public function saving(Invoice $invoice): void {
        // saving fires before both creating and updating — covers all writes
    }
}

// Modules/Invoices/Providers/InvoicesServiceProvider.php  boot()
Invoice::observe(InvoiceObserver::class);
```

---

## Key model notes

- `Company::$primaryKey` = `id` (standard); URL slug is `search_code` (10 chars, unique, e.g. `ivplv2`)
- `User::$timestamps = false` — no created_at/updated_at on users table
- `Relation` model → table `relations` (not `customers`, not `clients`)
- Soft deletes on: Invoice, Quote (and their items)
- `BelongsToCompany` trait → adds `company()` BelongsTo, `scopeForCompany()`, and global scope

---

## Services

All services extend `Modules\Core\Services\BaseService`:
```php
// BaseService gives you:
$this->create(array $data): Model
$this->find($id): Model
$this->update(array $input, $model): Model
$this->delete($id): bool
$this->paginate($perPage): LengthAwarePaginator
$this->getCompanyId(): ?int   // resolves from Filament/session/user
```

No DTO layer — services accept arrays and return Eloquent models.

---

## Seeder

`database/seeders/DatabaseSeeder.php` seeds:
- `ivplv2` company hard-coded at `id=22`, `search_code='ivplv2'`, `name='InvoicePlane Corporation'`
- 5 additional random companies
- Spatie roles for all UserRole values
- One user per role, all attached to ivplv2

---

## Common debugging shortcuts

| Symptom | Check |
|---------|-------|
| Company-scoped query returns nothing | `session('current_company_id')` set? Filament tenant set? |
| 403 on company panel route | User in `company_user` pivot for that company? |
| Redirect after login goes to wrong company | `LoginResponse::DEFAULT_COMPANY_CODE` constant and `whereRaw('LOWER(search_code) = ?')` query |
| Factory creates model with null company_id | Pass `->for($company)` or set Filament tenant before creation |
| Livewire test fails with "No tenant" | Call `Filament::setTenant($company)` in test setUp |
| Test role check fails | Create Role DB record first with `Role::query()->firstOrCreate(...)` |

---

## Don't re-derive these

- The default company is **always** `ivplv2` (search_code, lowercase in URLs)
- Panel routes are named `filament.<panel-id>.pages.<slug>` and `filament.<panel-id>.resources.<resource>.<action>`
- `$user->companies()` returns the BelongsToMany — call `.first()`, `.attach()`, `.detach()` on it
- `Str::lower($company->search_code)` is always the URL tenant parameter
- The three tenant middleware classes live at `Modules/Core/Http/Middleware/`
- Panel providers live at `Modules/Core/Providers/`, not `app/Providers/`

# LESSONS

- Never write `*/` inside a PHP docblock (e.g. glob patterns like `Header*/Detail*`) — it terminates the comment and causes a parse error.
- When running a test suite in the background, redirect FULL output to a file — never pipe through `tail`/`head`, it destroys the failure details and forces a rerun.

---

## Report Builder Guardrails

- **Blade directive gotcha**: Blade directives (`@if`/`@endif`/etc.) inside report-builder brick preview/render templates (`Modules/Core/resources/views/report-builder/bricks/*/preview.blade.php`) must not be immediately preceded by a word character — Blade's directive-matching regex requires a non-word boundary before `@`. Always wrap literal values in `{{ }}` rather than concatenating raw text directly next to a directive, or the directive silently fails to compile and leaves e.g. an `@if` unclosed, causing a parse error.
- **`toPreviewHtml()` vs `toHtml()` convention**: In `Modules/Core/ReportBuilder/ReportBrickAction.php` and `Modules/Core/ReportBuilder/ReportIframeRenderer.php`, preview-context rendering must always call `toPreviewHtml()`, never `toHtml()` — `toHtml()` needs real entity data and is for print/export output only; using it in a preview context renders fine on load but turns into a near-empty box the moment the brick is inserted or reconfigured.
- **Mandatory regression tests for report-builder changes**: Any change touching `Modules/Core/ReportBuilder/`, `Modules/Core/Filament/Pages/Reports/`, `Modules/Core/Filament/Admin/Pages/ReportTemplates.php`, or `Modules/Core/resources/views/report-builder/bricks/**` must be run against, at minimum:
  `php artisan test --filter='AdminReportBuilderTest|CompanyReportBuilderTest|MasonDocumentConverterTest|MasonBricksTest'`
  run against a real MySQL/MariaDB connection. Note the single `--filter` with a `|`-joined regex, not repeated `--filter` flags — `php artisan test` silently drops all but the last `--filter` flag when it's passed more than once (verified 2026-09-04: `--filter=A --filter=B --filter=C --filter=D` only ran `D`'s tests).
- **Known non-report-builder issues, do not chase them here**: `UserProfileTest`'s tenant test fails only at full-suite scale (passes in isolation) — a pre-existing order-dependent flake, unrelated to the report builder. Ctrl+Z triggering undo on all 5 Mason iframe editors simultaneously when focus is outside the iframes is an `awcodes/mason` vendor limitation, not something patchable from application code.
