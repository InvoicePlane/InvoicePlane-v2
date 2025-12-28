# Quick Reference: Running PEPPOL Tests

## Prerequisites
```bash
cd /home/jailuser/git
composer install
```

## Run All Tests
```bash
# All tests in the project
./vendor/bin/phpunit

# All Peppol tests only
./vendor/bin/phpunit --group=peppol

# Run with ParaTest (parallel execution)
./vendor/bin/paratest --group=peppol
```

## Run Specific Test Suites

### Enum Tests
```bash
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Enums/
```

### Factory Tests  
```bash
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Peppol/FormatHandlers/FormatHandlerFactoryTest.php
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Peppol/Providers/ProviderFactoryTest.php
```

### Individual Test Files
```bash
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Enums/PeppolConnectionStatusTest.php
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Enums/PeppolErrorTypeTest.php
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Enums/PeppolTransmissionStatusTest.php
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Enums/PeppolValidationStatusTest.php
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Peppol/Enums/PeppolEndpointSchemeTest.php
```

## Run with Filters

### Run specific test method
```bash
./vendor/bin/phpunit --filter it_has_all_expected_cases
./vendor/bin/phpunit --filter it_validates_correct_identifiers
```

### Run tests matching pattern
```bash
./vendor/bin/phpunit --filter "Test.*label"
./vendor/bin/phpunit --filter "Test.*validation"
```

## Coverage Reports

### HTML Coverage Report
```bash
./vendor/bin/phpunit --group=peppol --coverage-html coverage/
open coverage/index.html
```

### Text Coverage Summary
```bash
./vendor/bin/phpunit --group=peppol --coverage-text
```

### Coverage for Specific Directory
```bash
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Enums/ --coverage-text
```

## Debugging Tests

### Stop on Failure
```bash
./vendor/bin/phpunit --stop-on-failure --group=peppol
```

### Verbose Output
```bash
./vendor/bin/phpunit --verbose --group=peppol
```

### Debug Mode
```bash
./vendor/bin/phpunit --debug --group=peppol
```

## Test Results

### Successful Test Output Example