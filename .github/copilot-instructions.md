# GitHub Copilot Context

This is the default Copilot prompt for this project.

## Project Description

This project is **InvoicePlane v2**, a **multi-tenant Laravel application** with a **modular architecture**.

- The application uses **Laravel Filament** for Admin Panel, Company Panel, and InvoicePanel interfaces.
- Code is structured into **Modules**, each module encapsulating its own logic (models, services, repositories, DTOs,
  transformers, tests, etc.).
- Tests for each module are located in:  
  `/Modules/(ModuleName)/Tests`

## Tech Stack

- **Backend:** Laravel 12+ (PHP 8.2+)
- **UI Framework:** Filament 4.0
- **Frontend:** Livewire, Tailwind CSS
- **Testing:** PHPUnit 11+
- **Code Quality:** Laravel Pint (PSR-12), PHPStan, Rector
- **Module System:** nwidart/laravel-modules
- **Permissions:** spatie/laravel-permission
- **Multi-tenancy:** Filament Companies with `BelongsToCompany` trait

## Development Commands

### Testing
```bash
# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

### Code Quality
```bash
# Format code with Laravel Pint
vendor/bin/pint

# Run static analysis
vendor/bin/phpstan analyse

# Run Rector for automated refactoring
vendor/bin/rector process --dry-run
```

### Setup & Installation
```bash
# See .github/INSTALLATION.md for detailed setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Related Documentation

- **Installation:** `.github/INSTALLATION.md`
- **Contributing:** `.github/CONTRIBUTING.md`
- **Seeding:** `.github/SEEDING.md`
- **Testing:** See test examples in `Modules/*/Tests/`
- **Commit Conventions:** `.github/git-commit-instructions.md`

## Guidelines

- **SOLID Principles** must be followed at all times.
- **Early returns** are preferred for readability.
- **Dynamic programming practices** must be applied where relevant.
- **Code must be modular and refactored**; avoid inline data setups.
- **No JSON columns** in Laravel migrations.
- **No ENUM columns** in Laravel migrations.
- **Abstractions must reduce dependencies** while ensuring single responsibility.
- **Centralize shared functionality in Traits** (avoid duplication).
- **Catch `Error`, `ErrorException`, and `Throwable` separately.**
- **Class names must always be provided in Markdown code blocks** for approval.

### DTO & Transformer Rules

- **All DTOs must avoid constructors.**
- DTOs use static named constructors when necessary.
- DTOs rely on getters and setters for data access.
- **All DTOs get transformed using Transformers.**
- **Services must not build DTOs manually**; instead, they must use Transformers directly.
- **EntityExtractionService must use Transformers** for the entire transformation process.
- Transformers use `toDto()` and `toModel()` methods.

### API & Service Integration

- **All API requests must go through the Advanced API Client.**
- No direct API calls in controllers, services, or jobs.
- Use Laravel’s HTTP client instead of curl or Guzzle.
- **All transformations must go through Transformers.**
- **API responses and errors must be logged separately** for debugging.
- **Upserts must use repository methods** instead of `updateOrCreate`.

### Filament Rules

- **Filament resources must respect proper panel separation and namespaces.**
- **Resource Generation (via commands):**
    - Must use Filament internal traits (`CanReadModelSchemas`, etc.).
    - No reflection for relationship detection.
    - Separate form and table generators by field type.
    - Keep a configurable `$excludedFields` array.
    - Enums detected via `$casts` and `enum_exists()`.
    - Add docblocks above `form()`, `table()`, `getRelations()` with relationships/fields.
    - Use `copyStubToApp()` instead of inline string replacements.
    - **Preserve the exact method signatures** for Filament resource methods.
    - **Use the correct `Action::make()` syntax** with fluent methods.
    - **Do not display raw `created_at` or `updated_at`** in tables/infolists; use dedicated timestamp columns.

### Testing Rules

- **Unit Tests must follow these rules:**
    - Test functions must be prefixed with `it_`.
    - No `@test` annotations.
    - Prefer Fakes and Fixtures over Mocks.
    - Place happy paths last in test cases.
    - Reusable logic (e.g., fixtures, setup) must live in abstract test cases, not inline.
    - Tests have inline comment blocks above sections (Arrange, Act, Assert).

### Database & Models

- **No `$fillable` array in Models.**
- **No `timestamps()` or `softDeletes()` in Migrations** unless explicitly specified.
- **No `timestamps` or `softDeletes` properties/traits in Models** unless explicitly specified.
- **Use native PHP type hints** and utilize `$casts` for Enum fields.

### Seeding Rules

- Seed 5 default roles (`superadmin`, `admin`, `assistance`, `useradmin`, `user`).
- Ensure users can belong to accounts when relevant.
- Admin Panel access restricted to `admin` and `superadmin`.
