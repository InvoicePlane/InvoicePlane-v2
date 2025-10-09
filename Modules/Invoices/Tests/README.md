# Peppol Integration Test Suite

## 📋 Overview

This comprehensive test suite covers the Peppol electronic invoicing integration, providing thorough testing for all critical components including models, jobs, services, enums, events, listeners, and HTTP clients.

## ✅ Test Coverage

### Newly Created Tests (109 tests)

#### 1. Models (42 tests)
- **PeppolIntegrationTest.php** (20 tests)
  - Configuration management (set/get/update)
  - API token encryption and decryption
  - Relationship testing (company, transmissions, configurations)
  - Status checks (isReady, isConnectionSuccessful)
  - Casting and type safety
  
- **PeppolTransmissionTest.php** (22 tests)
  - Relationship testing (invoice, customer, integration, responses)
  - Status enum casting
  - Datetime field management
  - Provider response handling
  - File path storage
  - Error information tracking
  - Idempotency key uniqueness

#### 2. Jobs (26 tests)
- **PeppolStatusPollerTest.php** (13 tests)
  - Polling logic for sent transmissions
  - Status updates (accepted, rejected, failed)
  - Batch processing (100 max per run)
  - Grace period handling
  - Error recovery
  - Provider response storage
  
- **RetryFailedTransmissionsTest.php** (13 tests)
  - Retry scheduling and timing
  - Exponential backoff logic
  - Dead letter queue handling
  - Max attempts enforcement
  - Batch processing (50 max per run)
  - Job dispatch verification

#### 3. Services (16 tests)
- **PeppolManagementServiceTest.php** (16 tests)
  - Integration creation with configuration
  - API token encryption handling
  - Connection testing (success/failure)
  - Peppol ID validation
  - Integration enable/disable
  - Invoice sending orchestration
  - Error handling and rollback

#### 4. Enums (10 tests)
- **PeppolTransmissionStatusTest.php** (10 tests)
  - Enum case validation (all 9 statuses)
  - Label/color/icon mappings
  - Value conversions (from/tryFrom)
  - String value verification
  - Options array generation

#### 5. Events (9 tests)
- **PeppolEventsTest.php** (9 tests)
  - Event structure validation
  - Payload correctness
  - Event name generation
  - Serialization support
  - Interface compliance

#### 6. Listeners (6 tests)
- **LogPeppolEventToAuditTest.php** (6 tests)
  - Audit log creation
  - Event type detection
  - JSON payload storage
  - Multiple event logging
  - Timestamp tracking

### Existing Tests (~50 tests)
- **SendInvoiceToPeppolActionTest.php** - Invoice sending coordination
- **ApiClientTest.php** - HTTP client functionality
- **HttpClientExceptionHandlerTest.php** - Error handling and retries
- **DocumentsClientTest.php** - Peppol document operations
- **PeppolServiceTest.php** - Provider interaction tests
- **PeppolDocumentFormatTest.php** - Format validation
- Various Feature tests for exports/imports

### Total: ~159 tests

## 🧪 Test Patterns & Best Practices

### 1. Arrange-Act-Assert (AAA)
```php
#[Test]
public function it_creates_a_new_peppol_integration(): void
{
    // Arrange
    $config = ['api_endpoint' => 'https://api.example.com'];

    // Act
    $integration = $this->service->createIntegration(
        $this->company->id,
        'e_invoice_be',
        $config
    );

    // Assert
    $this->assertInstanceOf(PeppolIntegration::class, $integration);
    $this->assertEquals('e_invoice_be', $integration->provider_name);
}
```

### 2. Factory Usage
```php
$integration = PeppolIntegration::factory()->create([
    'company_id' => $this->company->id,
]);
```

### 3. Mocking External Dependencies
```php
$providerMock = Mockery::mock(ProviderInterface::class);
$providerMock->shouldReceive('testConnection')
    ->once()
    ->andReturn(['ok' => true]);

ProviderFactory::shouldReceive('make')
    ->once()
    ->andReturn($providerMock);
```

### 4. Event Faking
```php
Event::fake();
// ... trigger event
Event::assertDispatched(PeppolIntegrationCreated::class);
```

### 5. Database Assertions
```php
$this->assertDatabaseHas('peppol_integrations', [
    'company_id' => $this->company->id,
    'enabled' => true,
]);
```

## 🚀 Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Unit Tests Only
```bash
php artisan test --testsuite=Unit
```

### Run Specific Module Tests
```bash
php artisan test Modules/Invoices/Tests
```

### Run Specific Test File
```bash
php artisan test Modules/Invoices/Tests/Unit/Models/PeppolIntegrationTest.php
```

### Run Specific Test Method
```bash
php artisan test --filter=it_creates_a_new_peppol_integration
```

### Run With Coverage
```bash
php artisan test --coverage
php artisan test --coverage --min=80
```

### Parallel Execution
```bash
php artisan test --parallel
```

### Stop on First Failure
```bash
php artisan test --stop-on-failure
```

### Verbose Output
```bash
php artisan test --verbose
```

## 📊 Coverage Areas

### ✅ Happy Paths
- Successful integration creation and configuration
- Successful connection testing
- Successful transmission sending and polling
- Successful Peppol ID validation
- Event dispatching and audit logging

### ✅ Edge Cases
- Null values in optional fields
- Empty configuration arrays
- Missing external IDs
- Already acknowledged transmissions
- Already processed transmissions
- Duplicate idempotency keys

### ✅ Error Conditions
- Failed API connections
- Invalid credentials
- Network timeouts
- Max retry attempts exceeded
- Database constraint violations
- Transaction rollbacks
- Exception handling in async operations

### ✅ Concurrency & State
- Multiple simultaneous transmissions
- Batch processing limits (50-100 per run)
- Grace periods for polling
- Transaction isolation
- Event ordering and serialization

## 📁 Test File Structure