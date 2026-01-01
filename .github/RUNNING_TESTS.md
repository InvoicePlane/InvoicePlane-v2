# Running Tests in InvoicePlane v2

This guide covers how to run tests in InvoicePlane v2, including full test suites, smoke tests, and specific test groups.

## Prerequisites

```bash
composer install
cp .env.testing.example .env.testing
php artisan key:generate --env=testing
```

## Quick Reference

### Run All Tests
```bash
# Using Laravel Artisan (recommended)
php artisan test

# Using PHPUnit directly
./vendor/bin/phpunit
```

### Run Test Suites
```bash
# Run only Unit tests
php artisan test --testsuite=Unit

# Run only Feature tests
php artisan test --testsuite=Feature
```

### Run Smoke Tests
Smoke tests are fast, critical tests that verify core functionality. They use the `#[Group('smoke')]` attribute.

```bash
# Using PHPUnit with smoke configuration
php artisan test --configuration=phpunit.smoke.xml

# Using --group flag
php artisan test --group=smoke

# Run smoke tests for a specific module
./vendor/bin/phpunit Modules/Clients/Tests/Feature/ --group=smoke
```

### Run Tests with Coverage
```bash
# Generate coverage report
php artisan test --coverage

# Generate HTML coverage report
php artisan test --coverage-html coverage/

# View coverage in browser
open coverage/index.html  # macOS
xdg-open coverage/index.html  # Linux
```

## Test Groups

InvoicePlane v2 uses PHPUnit groups to organize tests:

- `smoke` - Fast, critical tests (runs in ~10-30 seconds)
- `crud` - Create, Read, Update, Delete operation tests
- `peppol` - Peppol e-invoicing integration tests
- `integration` - Integration tests with external services

### Run Specific Groups
```bash
# Smoke tests only
php artisan test --group=smoke

# CRUD tests only
php artisan test --group=crud

# Peppol tests only
php artisan test --group=peppol

# Multiple groups
php artisan test --group=smoke,crud
```

## Module-Specific Tests

### Run Tests for a Specific Module
```bash
# All tests for a module
./vendor/bin/phpunit Modules/Clients/Tests/

# Only Feature tests for a module
./vendor/bin/phpunit Modules/Clients/Tests/Feature/

# Only Unit tests for a module
./vendor/bin/phpunit Modules/Clients/Tests/Unit/
```

### Examples
```bash
# Clients module
./vendor/bin/phpunit Modules/Clients/Tests/

# Invoices module
./vendor/bin/phpunit Modules/Invoices/Tests/

# Peppol-specific tests
./vendor/bin/phpunit Modules/Invoices/Tests/Unit/Peppol/
```

## Individual Test Files

```bash
# Run a specific test file
./vendor/bin/phpunit Modules/Clients/Tests/Feature/ContactsTest.php

# Run a specific test method
./vendor/bin/phpunit --filter it_lists_contacts Modules/Clients/Tests/Feature/ContactsTest.php
```

## Debugging Tests

### Stop on First Failure
```bash
php artisan test --stop-on-failure
```

### Verbose Output
```bash
php artisan test --verbose
```

### Run with Debug Mode
```bash
./vendor/bin/phpunit --debug
```

### Run Specific Test Method
```bash
# Using --filter
php artisan test --filter it_creates_invoice

# Pattern matching
php artisan test --filter ".*creates.*"
```

## Parallel Testing

For faster test execution, ParaTest can be used (if installed in the project):

```bash
# Run tests in parallel (if ParaTest is available)
./vendor/bin/paratest

# Run with specific number of processes
./vendor/bin/paratest --processes=4
```

**Note:** ParaTest should be added to `composer.json` as a dev dependency if parallel testing is needed:

```bash
composer require --dev brianium/paratest
```

## Configuration Files

InvoicePlane v2 uses two PHPUnit configuration files:

- `phpunit.xml` - Default configuration for all tests
- `phpunit.smoke.xml` - Configuration for smoke tests only

### phpunit.xml
- Runs all Unit and Feature test suites
- Uses SQLite in-memory database
- Includes all test directories

### phpunit.smoke.xml
- Filters tests by `#[Group('smoke')]` attribute
- Faster execution (~10-30 seconds)
- Ideal for CI/CD pipelines and post-deployment checks

## Continuous Integration

### GitHub Actions Workflows

Smoke tests run automatically after Composer dependency updates:

```bash
# See .github/workflows/composer-update.yml
```

Full test suite runs manually:

```bash
# See .github/workflows/phpunit.yml
```

## Best Practices

1. **Run smoke tests frequently** - They catch critical issues quickly
2. **Use `--stop-on-failure`** - When debugging to save time
3. **Run full suite before committing** - Ensure no regressions
4. **Use coverage reports** - To identify untested code
5. **Group tests logically** - Use `#[Group()]` for better organization

## Troubleshooting

### Tests Failing Due to Database Issues
```bash
# Ensure .env.testing is configured
cp .env.testing.example .env.testing
php artisan key:generate --env=testing

# Check database connection
php artisan test --env=testing
```

### Memory Limit Issues
```bash
# Increase PHP memory limit
php -d memory_limit=512M artisan test
```

### Cache Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## See Also

- [CONTRIBUTING.md](.github/CONTRIBUTING.md) - Guidelines for contributing tests
- [TEST_GENERATION_SUMMARY.md](.github/TEST_GENERATION_SUMMARY.md) - Test generation documentation
- [PEPPOL_TESTS_SUMMARY.md](.github/PEPPOL_TESTS_SUMMARY.md) - Peppol-specific test documentation