# Junie AI Agent Guidelines for InvoicePlane v2

This document provides comprehensive guidelines for AI agents (like Junie) working on the InvoicePlane v2 codebase to ensure maximum information accuracy and performance.

---

## Project Overview

**InvoicePlane v2** is a multi-tenant invoicing and billing application built with modern PHP/Laravel technologies.

### Core Architecture
- **Framework:** Laravel 12+ (PHP 8.2+)
- **UI:** Filament 4.0 (Admin/Company/Invoice panels)
- **Frontend:** Livewire + Tailwind CSS
- **Module System:** nwidart/laravel-modules (modular monolith)
- **Multi-tenancy:** Filament Companies with `BelongsToCompany` trait
- **Permissions:** spatie/laravel-permission
- **Queue System:** Required for export functionality

### Module Structure
```
Modules/
 ModuleName/
 Models/ # Eloquent models
 Services/ # Business logic layer
 Repositories/ # Data access layer
 DTOs/ # Data Transfer Objects
 Transformers/ # DTO ↔ Model transformations
 Filament/ # Filament resources (Admin/Company panels)
 Tests/ # PHPUnit tests
 Database/ # Migrations, seeders, factories
```

---

## Critical Principles (MUST FOLLOW)

### 1. SOLID Principles
- **Single Responsibility:** Each class has one clear purpose
- **Open/Closed:** Extend behavior without modifying existing code
- **Liskov Substitution:** Subtypes must be substitutable for base types
- **Interface Segregation:** No fat interfaces; clients shouldn't depend on unused methods
- **Dependency Inversion:** Depend on abstractions, not concretions

### 2. Code Quality Standards
- **Early Returns:** Prefer early returns over nested conditions
- **No Inline Logic:** Business logic must be in services, not controllers/resources
- **Dynamic Programming:** Apply where relevant (memoization, tabulation)
- **Centralize Shared Logic:** Use traits to avoid duplication
- **Type Safety:** Use native PHP type hints throughout

### 3. Error Handling
```php
// Catch specific exceptions separately
try {
 // code
} catch (Error $e) {
 // Handle Error
} catch (ErrorException $e) {
 // Handle ErrorException
} catch (Throwable $e) {
 // Handle other throwables
}
```

---

## Architecture Patterns

### DTO & Transformer Rules

**DTOs (Data Transfer Objects):**
- NO constructors in DTOs
- Use static named constructors when necessary
- Rely on getters and setters for data access
- DTOs are transformed using Transformers

**Transformers:**
- Must implement `toDto()` and `toModel()` methods
- Services must use Transformers directly (not build DTOs manually)
- EntityExtractionService must use Transformers for entire transformation process

**Example:**
```php
// DTO
class InvoiceDTO
{
 private string $number;
 private float $total;

 // No constructor!

 public static function fromArray(array $data): self
 {
 $dto = new self();
 $dto->setNumber($data['number']);
 $dto->setTotal($data['total']);
 return $dto;
 }

 public function getNumber(): string { return $this->number; }
 public function setNumber(string $number): void { $this->number = $number; }
}

// Transformer
class InvoiceTransformer
{
 public function toDto(Invoice $model): InvoiceDTO
 {
 return InvoiceDTO::fromArray([
 'number' => $model->number,
 'total' => $model->total,
 ]);
 }

 public function toModel(InvoiceDTO $dto): Invoice
 {
 $model = new Invoice();
 $model->number = $dto->getNumber();
 $model->total = $dto->getTotal();
 return $model;
 }
}
```

### Service Layer
- All business logic must be in services
- Services coordinate between repositories, transformers, and external systems
- Services must not build DTOs manually—use Transformers
- Services return DTOs or collections of DTOs

### Repository Layer
- Repositories handle data access only
- Use repository methods for upserts (not `updateOrCreate`)
- Repositories return models or collections of models

### API Integration
- All API requests must go through the Advanced API Client
- No direct API calls in controllers, services, or jobs
- Use Laravel's HTTP client (not curl or Guzzle)
- All transformations must go through Transformers
- API responses and errors must be logged separately

---

## Testing Standards

### Test Structure
```php
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;

class InvoiceServiceTest extends AbstractCompanyPanelTestCase
{
 use RefreshDatabase;

 #[Test]
 #[Group('invoices')]
 public function it_creates_invoice_with_valid_data(): void
 {
 /* Arrange */
 $data = ['number' => 'INV-001', 'total' => 100.00];

 /* Act */
 $result = $this->service->createInvoice($data);

 /* Assert */
 $this->assertInstanceOf(InvoiceDTO::class, $result);
 $this->assertEquals('INV-001', $result->getNumber());
 }
}
```

### Testing Rules (MANDATORY)
1. **Test Naming:** Functions prefixed with `it_` (e.g., `it_creates_invoice`)
2. **No `@test` Annotations:** Use `#[Test]` attribute instead
3. **Prefer Fakes over Mocks:**
 ```php
 Queue::fake();
 Storage::fake('local');
 Notification::fake();
 ```
4. **Happy Paths Last:** Place success scenarios at the end
5. **Reusable Setup:** Abstract test cases for fixtures, not inline
6. **Comment Blocks:** Use `/* Arrange */`, `/* Act */`, `/* Assert */`

### Export Testing
```php
#[Test]
#[Group('export')]
public function it_dispatches_csv_export_job(): void
{
 /* Arrange */
 Queue::fake();
 Storage::fake('local');
 $records = Model::factory()->count(3)->create();

 /* Act */
 Livewire::actingAs($this->user)
 ->test(ListPage::class)
 ->callAction('exportCsv', data: [
 'columnMap' => [
 'field' => ['isEnabled' => true, 'label' => 'Label'],
 ],
 ]);

 /* Assert */
 Bus::assertChained([
 fn($batch) => $batch instanceof \Illuminate\Bus\PendingBatch
 ]);
}
```

---

## Database & Models

### Migration Rules
- NO JSON columns in migrations
- NO ENUM columns in migrations
- NO `timestamps()` unless explicitly specified
- NO `softDeletes()` unless explicitly specified

### Model Rules
- NO `$fillable` array in models
- NO `timestamps` or `softDeletes` properties unless needed
- Use native PHP type hints
- Use `$casts` for Enum fields

```php
class Invoice extends Model
{
 // No $fillable!

 protected $casts = [
 'status' => InvoiceStatus::class, // Enum
 'total' => 'decimal:2',
 'issued_at' => 'datetime',
 ];
}
```

---

## Filament Resources

### Resource Generation
- Must use Filament internal traits (`CanReadModelSchemas`, etc.)
- No reflection for relationship detection
- Separate form and table generators by field type
- Keep configurable `$excludedFields` array
- Detect Enums via `$casts` and `enum_exists()`
- Add docblocks above `form()`, `table()`, `getRelations()`
- Use `copyStubToApp()` instead of inline string replacements

### Panel Separation
- Respect proper panel namespaces (Admin/Company/Invoice)
- Resources in correct panel directories
- Preserve exact method signatures

### Best Practices
- Use correct `Action::make()` syntax with fluent methods
- Don't display raw `created_at` or `updated_at` in tables/infolists
- Use dedicated timestamp columns instead

---

## Export System

### Architecture
- Exports use Filament's asynchronous export system
- **Requires queue workers** to be running
- The `exports` table is temporary (job coordination only)
- NO export history feature
- Auto-prunable via Laravel's model pruning

### Queue Configuration

**Local Development:**
```bash
# Option 1: Sync driver (blocks request)
QUEUE_CONNECTION=sync

# Option 2: Queue worker
php artisan queue:work
```

**Production:**
```bash
# Redis (recommended)
QUEUE_CONNECTION=redis

# With Supervisor
[program:invoiceplane-worker]
command=php /path/to/artisan queue:work --sleep=3 --tries=3
```

### Export Test Requirements
- Must use `Queue::fake()` and `Storage::fake()`
- Verify job dispatching with `Bus::assertChained()`
- Don't test file content (test job dispatch only)
- See: `Modules/Core/Filament/Exporters/README.md`

---

## Peppol E-Invoicing Integration

### Architecture Overview
InvoicePlane v2 includes a comprehensive Peppol integration for sending electronic invoices across the European Peppol network.

**Key Components:**
- **PeppolService:** Main facade for invoice transmission and status checking
- **PeppolManagementService:** Integration lifecycle management (create, test, validate, send)
- **Format Handlers:** Strategy Pattern for different e-invoice formats (UBL, FatturaPA, ZUGFeRD, etc.)
- **Provider Factory:** Creates provider-specific clients (e.g., EInvoiceBe)
- **API Client:** Centralized HTTP client with exception handling
- **Event System:** Dispatches events for all major operations

### Format Handlers (Strategy Pattern)
Each format handler implements:
- `validate(Invoice $invoice): array` - Validates invoice for format requirements
- `transform(Invoice $invoice, array $options): array` - Converts to format-specific structure
- `getFormat(): PeppolDocumentFormat` - Returns format enum

**Supported Formats:**
- UBL 2.1 (Universal Business Language)
- FatturaPA (Italian e-invoicing)
- ZUGFeRD (German hybrid PDF/XML format)
- Peppol BIS Billing 3.0

### Service Layer Pattern
```php
// PeppolService - Transmission & Status
$peppolService->sendInvoiceToPeppol($invoice, $options);
$peppolService->getDocumentStatus($documentId);
$peppolService->cancelDocument($documentId);

// PeppolManagementService - Lifecycle
$service->createIntegration($companyId, $provider, $config, $token);
$service->testConnection($integration);
$service->validatePeppolId($customer, $integration);
$service->sendInvoice($invoice, $integration);
```

### Logging & Monitoring
- **LogsApiRequests trait:** Logs all API requests/responses
- **LogsPeppolActivity trait:** Logs Peppol-specific events
- **Events:** PeppolTransmissionCreated, PeppolTransmissionSent, etc.
- **Status Tracking:** Comprehensive enum-based status system

### Database Structure
- `peppol_integrations` - Company provider configurations
- `peppol_integration_config` - Key-value config storage
- `peppol_transmissions` - Transmission tracking
- `peppol_transmission_responses` - Provider responses
- `customer_peppol_validation_history` - Validation records

### Testing Peppol Components
```php
#[Test]
public function it_sends_invoice_to_peppol_successfully(): void
{
 /* Arrange */
 Http::fake(['https://api.e-invoice.be/*' => Http::response([
 'document_id' => 'DOC-123456',
 'status' => 'submitted',
 ], 200)]);

 $invoice = $this->createMockInvoice();

 /* Act */
 $result = $this->service->sendInvoiceToPeppol($invoice);

 /* Assert */
 $this->assertTrue($result['success']);
 $this->assertEquals('DOC-123456', $result['document_id']);
}
```

---

## Security & Permissions

### Seeding Rules
- Seed 5 default roles: `superadmin`, `admin`, `assistance`, `useradmin`, `user`
- Users can belong to accounts (multi-tenancy)
- Admin Panel access restricted to `admin` and `superadmin`

### Multi-tenancy
- Use `BelongsToCompany` trait on models
- Company context required for all user operations
- Filament panels enforce tenant isolation

---

## Development Workflow

### Commands

**Testing:**
```bash
php artisan test # All tests
php artisan test --coverage # With coverage
php artisan test --testsuite=Unit # Unit tests only
php artisan test --group=export # Export tests only
```

**Code Quality:**
```bash
vendor/bin/pint # Format code (PSR-12)
vendor/bin/phpstan analyse # Static analysis
vendor/bin/rector process --dry-run # Refactoring suggestions
```

**Setup:**
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan queue:work # For exports
```

### Git Commit Conventions
- Follow conventions in `.github/git-commit-instructions.md`
- Use semantic commit messages
- Reference issues when applicable

---

## Documentation References

### Key Documentation Files
- **Installation:** `.github/INSTALLATION.md`
- **Contributing:** `.github/CONTRIBUTING.md`
- **Testing:** Module tests in `Modules/*/Tests/`
- **Seeding:** `.github/SEEDING.md`
- **Commits:** `.github/git-commit-instructions.md`
- **Export Architecture:** `Modules/Core/Filament/Exporters/README.md`
- **Module Checklist:** `CHECKLIST.md`

### Related Documentation
- Laravel 12: https://laravel.com/docs/12.x
- Filament 4: https://filamentphp.com/docs/4.x
- Livewire 3: https://livewire.laravel.com/docs
- PHPUnit 11: https://docs.phpunit.de/en/11.0/

---

## Performance Optimization

### Query Optimization
- Use eager loading to prevent N+1 queries
- Index foreign keys and frequently queried columns
- Use `select()` to limit columns when possible
- Chunk large datasets for processing

### Caching Strategy
- Cache expensive computations
- Use Redis for session and cache storage
- Implement query result caching where appropriate

### Queue Workers
- Use multiple workers for high-volume operations
- Configure max execution time appropriately
- Monitor failed jobs and retry logic

---

## Common Pitfalls to Avoid

1. Don't use `$fillable` in models
2. Don't create DTOs with constructors
3. Don't build DTOs manually in services—use Transformers
4. Don't use JSON or ENUM columns in migrations
5. Don't add timestamps/softDeletes unless specified
6. Don't test export file content—test job dispatching
7. Don't make direct API calls—use Advanced API Client
8. Don't use `updateOrCreate`—use repository upsert methods
9. Don't nest conditions deeply—use early returns
10. Don't duplicate logic—centralize in traits

---

## Code Review Checklist

Before submitting code, verify:

- [ ] Follows SOLID principles
- [ ] No inline business logic (in services)
- [ ] DTOs use static constructors, not `__construct()`
- [ ] Transformers used for DTO ↔ Model conversions
- [ ] Tests use `it_` prefix and `#[Test]` attribute
- [ ] Tests have Arrange/Act/Assert comments
- [ ] No `$fillable` in models
- [ ] No JSON/ENUM in migrations
- [ ] Type hints used throughout
- [ ] Early returns instead of nested conditions
- [ ] Fakes used instead of mocks in tests
- [ ] Export tests use Queue/Storage fakes
- [ ] Code formatted with `vendor/bin/pint`
- [ ] Static analysis passes (`vendor/bin/phpstan`)
- [ ] All tests pass (`php artisan test`)
- [ ] Documentation updated if needed

---

## Learning Resources

### InvoicePlane-Specific
- Review existing modules for patterns
- Check test files for examples
- Read module-specific README files
- Follow CHECKLIST.md for feature status

### Laravel/PHP
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [PHP: The Right Way](https://phptherightway.com/)
- [SOLID Principles in PHP](https://solidprinciples.dev/)

### Filament
- [Filament Tricks](https://filamentphp.com/tricks)
- [Filament Community](https://github.com/filamentphp)

---

## Continuous Improvement

This document should be updated as:
- New patterns emerge
- Architecture decisions change
- Best practices evolve
- Performance optimizations discovered

**Last Updated:** 2025-11-13

---

## Support

- **Discord:** https://discord.gg/PPzD2hTrXt
- **Forums:** https://community.invoiceplane.com
- **Issues:** https://github.com/InvoicePlane/InvoicePlane/issues
- **Wiki:** https://wiki.invoiceplane.com

---

**Remember:** These guidelines ensure consistency, maintainability, and performance across the InvoicePlane v2 codebase. When in doubt, refer to existing code that follows these patterns, and always prioritize code quality over speed of delivery.
