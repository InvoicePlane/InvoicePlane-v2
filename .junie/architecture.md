# Architecture — InvoicePlane v2

## Auth & login flow

```
POST /login (Filament Livewire Login component)
  Modules/Core/Filament/Pages/Auth/Login.php
    → rate-limit (5/min), check is_active, regenerate session
    → return app(LoginResponse::class)

  Modules/Core/Filament/Responses/LoginResponse.php
    → elevated user? (super_admin / admin / assist)
        Company::whereRaw('LOWER(search_code) = ?', ['ivplv2'])->first()
        ?? Company::query()->first()
        filament()->setTenant($company)
    → regular user? (client_admin / client)
        $user->companies()->whereRaw('LOWER(search_code) = ?', ['ivplv2'])->first()
        ?? $user->companies()->first()
        session(['current_company_id' => $company->id])
        filament()->setTenant($company)
    → redirect to filament.company.pages.dashboard [tenant=<search_code>]

Logout: Modules/Core/Filament/Responses/LogoutResponse.php
    → redirect to filament.company.auth.login
```

---

## Tenant resolution middleware (company panel, every request)

```
1. SetTenantFromQueryString   (Modules/Core/Http/Middleware/)
     reads request('tenant') — the search_code from the URL segment
     queries Company where LOWER(search_code) = lower(tenant)
     sets session('current_company_id') + filament()->setTenant()

2. ConfigureTenant
     resolves tenant: route param → query string → session → user's first company
     shares $currentCompany to all views

3. EnsureUserCanAccessCompany
     elevated users: pass unconditionally
     regular users: must exist in company_user pivot — otherwise abort(403)
     updates session('current_company_id')
```

---

## Company scoping (BelongsToCompany trait)

Every business model applies this trait. It:

1. Registers a **global scope** that appends `WHERE company_id = <current>` to all queries
2. On model `creating`, auto-injects `company_id` from:
   - `Filament::getTenant()->id`
   - `session('current_company_id')`
   - `auth()->user()->companies()->first()->id`
3. Adds `company()` BelongsTo relationship
4. Adds `scopeForCompany(int $companyId)` named scope

**Consequence:** Never do raw `Invoice::all()` inside tenant context — the global scope filters automatically. In tests, set the tenant before creating company-scoped factories.

---

## Filament resource anatomy

Each resource is split into focused files:

```
InvoiceResource.php      — getModel(), navigationIcon(), navigationGroup(), getPages()
Pages/
  ListInvoices.php       — extends ListRecords; table configuration via InvoicesTable
  CreateInvoice.php      — extends CreateRecord; form via InvoiceForm
  EditInvoice.php        — extends EditRecord; form via InvoiceForm
Tables/
  InvoicesTable.php      — static table(Table $table): Table — columns, filters, actions
Schemas/
  InvoiceForm.php        — static form(Schema $schema): Schema — fields, sections, repeaters
```

---

## Seeder (database/seeders/DatabaseSeeder.php)

```
Companies:
  id=22, search_code='ivplv2', name='InvoicePlane Corporation'  ← hard-coded default
  5 additional random companies

Roles (Spatie):
  super_admin, admin, assist, client_admin, client

Users (one per role, all attached to ivplv2 company):
  super_admin@example.com  / password
  admin@example.com        / password
  assist@example.com       / password
  client_admin@example.com / password
  client@example.com       / password
```

---

## Key relationships

```php
// User ↔ Company (pivot: company_user — id, company_id, user_id, no timestamps)
$user->companies()            // BelongsToMany<Company>
$company->users()             // BelongsToMany<User>
$user->companies()->attach($company->id)
$user->companies()->detach($company->id)

// Business models → Company (via BelongsToCompany trait)
$invoice->company()           // BelongsTo<Company>
$company->invoices()          // HasMany<Invoice>

// Relations (customers) → Contacts, Addresses
$relation->contacts()         // HasMany<Contact>
$relation->primaryContact()   // BelongsTo<Contact>
$relation->addresses()        // MorphMany<Address>
```

---

## Config files

| File | Purpose |
|------|---------|
| `config/modules.php` | nwidart module settings; migration auto-discovery |
| `config/ip.php` | InvoicePlane custom settings |
| `config/filament.php` | Filament framework config |
| `config/database.php` | DB connections (mysql as `mariadb` driver in prod) |

## Environment variables worth knowing

```
APP_EXTREME_LOGGING=true   # verbose company-scope debug output
DB_CONNECTION=mysql        # tests run against real MariaDB, no SQLite fallback
DB_DATABASE=invoiceplane_test
```
