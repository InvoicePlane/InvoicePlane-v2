# Junie Guidelines — InvoicePlane v2

## How to Use These Guidelines

These guidelines contain verified facts about the InvoicePlane v2 codebase. **Trust them** and use them as the primary reference. Only search the repository when something is not covered here or contradicts what you find.

---

## Verified Tech Stack

- **Framework:** Laravel **11** (PHP **8.1**+)
- **UI:** Filament **4.0** + Livewire **3**
- **Modules:** `nwidart/laravel-modules`
- **Permissions:** `spatie/laravel-permission`
- **Multi-tenancy:** Filament Companies with `BelongsToCompany` trait
- **DB (prod):** MariaDB 11 | **DB (tests):** MariaDB 11 — no SQLite fallback (parity with CI)
- **Code quality:** Laravel Pint (PSR-12), PHPStan, Rector

---

## Project Architecture

All business logic lives in `Modules/`. The `app/` directory is intentionally thin.

### Module inventory

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

### Module directory layout

```
Modules/<Name>/
  Models/
  Services/               # extend BaseService; no DTO/Repository layer
  Enums/                  # PHP 8.1+ backed string enums
  Filament/
    Admin/Resources/      # Admin panel CRUD
    Company/Resources/
      <Resource>/
        Pages/   CreateX, EditX, ListX
        Tables/  XTable (columns, filters, actions)
        Schemas/ XForm (form schema)
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

| ID | Path | Tenant | Roles |
|----|------|--------|-------|
| `admin` | `/admin` | No | super_admin, admin, assist |
| `company` | `` **(root, empty)** | Yes — Company by `search_code` | client_admin, client |
| `user` | `/user` | No | (future) |

> Company panel path is **empty string** — URLs are `/{search_code}/{page}`, e.g. `/ivplv2/dashboard`.

Panel providers all live at `Modules/Core/Providers/`.

Route names: `filament.<panel-id>.pages.<slug>` and `filament.<panel-id>.resources.<resource>.<action>`

---

## Multi-Tenancy

```php
// URL tenant param — always lowercase search_code
Str::lower($company->search_code)   // e.g. 'ivplv2'

// Pivot: company_user (id, company_id, user_id — no timestamps)
$user->companies()          // BelongsToMany<Company>
$company->users()           // BelongsToMany<Company>

// Current tenant resolution (in order):
// 1. Filament::getTenant()
// 2. session('current_company_id')
// 3. auth()->user()->companies()->first()
```

Tenant middleware chain (company panel, persistent):
1. `SetTenantFromQueryString` → reads `?tenant=`, sets session + Filament tenant
2. `ConfigureTenant` → resolves from route/query/session/user
3. `EnsureUserCanAccessCompany` → 403 if user not in `company_user` for that company

`BelongsToCompany` trait (on every business model):
- Adds `company()` BelongsTo relationship
- Adds global scope filtering by `company_id` — queries are always tenant-scoped
- Auto-injects `company_id` on model creation

---

## Roles (verified Spatie values)

```php
UserRole::SUPER_ADMIN    = 'super_admin'    // full access, any panel
UserRole::ADMIN          = 'admin'
UserRole::ASSIST         = 'assist'
UserRole::CUSTOMER_ADMIN = 'client_admin'   // company panel only
UserRole::CUSTOMER       = 'client'

UserRole::elevated()   // ['super_admin', 'admin', 'assist']
UserRole::nonAdmin()   // ['client_admin', 'client']
```

Spatie roles require a DB record in tests:
```php
Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
$user->assignRole(UserRole::SUPER_ADMIN->value);
```

---

## Key Model Facts

- `User::$timestamps = false` — no created_at/updated_at
- `ClientCustom::$primaryKey = 'client_custom_id'`
- `Import::$primaryKey = 'import_id'`
- `Relation` → table `relations` (not `customers`, not `clients`)
- `Company::search_code` — 10-char unique string; URL slug
- Soft deletes on Invoice, Quote (and their items)
- Default company: `search_code='ivplv2'`, `id=22`, name `'InvoicePlane Corporation'` (seeder)

---

## Service Layer

**No DTO, Transformer, or Repository layer.** Services accept plain arrays, return Eloquent models.

```php
class InvoiceService extends BaseService {
    public function model(): string { return Invoice::class; }
    // Inherited from BaseService:
    // create(array $data): Model
    // find($id): Model
    // update(array $input, Model $model): Model
    // delete($id): bool
    // paginate(int $perPage): LengthAwarePaginator
    // getCompanyId(): ?int
}
```

---

## SOLID & Code Quality Principles

- **Single Responsibility:** one clear purpose per class
- **Early returns:** reduce nesting; validate inputs at the top of methods
- **No inline business logic:** logic in services, not resources/controllers
- **Centralize shared logic:** traits over copy-paste
- **Type hints everywhere:** native PHP types throughout

---

## Database & Model Rules

- **No `$fillable`** — use `$guarded = []`
- **No JSON or ENUM columns** in migrations
- **No `timestamps()` or `softDeletes()`** unless explicitly needed
- **Use `$casts`** for enum fields: `'status' => InvoiceStatus::class`
- Native PHP type hints on all properties and return types

---

## Internationalization

**Always `trans()`, never `__()`:**

```php
// ❌ WRONG
$label = __('ip.invoice_total');

// ✅ CORRECT
$label = trans('ip.invoice_total');
```

Translation keys: `resources/lang/en/ip.php`, prefix `ip.`, snake_case.
Apply to: form labels, placeholders, helper text, section titles, button labels, table headers, messages.

---

## Filament Conventions

Resource anatomy:
```
InvoiceResource.php      — getModel(), navigationIcon(), getPages()
Pages/ListInvoices.php   — extends ListRecords
Pages/CreateInvoice.php  — extends CreateRecord
Pages/EditInvoice.php    — extends EditRecord
Tables/InvoicesTable.php — static table(Table $table): Table
Schemas/InvoiceForm.php  — static form(Schema $schema): Schema
```

- Respect panel namespace: `Filament/Admin/` vs `Filament/Company/`
- Use `Action::make()` with fluent method chains
- Do not display raw `created_at`/`updated_at` — use formatted timestamp columns

---

## Common Pitfalls

1. Never add business logic to `app/` — use `Modules/`
2. Never define routes in `routes/` — panels handle routing
3. Never use `$fillable` — use `$guarded = []`
4. Never add JSON or ENUM columns to migrations
5. Never call `Invoice::all()` inside tenant context without company scope — BelongsToCompany adds it automatically
6. Never skip `->for($company)` on factory calls for company-scoped models (results in `null` company_id)
7. Never use `__()` for translations — always `trans()`
8. Never use floats as array keys — cast to string first

---

## Development Commands

```bash
# Setup
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan queue:work   # required for export functionality

# Validation pipeline (run in order before committing)
vendor/bin/pint                   # 1. format code
vendor/bin/phpstan analyse         # 2. static analysis
php artisan test                   # 3. all tests must pass
```

See `.github/git-commit-instructions.md` for commit message conventions.

---

## CI/CD (all must pass before merge)

- `php artisan test` — PHPUnit green
- `vendor/bin/phpstan analyse` — no type errors
- `vendor/bin/pint` — PSR-12 compliant
- Docker build — must succeed

---

## Reference Files

| File | Content |
|------|---------|
| `.github/copilot-instructions.md` | Copilot-specific context (same facts, Copilot format) |
| `.junie/testing.md` | Testing patterns, base classes, factory cheat sheet |
| `.junie/architecture.md` | Auth flow, middleware, seeder details |
| `CLAUDE.md` | Claude Code context (same facts, Claude format) |
| `AGENTS.md` | OpenAI Codex / Agents context |
| `.github/INSTALLATION.md` | Installation guide |
| `.github/CONTRIBUTING.md` | Contributing guide |
| `.github/git-commit-instructions.md` | Commit conventions |
| `Modules/Core/Filament/Exporters/README.md` | Export architecture |
