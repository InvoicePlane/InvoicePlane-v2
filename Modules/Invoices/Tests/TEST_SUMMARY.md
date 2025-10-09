# Peppol Integration Test Suite

## Overview
This test suite provides comprehensive coverage for the Peppol integration feature, covering models, jobs, services, enums, events, listeners, and clients.

## Test Coverage Summary

### Models (Unit Tests)
- **PeppolIntegrationTest** - 20 tests
  - Configuration management
  - API token encryption/decryption
  - Relationship testing
  - Status checks (isReady, isConnectionSuccessful)
  
- **PeppolTransmissionTest** - 22 tests
  - Relationship testing
  - Status transitions
  - Provider response handling
  - Timestamp management

### Jobs (Unit Tests)
- **PeppolStatusPollerTest** - 13 tests
  - Polling logic for sent transmissions
  - Status updates (accepted, rejected)
  - Batch processing
  - Error handling

- **RetryFailedTransmissionsTest** - 13 tests
  - Retry scheduling
  - Backoff logic
  - Dead letter queue handling
  - Max attempts enforcement

### Services (Unit Tests)
- **PeppolServiceTest** (existing) - Provider interaction tests
- **PeppolManagementServiceTest** - 16 tests
  - Integration creation/management
  - Connection testing
  - Peppol ID validation
  - Invoice sending orchestration

### Enums (Unit Tests)
- **PeppolDocumentFormatTest** (existing) - Format validation
- **PeppolTransmissionStatusTest** - 10 tests
  - Enum cases validation
  - Label/color/icon mappings
  - Value conversions

### Events (Unit Tests)
- **PeppolEventsTest** - 9 tests
  - Event structure validation
  - Payload correctness
  - Serialization support

### Listeners (Unit Tests)
- **LogPeppolEventToAuditTest** - 6 tests
  - Audit log creation
  - Event type detection
  - Error handling

### HTTP Clients (Unit Tests - existing)
- **ApiClientTest** - HTTP client functionality
- **HttpClientExceptionHandlerTest** - Error handling and retries
- **DocumentsClientTest** - Peppol document operations

### Actions (Unit Tests - existing)
- **SendInvoiceToPeppolActionTest** - Invoice sending coordination

## Test Execution

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Unit tests only
php artisan test --testsuite=Unit

# Feature tests only
php artisan test --testsuite=Feature

# Specific module tests
php artisan test Modules/Invoices/Tests
```

### Run With Coverage
```bash
php artisan test --coverage
```

## Test Patterns Used

### 1. Arrange-Act-Assert (AAA)
All tests follow the AAA pattern for clarity:
```php
// Arrange
$integration = PeppolIntegration::factory()->create();

// Act
$result = $service->testConnection($integration);

// Assert
$this->assertTrue($result['ok']);
```

### 2. Factory Usage
Tests use factories for model creation:
```php
$integration = PeppolIntegration::factory()->create([
    'company_id' => $this->company->id,
]);
```

### 3. Mocking External Dependencies
External services are mocked using Mockery:
```php
$providerMock = Mockery::mock(ProviderInterface::class);
$providerMock->shouldReceive('testConnection')
    ->once()
    ->andReturn(['ok' => true]);
```

### 4. Event Faking
Laravel's event faking is used to test events:
```php
Event::fake();
// ... trigger event
Event::assertDispatched(PeppolIntegrationCreated::class);
```

### 5. Database Assertions
Database state is verified after operations:
```php
$this->assertDatabaseHas('peppol_integrations', [
    'company_id' => $this->company->id,
    'enabled' => true,
]);
```

## Best Practices Followed

1. **Descriptive Test Names**: Each test name clearly describes what is being tested
2. **Single Responsibility**: Each test focuses on one specific behavior
3. **Test Isolation**: Tests don't depend on each other and can run in any order
4. **Comprehensive Coverage**: Tests cover happy paths, edge cases, and error conditions
5. **Clean Setup/Teardown**: Setup and teardown are properly handled
6. **Mock Cleanup**: Mockery::close() is called in tearDown()

## Areas Tested

### Happy Paths ✓
- Successful integration creation
- Successful connection testing
- Successful transmission sending
- Successful status polling

### Edge Cases ✓
- Null values in optional fields
- Empty configuration arrays
- Missing external IDs
- Already processed transmissions

### Error Conditions ✓
- Failed API connections
- Invalid credentials
- Network timeouts
- Max retry attempts exceeded
- Database constraint violations

### Concurrency & State ✓
- Multiple simultaneous transmissions
- Batch processing limits
- Transaction rollbacks
- Event ordering

## Future Test Enhancements

1. **Integration Tests**: Add tests that verify end-to-end flows
2. **Performance Tests**: Add tests for batch processing performance
3. **Load Tests**: Verify system behavior under high load
4. **Contract Tests**: Add Pact tests for API interactions
5. **Mutation Testing**: Use infection/infection for mutation testing

## Running Tests in CI/CD

### GitHub Actions Example
```yaml
- name: Run Tests
  run: |
    php artisan test --parallel
    php artisan test --coverage --min=80
```

### GitLab CI Example
```yaml
test:
  script:
    - php artisan test --parallel
    - php artisan test --coverage --min=80
```

## Test Data Management

### Factories
Factories are used to generate test data consistently:
- `PeppolIntegration::factory()`
- `PeppolTransmission::factory()`
- `Invoice::factory()`
- `Company::factory()`

### Seeders
Test seeders can be used for feature tests requiring complex data setups.

## Debugging Tests

### Run Specific Test
```bash
php artisan test --filter=it_creates_a_new_peppol_integration
```

### Stop on Failure
```bash
php artisan test --stop-on-failure
```

### Verbose Output
```bash
php artisan test --verbose
```

## Continuous Improvement

- Tests should be reviewed and updated with code changes
- New features must include corresponding tests
- Test coverage should be maintained above 80%
- Flaky tests should be investigated and fixed immediately