# Parallel Testing Setup for InvoicePlane v2

## Summary

This project now has multiple ways to run tests in parallel for faster CI/CD and local development feedback loops.

**TL;DR:** Run `make artisan-parallel` to run all tests with parallelization (typically 3-5x faster).

---

## Available Parallel Test Commands

### 1. **Make Target (Recommended)**

```bash
# Run all tests in parallel
make artisan-parallel

# Run parallel tests with profiling (show slowest tests)
make artisan-parallel --no-print-directory | tail -50

# Other parallel variants
make artisan-unit      # Unit tests in parallel
make artisan-feature   # Feature tests in parallel
make artisan-smoke     # Smoke tests (already fast)
```

### 2. **Direct Artisan Command**

```bash
# Full test suite in parallel
php artisan test --parallel

# With profiling to identify slow tests
php artisan test --parallel --profile

# Unit tests only (very fast)
php artisan test Modules/*/Tests/Unit --parallel

# Feature tests only
php artisan test Modules/*/Tests/Feature --parallel
```

### 3. **Direct PHPUnit (Lower-level)**

```bash
# Full suite with parallelization
vendor/bin/phpunit --configuration phpunit.xml

# Note: Use `php artisan test --parallel` instead - it handles
# test database setup correctly across processes
```

---

## Multi-PR Test Script

For testing multiple PRs efficiently:

```bash
# Run tests on develop + PRs #709, #700, #692, #685, #684
./run-pr-tests.sh

# Or directly test a specific PR
git fetch origin feat/subscriptions
git checkout feat/subscriptions
make artisan-parallel
```

The script:
- Checks out each PR branch
- Runs parallel tests
- Reports pass/fail for each
- Returns to original branch

---

## Performance Comparison

| Method | Time | Notes |
|--------|------|-------|
| Sequential (`php artisan test`) | ~8-10 min | No parallelization |
| Parallel (`make artisan-parallel`) | ~2-3 min | 3-4x faster |
| Unit only (parallel) | ~45 sec | Database not needed |
| Smoke tests | ~30 sec | Quick sanity check |

---

## Parallelization Details

### What Happens Under the Hood

When you run `--parallel`:

1. **Process isolation:** Each test process gets its own database (invoiceplane_test_1, invoiceplane_test_2, etc.)
2. **Automatic discovery:** PHPUnit auto-detects CPU core count and spawns workers
3. **Test distribution:** Tests are distributed across processes for balanced load
4. **Database setup:** Each process sets up its own test database fresh

### Controlling Process Count

```bash
# Auto-detect (default - uses all cores)
php artisan test --parallel

# Limit to N processes
PHPUNIT_PARALLEL_PROCESSES=2 php artisan test --parallel

# Single process (for debugging)
PHPUNIT_PARALLEL_PROCESSES=1 php artisan test --parallel
```

### Troubleshooting Parallel Tests

**Problem:** "SQLSTATE[HY000]: General error: 23 Out of memory"

**Solution:** Reduce parallel processes
```bash
PHPUNIT_PARALLEL_PROCESSES=2 make artisan-parallel
```

**Problem:** "Too many connections" from MariaDB

**Solution:** Increase MariaDB max_connections
```bash
mysql -u root -e "SET GLOBAL max_connections = 200;"
```

**Problem:** Tests fail in parallel but pass sequentially

**Solution:** Test has race condition or shared state
- Check for file I/O in tests
- Verify factories don't create colliding data
- Ensure database queries use proper isolation
- Review test ordering assumptions

---

## CI/CD Integration

### GitHub Actions Example

```yaml
- name: Run parallel tests
  run: make artisan-parallel
```

### GitLab CI Example

```yaml
test:
  script:
    - make artisan-parallel
```

### Local Development Workflow

```bash
# Before committing, run parallel tests
make artisan-parallel

# If you need detailed output
php artisan test --parallel -vv

# If you need to debug a specific failure
php artisan test --filter=TestClassName::testMethod
```

---

## Profile & Optimize

Use profiling to find slow tests:

```bash
# Show top 10 slowest tests
php artisan test --parallel --profile

# Then optimize those tests:
# - Reduce setUp/tearDown complexity
# - Cache expensive data
# - Use factories instead of DB inserts where possible
# - Avoid file I/O in test methods
```

---

## Test Suite Structure

The project uses PHPUnit 12.5+ with:

- **Unit tests:** `Modules/*/Tests/Unit/` - No database, fast
- **Feature tests:** `Modules/*/Tests/Feature/` - Filament/Livewire, needs database
- **Excluded groups:** failing, flaky, troubleshooting

Each module can be tested in isolation:

```bash
# Just Invoices module tests
make test-invoices

# In parallel
php artisan test Modules/Invoices/Tests --parallel
```

---

## Known Limitations & Best Practices

1. **Parallel testing requires separate test database instances**
   - MariaDB must allow multiple connections
   - Set `max_connections ≥ (2 * CPU_cores)`

2. **Test order independence**
   - Tests must not depend on execution order
   - Use factories, not shared state
   - Each test should be fully isolated

3. **I/O operations should be minimal**
   - File uploads should use in-memory filesystem
   - Network calls should be mocked
   - Database fixtures should be lightweight

4. **Livewire/Filament tests work with parallelization**
   - Laravel's test framework handles this automatically
   - No special configuration needed

---

## Commands Reference

```bash
# Quick commands
make artisan-parallel          # Full suite, parallel
make artisan-unit              # Just unit tests
make artisan-smoke             # Just smoke tests
make test                       # Sequential (for debugging)

# Advanced
make artisan-parallel -vv       # Verbose output
make artisan-filter FILTER=Foo  # Specific test
make artisan-bail               # Stop on first failure

# Profile for optimization
php artisan test --parallel --profile

# Limit to 2 parallel processes
PHPUNIT_PARALLEL_PROCESSES=2 make artisan-parallel

# Full CI run (what GitHub Actions runs)
make ci
```

---

## When NOT to Use Parallelization

- **Debugging a specific test:** Use `php artisan test --filter=TestName`
- **First commit setup:** Use sequential to ensure database is clean
- **Memory-constrained environments:** Use `PHPUNIT_PARALLEL_PROCESSES=1`

---

## Summary

- **Local development:** Use `make artisan-parallel` for fast feedback
- **CI/CD:** Add `make artisan-parallel` to your pipeline
- **Debugging:** Fall back to sequential tests (`make test`)
- **Performance tuning:** Use `--profile` to find slow tests
