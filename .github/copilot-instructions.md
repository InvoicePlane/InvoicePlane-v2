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
- **Queue System:** Required for export functionality (Redis, database, or sync for local development)

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

# Start queue worker for export functionality
php artisan queue:work
```

**Queue Configuration:**
- Export functionality requires a queue worker to be running
- For local development, you can use `QUEUE_CONNECTION=sync` in `.env`
- For production, use Redis or database queue driver with Supervisor

**GitHub Actions Automation:**
- Automated dependency updates require `PAT_TOKEN` secret (Personal Access Token with `repo` and `workflow` scopes)
- Set up at: Settings → Secrets and variables → Actions
- See `.github/workflows/README.md` for detailed setup instructions

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
 - Test functions must be prefixed with `it_` and make grammatical sense (e.g., `it_creates_payment`, `it_validates_invoice_has_customer`).
 - Use `#[Test]` attribute instead of `@test` annotations.
 - Prefer Fakes and Fixtures over Mocks.
 - Place happy paths last in test cases.
 - Reusable logic (e.g., fixtures, setup) must live in abstract test cases, not inline.
 - Tests have inline comment blocks above sections (/* Arrange */, /* Act */, /* Assert */).
 - **CRITICAL:** All tests MUST have an "act" section where variables are defined BEFORE assertions.
 - Tests must be meaningful - avoid simple "ok" checks; validate actual behavior and data.
 - Use data providers for testing multiple scenarios with the same logic.
 - **NEVER extend `Tests\TestCase`** - all tests must extend one of the abstract test cases from `Modules/Core/Tests/`:
   - `AbstractTestCase` - Basic test case with application bootstrap
   - `AbstractAdminPanelTestCase` - For admin panel tests with RefreshDatabase
   - `AbstractCompanyPanelTestCase` - For company panel tests with multi-tenancy

**Test Structure Example:**
```php
#[Test]
public function it_creates_invoice(): void
{
    /* Arrange */
    $data = ['number' => 'INV-001', 'total' => 100.00];
    
    /* Act */
    $invoice = $this->service->createInvoice($data);  // ❗ Define variable here
    
    /* Assert */
    $this->assertInstanceOf(Invoice::class, $invoice);  // Use it here
}
```

### Export System Rules

- **Exports use Filament's asynchronous export system** which requires queue workers.
- **Export tests must use fakes:** `Queue::fake()`, `Storage::fake()`, and verify job dispatching with `Bus::assertChained()`.
- **The `exports` table is temporary** and managed by Filament for job coordination only.
- **No export history feature** - export records are ephemeral and auto-prunable.
- **Queue configuration is required** for export functionality to work in production.
- See `Modules/Core/Filament/Exporters/README.md` for export architecture details.

### Database & Models

- **No `$fillable` array in Models.**
- **No `timestamps()` or `softDeletes()` in Migrations** unless explicitly specified.
- **No `timestamps` or `softDeletes` properties/traits in Models** unless explicitly specified.
- **Use native PHP type hints** and utilize `$casts` for Enum fields.

### Peppol Integration Rules

- **Peppol service follows Strategy Pattern** for format handlers with 11 supported formats.
- **PeppolService coordinates** invoice transformation and transmission.
- **PeppolManagementService handles** integration lifecycle (create, test, validate, send).
- **Format handlers** must implement validation, transformation, and format-specific logic.
- **Provider Factory** creates provider-specific clients (e.g., EInvoiceBe).
- **All API calls** must go through the ApiClient with exception handling.
- **Logging** is done via LogsApiRequests and LogsPeppolActivity traits.
- **Events** are dispatched for all major Peppol operations (transmission, validation, etc.).

**All 11 Supported Peppol Format Handlers:**
1. **CII** - Cross Industry Invoice (UN/CEFACT standard for Germany/France/Austria)
2. **EHF 3.0** - Norwegian e-invoice format (Elektronisk Handelsformat)
3. **Factur-X** - French/German hybrid (PDF with embedded XML)
4. **Facturae 3.2** - Spanish format (mandatory for public administration)
5. **FatturaPA 1.2** - Italian format (mandatory for all invoices)
6. **OIOUBL** - Danish e-invoice format
7. **PEPPOL BIS 3.0** - Default Peppol format (pan-European)
8. **UBL 2.1** - Universal Business Language (most common)
9. **UBL 2.4** - Updated UBL with enhanced features
10. **ZUGFeRD 1.0** - German format (PDF with embedded XML)
11. **ZUGFeRD 2.0** - Updated German format (compatible with Factur-X)

Each handler is registered in `FormatHandlerFactory` with comprehensive PHPUnit test coverage.
The factory automatically selects handlers with fallback logic and proper logging.

### Seeding Rules

- Seed 5 default roles (`superadmin`, `admin`, `assistance`, `useradmin`, `user`).
- Ensure users can belong to accounts when relevant.
- Admin Panel access restricted to `admin` and `superadmin`.

### Code Refactoring Principles

- **Extract duplicate code** into private/protected methods following Single Responsibility Principle.
- **Use early returns** to reduce nesting and improve readability.
- **Validate inputs** at the start of methods and abort/throw exceptions early.
- **Extract complex conditions** into well-named methods.
- **Use meaningful method names** that describe what they do.

### Internationalization & Translations

**CRITICAL:** InvoicePlane v2 uses `trans()` for all translations, NOT `__()`.

```php
// ❌ WRONG - Do not use __()
$label = __('ip.invoice_total');

// ✅ CORRECT - Always use trans()
$label = trans('ip.invoice_total');
```

**Translation Key Conventions:**
- Main translation file: `resources/lang/en/ip.php`
- Prefix keys with `ip.` (e.g., `ip.invoice_total`, `ip.payment_method`)
- Use snake_case for key names
- In Blade: Use `{{ trans('ip.key') }}` or `@lang('ip.key')`

**UI Text Translation Requirements:**
ALL user-facing text must use trans():
- Form field labels: `->label(trans('ip.field_label'))`
- Placeholders: `->placeholder(trans('ip.placeholder'))`
- Helper text: `->helperText(trans('ip.help_text'))`
- Section titles: `Section::make(trans('ip.section_title'))`
- Button labels: `trans('ip.button_text')`
- Table headers: `trans('ip.column_name')`
- Tooltips & hints: `trans('ip.tooltip')`
- Success/error messages: `trans('ip.message')`

**Example:**
```php
TextInput::make('name')
    ->label(trans('ip.report_block_name'))
    ->placeholder(trans('ip.report_block_name_placeholder'))
    ->helperText(trans('ip.report_block_name_help'));

Section::make(trans('ip.section_general'))
    ->schema([...]);
```

## PHPStan Type Safety Guidelines

### Float Array Keys (CRITICAL)
**Never use floats directly as array keys** - they cause PHPStan errors and precision issues:

```php
// ❌ WRONG
$rate = 21.0;
$taxGroups[$rate] = ['base' => 0];

// ✅ CORRECT
$rate = 21.0;
$rateKey = (string) $rate;
$taxGroups[$rateKey] = ['base' => 0];

// When iterating, cast back to float
foreach ($taxGroups as $rateKey => $group) {
    $rate = (float) $rateKey;
    // Use $rate for calculations
}
```

### DTO Constructor Usage
**Always use static factory methods**, never call DTO constructors with parameters:

```php
// ❌ WRONG
$dto = new GridPositionDTO(0, 0, 6, 4);

// ✅ CORRECT
$dto = GridPositionDTO::create(0, 0, 6, 4);
```

### Property Type Consistency
**Match parent class property types exactly**:

```php
// ❌ WRONG - Parent expects string
protected static ?string $navigationGroup = 'Reports';

// ✅ CORRECT
protected static string $navigationGroup = 'Reports';
```

### Import Statements
**Always use full namespace imports**:

```php
// ❌ WRONG
use Log;

// ✅ CORRECT
use Illuminate\Support\Facades\Log;
```

### Test Mocks
**Use PHPStan suppressions for test mocks with type mismatches**:

```php
$customer = new stdClass();
/** @phpstan-ignore-next-line */
$invoice->customer = $customer;
```

### Factory Return Types
**Add type hints when factories return Collection but method expects Model**:

```php
protected function createCompany(): Company
{
    /** @var Company $company */
    $company = Company::factory()->create();
    return $company;
}
```
