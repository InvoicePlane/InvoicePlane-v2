Contributing to InvoicePlane V2

Thank you for considering contributing to **InvoicePlane V2** — a Laravel + Filament modular rewrite of the original InvoicePlane. This guide outlines our project rules and contribution standards.

---

## Structure & Standards

- **Laravel 11+**, **PHP 8.3**
- **Filament** used for all UI
- **Livewire** used for reactive components
- **Modular** folder structure only:
  - `Modules/{Module}/Filament/Admin/Resources/`
  - `Modules/{Module}/Services/`
  - `Modules/{Module}/Tests/Feature/`
- Use:
  - `BelongsToCompany` trait for multi-tenancy
  - `DTOs` + `Transformers` for all data
  - `Services` for all business logic

---

## Code Formatting

```bash
vendor/bin/pint
php artisan test
```

- Follow PSR-12
- No logic in Filament or Livewire pages
- No inline DTO construction
- No tight coupling — use DI and clear return types

---

Test Requirements

All tests go in: Modules/{Module}/Tests/Feature/

Extend: AbstractAdminPanelTestCase OR AbstractCompanyPanelTestCase OR AbstractTestCase (for Unit tests)

Use: #[Test] and #[Group('crud')] or #[Group('smoke')]

All tests should have:

- @payload block (if input-based)
- it_ prefix in the method

```
#[Test]
#[Group('crud')]
/**
 * @payload missing: invoice_number
 * {
 *   "customer_id": 1,
 *   "due_date": "2025-06-01"
 * }
 */
public function it_fails_to_create_invoice_without_required_invoice_number(): void
```

---

Pull Requests

- One PR per feature or fix
- Reference rows in CHECKLIST.md
- Translate if needed
- Add tests where you can



---

Translation

All strings use trans('...')

Translations managed via Crowdin:
https://translations.invoiceplane.com



---

Community

Discord

Forums

Issues


