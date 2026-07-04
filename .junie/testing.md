# Testing — InvoicePlane v2

## Base classes

All live at `Modules/Core/Tests/`.

### AbstractCompanyPanelTestCase

Use for any test that exercises the company panel (most tests).

```php
class MyTest extends AbstractCompanyPanelTestCase {
    // Available in every test method:
    // $this->user    — active User, attached to $this->company
    // $this->company — Company with search_code='IVPLV2'
    //
    // Filament tenant is pre-set, session current_company_id is pre-set
}
```

### AbstractAdminPanelTestCase

Use for admin panel tests.

```php
class MyTest extends AbstractAdminPanelTestCase {
    // Available:
    // $this->company  — random Company
    // $this->superAdmin() — User (no role assigned, panel set to 'admin')
}
```

### AbstractTestCase

Pure unit tests — no database refresh, no app setup beyond boot.

---

## Writing Livewire tests

```php
// Preferred shorthand (company panel):
$this->testLivewire(ListInvoices::class)->assertSuccessful();

// Explicit form:
Livewire::actingAs($this->user)
    ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)])
    ->assertSuccessful();

// Admin panel:
Livewire::actingAs($this->superAdmin())
    ->test(ListUsers::class)
    ->assertSuccessful();

// Filling a Filament form:
Livewire::actingAs($this->user)
    ->test(CreateInvoice::class, ['tenant' => Str::lower($this->company->search_code)])
    ->fillForm(['number' => 'INV-001', 'status' => InvoiceStatus::DRAFT->value])
    ->call('create')
    ->assertHasNoErrors();
```

---

## Factories

```php
// User with a specific company attached (company is created + pivoted automatically)
User::factory()->withCompany(['search_code' => 'IVPLV2', 'name' => 'InvoicePlane Corp'])->create()

// Active, verified user
User::factory()->create(['is_active' => true, 'email_verified_at' => now()])

// Always pass ->for($company) on company-scoped models — omitting it leaves company_id null
Invoice::factory()->for($this->company)->create()
Expense::factory()->for($this->company)->create(['amount' => 50.00])
```

---

## Roles in tests

Spatie reads roles from the database. Create the DB record before assigning:

```php
Role::query()->firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
$user->assignRole(UserRole::SUPER_ADMIN->value);
```

---

## Test method conventions

```php
#[Test]
#[Group('smoke')]           // smoke | crud | security | authentication | redirect | edge-cases
public function it_lists_invoices(): void
{
    /* Arrange */

    /* Act */

    /* Assert */
}
```

- Method names: `it_<verb>_<subject>` (snake_case)
- Always use `#[Test]` attribute (not `/** @test */`)
- Always add `#[Group(...)]` for filtering
- Use `/* Arrange / Act / Assert */` block comments

---

## PHPUnit discovery

```xml
<!-- phpunit.xml -->
<testsuite name="Unit">   <directory>Modules/*/Tests/Unit</directory>   </testsuite>
<testsuite name="Feature"><directory>Modules/*/Tests/Feature</directory></testsuite>
```

Run all:        `php artisan test`
Run one module: `php artisan test Modules/Invoices/Tests/`
Run one file:   `php artisan test Modules/Invoices/Tests/Feature/InvoicesTest.php`
Run a group:    `php artisan test --group smoke`

---

## Database for tests

CI uses MariaDB 11. Locally, if no MySQL is running, add to `.env.testing`:
```
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

Carbon is frozen to `2026-01-01 00:00:00` in both panel test base classes — use `Carbon::parse('2026-01-01')` in expected date assertions.

---

## Common pitfalls

| Problem | Fix |
|---------|-----|
| Model created with `null` company_id | Add `->for($this->company)` to factory call |
| Livewire test throws "No tenant" | `Filament::setTenant($company)` in setUp |
| Role check silently fails | Create Spatie `Role` DB record before `assignRole()` |
| Test hits MySQL instead of SQLite | Check `.env.testing` has `DB_CONNECTION=sqlite` |
| `assertRedirect` fails on login test | Set `filament()->setCurrentPanel(filament()->getPanel('company'))` in setUp |
