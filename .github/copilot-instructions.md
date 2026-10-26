# GitHub Copilot Instructions — InvoicePlane v2

## How to Use These Instructions

These instructions contain verified facts about the InvoicePlane v2 codebase. **Trust them** — only search the repo when something is not covered here or contradicts what you find.

---

## Verified Tech Stack

- **Framework:** Laravel **11** (PHP **8.1**+) — not 12/8.2
- **UI:** Filament **4.0** + Livewire **3**
- **Modules:** `nwidart/laravel-modules`
- **Permissions:** `spatie/laravel-permission`
- **Multi-tenancy:** Filament Companies with `BelongsToCompany` trait
- **DB (production):** MariaDB 11
- **DB (tests):** MariaDB 11 — no SQLite fallback (parity with CI)
- **Code quality:** Laravel Pint (PSR-12), PHPStan, Rector

---

## Project Layout

```
app/                    # Thin root — Providers/AppServiceProvider.php only
Modules/                # ALL business logic lives here
Modules/Core/Providers/ # All three Filament panel providers
config/                 # modules.php, ip.php, filament.php, database.php
database/migrations/    # Only the exports table; all others are per-module
database/seeders/       # DatabaseSeeder — seeds ivplv2 company + roles + users
resources/css/filament/ # Per-panel Vite themes
routes/                 # Empty — all routing is done by Filament panel providers
```

### Modules

| Module | Key models |
|--------|-----------|
| Core | User, Company, CompanyUser, TaxRate, Numbering, EmailTemplate, CustomField, Upload, Note, AuditLog, Setting, MailQueue |
| Clients | Relation (table: `relations`), Contact, Address, Communication, ClientCustom (PK: `client_custom_id`) |
| Invoices | Invoice, InvoiceItem, RecurringInvoice |
| Quotes | Quote, QuoteItem |
| Payments | Payment |
| Products | Product, ProductUnit, ProductCategory |
| Projects | Project, Task |
| Expenses | Expense, ExpenseCategory, ExpenseItem |

### Module internal layout

```
Modules/<Name>/
  Models/
  Services/               # extend Modules\Core\Services\BaseService
  Enums/                  # PHP 8.1+ backed string enums
  Filament/
    Admin/Resources/      # Admin panel resources
    Company/Resources/    # Company panel resources — Pages/, Tables/, Schemas/
  Database/
    Factories/            # extend AbstractFactory
    Migrations/           # auto-discovered
    Seeders/
  Events/ Listeners/ Observers/
  Traits/ Helpers/ Http/ Providers/
  Tests/Feature/ Tests/Unit/
```

---

## Filament Panels (verified)

| Provider | Panel ID | URL path | Tenant | Access |
|----------|----------|----------|--------|--------|
| AdminPanelProvider | `admin` | `/admin` | No | super_admin, admin, assist |
| CompanyPanelProvider | `company` | `` **(root, default)** | Yes — Company by `search_code` | client_admin, client |
| UserPanelProvider | `user` | `/user` | No | (future) |

> **The company panel path is an empty string** — URLs look like `/ivplv2/dashboard`, not `/company/...`.

Panel route naming: `filament.<panel-id>.pages.<slug>` and `filament.<panel-id>.resources.<resource>.<action>`

Tenant middleware stack (company panel, persistent):
1. `SetTenantFromQueryString` — reads `?tenant=<search_code>`, sets session + Filament tenant
2. `ConfigureTenant` — resolves from route/query/session/user fallback
3. `EnsureUserCanAccessCompany` — 403 if regular user not in `company_user` pivot

---

## Roles (verified Spatie values)

```php
// Modules/Core/Enums/UserRole.php
UserRole::SUPER_ADMIN    = 'super_admin'    // full access, any panel
UserRole::ADMIN          = 'admin'
UserRole::ASSIST         = 'assist'
UserRole::CUSTOMER_ADMIN = 'client_admin'   // company panel only
UserRole::CUSTOMER       = 'client'         // company panel only

UserRole::elevated()   // ['super_admin', 'admin', 'assist']
UserRole::nonAdmin()   // ['client_admin', 'client']
```

Seeder seeds exactly these five roles. Create DB records before assigning in tests:
```php
Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
$user->assignRole(UserRole::SUPER_ADMIN->value);
```

---

## Key Model Facts

- `User::$timestamps = false` — no created_at/updated_at on users
- `ClientCustom::$primaryKey = 'client_custom_id'` — non-standard PK
- `Import::$primaryKey = 'import_id'` — non-standard PK
- `Relation` model → table `relations` (not `customers`, not `clients`)
- `Company::search_code` — 10-char unique slug used in URLs (e.g. `ivplv2`)
- Soft deletes on Invoice, Quote (and their items)
- `company_user` pivot: columns `id, company_id, user_id` (no timestamps)
- Default company: `search_code='ivplv2'`, `id=22` (hard-coded in seeder)

`BelongsToCompany` trait (used on every business model):
- Adds `company()` BelongsTo relationship
- Adds global scope filtering by `company_id`
- Auto-injects `company_id` on create from Filament tenant / session / user

---

## Service Layer

All services extend `Modules\Core\Services\BaseService`. **No DTO or Repository layer** — services accept plain arrays and return Eloquent models.

```php
$service->create(array $data): Model
$service->find($id): Model
$service->update(array $input, Model $model): Model
$service->delete($id): bool
$service->paginate(int $perPage): LengthAwarePaginator
$service->getCompanyId(): ?int   // resolves Filament tenant → session → user's company
```

---

## Testing

### Base classes (Modules/Core/Tests/)

```
AbstractTestCase               — no RefreshDatabase; pure unit tests
AbstractAdminPanelTestCase     — RefreshDatabase; panel='admin'; $this->company; $this->superAdmin()
AbstractCompanyPanelTestCase   — RefreshDatabase; panel='company'; $this->user; $this->company
```

**NEVER extend `Tests\TestCase`** — always extend one of the three above.

### Test patterns

```php
// Company panel (most tests):
class InvoicesTest extends AbstractCompanyPanelTestCase {
    #[Test]
    #[Group('smoke')]
    public function it_lists_invoices(): void {
        /* Arrange */
        $invoice = Invoice::factory()->for($this->company)->create();

        /* Act */
        $component = $this->testLivewire(ListInvoices::class);

        /* Assert */
        $component->assertSuccessful()->assertSee($invoice->number);
    }
}

// Admin panel:
class UsersTest extends AbstractAdminPanelTestCase {
    #[Test]
    public function it_lists_users(): void {
        Livewire::actingAs($this->superAdmin())->test(ListUsers::class)->assertSuccessful();
    }
}
```

### Factory patterns

```php
User::factory()->withCompany(['search_code' => 'IVPLV2'])->create()
User::factory()->create(['is_active' => true, 'email_verified_at' => now()])
Invoice::factory()->for($this->company)->create()   // always ->for($company) on scoped models
Company::factory()->create(['search_code' => 'acme'])
```

### Test conventions

- Method names: `it_<verb>_<subject>` (snake_case)
- Use `#[Test]` attribute (not `@test`)
- Use `#[Group('smoke|crud|security|authentication|...')]`
- Structure with `/* Arrange */`, `/* Act */`, `/* Assert */` blocks
- Define all variables in the "act" section before asserting on them
- Prefer fakes over mocks (`Queue::fake()`, `Storage::fake()`)

### PHPUnit discovery

```xml
<testsuite name="Unit">   <directory>Modules/*/Tests/Unit</directory>   </testsuite>
<testsuite name="Feature"><directory>Modules/*/Tests/Feature</directory></testsuite>
```

---

## Development Commands

```bash
# Tests
php artisan test                        # all tests (~30-60s)
php artisan test --testsuite=Unit       # unit only
php artisan test --testsuite=Feature    # feature only
php artisan test --group smoke          # by group

# Code quality (run in this order before committing)
vendor/bin/pint                         # format PSR-12
vendor/bin/phpstan analyse              # static analysis
php artisan test                        # all tests must pass
```

---

## Database & Model Conventions

- **No `$fillable` in models** — use `$guarded = []`
- **No JSON or ENUM columns** in migrations
- **No `timestamps()` or `softDeletes()`** in migrations unless explicitly needed
- **Use `$casts`** for enum fields: `'status' => InvoiceStatus::class`
- Use native PHP type hints throughout

---

## Filament Resource Conventions

Each resource is split across focused files:
```
InvoiceResource.php         — getModel(), navigationIcon(), getPages()
Pages/ListInvoices.php      — extends ListRecords
Pages/CreateInvoice.php     — extends CreateRecord
Pages/EditInvoice.php       — extends EditRecord
Tables/InvoicesTable.php    — static table(Table $table): Table
Schemas/InvoiceForm.php     — static form(Schema $schema): Schema
```

Rules:
- Respect panel namespace separation (Admin/ vs Company/)
- Use `Action::make()` with fluent methods
- Do not display raw `created_at`/`updated_at` in tables

---

## Internationalization

**Always use `trans()`, never `__()`:**

```php
// ❌ WRONG
$label = __('ip.invoice_total');

// ✅ CORRECT
$label = trans('ip.invoice_total');
```

Translation keys: `resources/lang/en/ip.php`, prefixed `ip.`, snake_case.

---

## Coding Rules Summary

- Business logic lives in `Modules/` — never in `app/`
- No routes in `routes/` — panels handle all routing
- No DTO or Repository layer — services use plain arrays
- `$user->companies()` BelongsToMany — use `.first()`, `.attach()`, `.detach()`
- `Str::lower($company->search_code)` is always the URL tenant parameter
- `$user->isSuperAdmin()` shorthand for role check

---

## CI/CD (must pass before merge)

- `php artisan test` — all tests green
- `vendor/bin/phpstan analyse` — no type errors
- `vendor/bin/pint` — PSR-12 compliant
