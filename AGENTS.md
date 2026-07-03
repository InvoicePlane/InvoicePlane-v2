# InvoicePlane v2 — Agent Context

## What this is

Laravel 11 + Filament v4 + Livewire v3 invoicing app. Modular architecture via `nwidart/laravel-modules`. PHP 8.1+ enums, Spatie roles/permissions, multi-tenancy scoped to `Company`.

## Setup

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate && php artisan db:seed
# Tests (no MySQL locally? use SQLite)
cp .env.testing.example .env.testing
# set DB_CONNECTION=sqlite, DB_DATABASE=:memory: in .env.testing
php artisan test
```

---

## Directory map

```
app/                        # Thin root (Providers/AppServiceProvider.php only)
Modules/                    # All business logic (8 modules)
Modules/Core/Providers/     # All three Filament panel providers
config/                     # modules.php, ip.php, filament.php, database.php
database/migrations/        # Only the exports table — all other migrations are per-module
database/seeders/           # Seeds ivplv2 company (id=22) + 5 random + all roles
resources/css/filament/     # Per-panel Vite themes
routes/                     # Empty — routing done entirely by Filament panel providers
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

Module structure:
```
Modules/<Name>/
  Database/{Factories,Migrations,Seeders}/
  Models/
  Filament/{Admin,Company}/Resources/<Resource>/{Pages,Tables,Schemas}/
  Services/         # extend Modules\Core\Services\BaseService
  Enums/            # PHP 8.1+ backed string enums
  Events/ Listeners/ Observers/
  Traits/ Helpers/
  Http/{Controllers,Requests,Middleware}/
  Providers/
  Tests/{Unit,Feature}/
```

---

## Filament panels

| Provider | Panel ID | URL path | Tenanted | Roles |
|----------|----------|----------|----------|-------|
| AdminPanelProvider | `admin` | `/admin` | No | SUPER_ADMIN, ADMIN, ASSIST |
| CompanyPanelProvider | `company` | `` (root, default) | Yes — Company by `search_code` | CUSTOMER_ADMIN, CUSTOMER |
| UserPanelProvider | `user` | `/user` | No | (future) |

Company panel tenant middleware (persistent):
1. `SetTenantFromQueryString` — reads `?tenant=<search_code>`, sets session + Filament tenant
2. `ConfigureTenant` — resolves tenant from route/query/session/user fallback
3. `EnsureUserCanAccessCompany` — 403 if regular user not in `company_user` pivot

URLs: `/ivplv2/dashboard` — `search_code` is the route segment, always lowercase.

Panel routes: `filament.<panel-id>.pages.<slug>` and `filament.<panel-id>.resources.<resource>.<action>`

---

## Auth flow

```
Login page (Modules/Core/Filament/Pages/Auth/Login.php)
  → checks is_active, rate-limits, regenerates session
  → returns app(LoginResponse::class)

LoginResponse (Modules/Core/Filament/Responses/LoginResponse.php)
  → elevated (super_admin/admin/assist): Company::whereRaw('LOWER(search_code) = ?', ['ivplv2'])->first()
                                         filament()->setTenant($tenant)
  → regular (client_admin/client):      $user->companies()->whereRaw(...)->first()
                                         session(['current_company_id' => $tenant->id])
                                         filament()->setTenant($tenant)
  → redirect()->route('filament.company.pages.dashboard', ['tenant' => Str::lower($tenant->search_code)])
```

---

## Roles

```php
// Modules/Core/Enums/UserRole.php
SUPER_ADMIN = 'super_admin'      // full access, any panel
ADMIN       = 'admin'            // elevated
ASSIST      = 'assist'           // elevated
CUSTOMER_ADMIN = 'client_admin'  // company panel only
CUSTOMER    = 'client'           // company panel only

UserRole::elevated()  // ['super_admin', 'admin', 'assist']
UserRole::nonAdmin()  // ['client_admin', 'client']

$user->hasRole(UserRole::SUPER_ADMIN->value)
$user->assignRole(UserRole::ADMIN->value)
$user->isSuperAdmin()
```

Spatie roles need a DB record in tests:
```php
Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
```

---

## Key model facts

- `Company::$primaryKey = 'id'`; URL slug is `search_code` (10 chars, unique, e.g. `ivplv2`)
- `User::$timestamps = false`
- `ClientCustom::$primaryKey = 'client_custom_id'`
- `Import::$primaryKey = 'import_id'`
- `Relation` model → table `relations` (not `customers`)
- Soft deletes on Invoice, Quote (and their items)
- `BelongsToCompany` trait — all business models use it; auto-injects `company_id` on create, adds global scope
- `company_user` pivot: columns `id, company_id, user_id` (no timestamps)
- `$user->companies()` — BelongsToMany; `$company->users()` — BelongsToMany

Default company: `ivplv2` — search_code `'ivplv2'`, name `'InvoicePlane Corporation'`, `id=22` (hard-coded in seeder).

---

## Services

All extend `Modules\Core\Services\BaseService`:
```php
$this->create(array $data): Model
$this->find($id): Model
$this->update(array $input, $model): Model
$this->delete($id): bool
$this->paginate($perPage): LengthAwarePaginator
$this->getCompanyId(): ?int   // resolves from Filament/session/user
```

No DTO layer — services accept plain arrays.

---

## Testing

### Base classes (`Modules/Core/Tests/`)

```php
AbstractAdminPanelTestCase   // RefreshDatabase; panel='admin'; $this->company; $this->superAdmin()
AbstractCompanyPanelTestCase // RefreshDatabase; panel='company'; $this->user; $this->company (search_code='IVPLV2')
AbstractTestCase             // no RefreshDatabase; pure unit tests
```

### Writing tests

```php
// Company panel (most common)
class FooTest extends AbstractCompanyPanelTestCase {
    public function it_lists_foo(): void {
        $this->testLivewire(ListFoo::class)->assertSuccessful();
        // or explicitly:
        Livewire::actingAs($this->user)
            ->test(ListFoo::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertSuccessful();
    }
}

// Admin panel
class BarTest extends AbstractAdminPanelTestCase {
    public function it_lists_bar(): void {
        Livewire::actingAs($this->superAdmin())->test(ListBar::class)->assertSuccessful();
    }
}
```

### Factories

```php
User::factory()->withCompany(['search_code' => 'IVPLV2'])->create()
User::factory()->create(['is_active' => true, 'email_verified_at' => now()])
Company::factory()->create(['search_code' => 'acme'])
Invoice::factory()->for($company)->create()   // always pass ->for() on scoped models
```

### PHPUnit discovery

```xml
<testsuite name="Unit">   <directory>Modules/*/Tests/Unit</directory>   </testsuite>
<testsuite name="Feature"><directory>Modules/*/Tests/Feature</directory></testsuite>
```

---

## Debugging quick-reference

| Symptom | Where to look |
|---------|--------------|
| Company-scoped query returns nothing | `session('current_company_id')` set? Filament tenant set? |
| 403 on company panel | User in `company_user` for that company? |
| Login redirects to wrong company | `LoginResponse` — checks `LOWER(search_code) = 'ivplv2'` first |
| Model created with null company_id | Missing `->for($company)` on factory |
| Livewire test "No tenant" error | `Filament::setTenant($company)` in setUp |
| Role check fails in tests | Create Spatie Role DB record first |
