##
## InvoicePlane v2 — Development Makefile
## ──────────────────────────────────────────────────────────────────────────────
##
## QUICK START
##   make test          Run the full PHPUnit suite (all tests)
##   make smoke         Run only @group smoke tests (fast sanity check)
##   make unit          Run only unit tests
##   make feature       Run only feature (Filament/Livewire) tests
##   make artisan-test  Run tests via `php artisan test`
##
## TARGETED RUNS  (by module)
##   make test-core     make test-invoices   make test-quotes
##   make test-products make test-payments   make test-projects
##   make test-clients  make test-expenses
##
## TARGETED RUNS  (by group)
##   make test-group GROUP=smoke|crud|unit|validation|multi-tenancy|...
##
## TARGETED RUNS  (by filter)
##   make filter FILTER="InvoiceModelTest"       exact class name
##   make filter FILTER="InvoiceModelTest::it_"  specific method prefix
##
## COVERAGE
##   make coverage      HTML coverage report → build/coverage/
##   make coverage-text Text-mode summary in terminal
##   make coverage-clover  Clover XML (for CI)
##
## ARTISAN TEST VARIANTS
##   make artisan-test           Full suite via artisan
##   make artisan-smoke          Smoke group via artisan
##   make artisan-filter FILTER= Single test via artisan
##   make artisan-parallel       Parallel test run via artisan
##
## CI / UTILITIES
##   make ci            Full suite in a single command (same as CI pipeline)
##   make clean         Remove PHPUnit cache, coverage, and temp artefacts
##   make help          Print this help text
##
## ENVIRONMENT VARIABLES (all have sane defaults; override on the CLI)
##   PHP      Path to the PHP binary  (default: php)
##   PHPUNIT  Path to PHPUnit binary  (default: vendor/bin/phpunit)
##   CONFIG   PHPUnit XML config file (default: phpunit.xml)
##   FILTER   Test filter passed to --filter
##   GROUP    Test group passed to --group
##   SUITE    Test suite name passed to --testsuite (Unit | Feature)
##   MODULE   Module directory name (e.g. Invoices, Products)
##   STOP     Set to 1 to stop on first failure: make test STOP=1
##
## ──────────────────────────────────────────────────────────────────────────────

# ── Configurable defaults ──────────────────────────────────────────────────────
PHP      ?= php
PHPUNIT  ?= vendor/bin/phpunit
CONFIG   ?= phpunit.xml
FILTER   ?=
GROUP    ?=
SUITE    ?=
MODULE   ?=
STOP     ?=

# ── Internal helpers ───────────────────────────────────────────────────────────
# Build optional flag strings; only include a flag when the variable is set.
_filter   = $(if $(FILTER),--filter "$(FILTER)")
_group    = $(if $(GROUP),--group "$(GROUP)")
_suite    = $(if $(SUITE),--testsuite "$(SUITE)")
_stop     = $(if $(STOP),--stop-on-failure)

# The core PHPUnit invocation (all optional flags appended).
_phpunit  = APP_ENV=testing $(PHPUNIT) --configuration $(CONFIG) \
            --exclude-group failing,flaky,troubleshooting $(_stop) $(_filter) $(_group) $(_suite)

# The core artisan invocation.
_artisan  = APP_ENV=testing $(PHP) artisan test --exclude-group failing,flaky,troubleshooting

.DEFAULT_GOAL := help

.PHONY: help test unit feature smoke ci \
        filter group suite \
        test-core test-invoices test-quotes test-products \
        test-payments test-projects test-clients test-expenses \
        test-group \
        coverage coverage-text coverage-clover \
        artisan-test artisan-smoke artisan-filter artisan-parallel \
        clean

# ── Help ──────────────────────────────────────────────────────────────────────
help:
	@grep -E '^##' $(MAKEFILE_LIST) | sed 's/^## //'

# ── Full suite ────────────────────────────────────────────────────────────────

docker-test:
	docker exec ivpldock-workspace-1 bash -c "cd /var/www/projects/ip2 && DB_HOST=mariadb php artisan test --exclude-group=failing,flaky,troubleshooting"

## ─── Full suite ───────────────────────────────────────────────────────────────
test:
	$(_phpunit)

## Fast test run: excludes slow tests (~5-10 min)
test-fast:
	docker exec ivpldock-workspace-1 bash -c "cd /var/www/projects/ip2 && $(PHPUNIT) --configuration $(CONFIG) --exclude-group failing,flaky,troubleshooting,slow"

## Run only the Unit test suite (Modules/*/Tests/Unit)
unit:
	$(_phpunit) --testsuite Unit

## Run only the Feature test suite (Modules/*/Tests/Feature)
feature:
	$(_phpunit) --testsuite Feature

## Run only @group smoke tests (fast sanity check, uses phpunit.smoke.xml)
smoke:
	APP_ENV=testing $(PHPUNIT) --configuration phpunit.smoke.xml

## ─── Stop on first failure ────────────────────────────────────────────────────

## Full suite, stop on the first failure or error
test-bail:
	$(_phpunit) --stop-on-failure --stop-on-error

## Unit suite, stop on the first failure or error
unit-bail:
	$(_phpunit) --testsuite Unit --stop-on-failure --stop-on-error

## Feature suite, stop on the first failure or error
feature-bail:
	$(_phpunit) --testsuite Feature --stop-on-failure --stop-on-error

# ── Flexible filter / group / suite targets ───────────────────────────────────

## Run tests matching a class or method name  (usage: make filter FILTER=MyTest)
filter:
	$(_phpunit) $(_filter)

## Run tests in a named group               (usage: make group GROUP=smoke)
group:
	$(_phpunit) $(_group)

## Run a specific test suite by name        (usage: make suite SUITE=Unit)
suite:
	$(_phpunit) $(_suite)

# ── Per-module targets ────────────────────────────────────────────────────────

## Run all tests in Modules/Core
test-core:
	$(_phpunit) --filter "Modules\\\\Core\\\\"

## Run all tests in Modules/Invoices
test-invoices:
	$(_phpunit) --filter "Modules\\\\Invoices\\\\"

## Run all tests in Modules/Quotes
test-quotes:
	$(_phpunit) --filter "Modules\\\\Quotes\\\\"

## Run all tests in Modules/Products
test-products:
	$(_phpunit) --filter "Modules\\\\Products\\\\"

## Run all tests in Modules/Payments
test-payments:
	$(_phpunit) --filter "Modules\\\\Payments\\\\"

## Run all tests in Modules/Projects
test-projects:
	$(_phpunit) --filter "Modules\\\\Projects\\\\"

## Run all tests in Modules/Clients
test-clients:
	$(_phpunit) --filter "Modules\\\\Clients\\\\"

## Run all tests in Modules/Expenses
test-expenses:
	$(_phpunit) --filter "Modules\\\\Expenses\\\\"

## Run tests in an arbitrary module          (usage: make test-module MODULE=Invoices)
test-module:
	$(_phpunit) --filter "Modules\\\\$(MODULE)\\\\"

# ── Per-group targets ─────────────────────────────────────────────────────────

## Run @group smoke tests
test-smoke:
	$(_phpunit) --group smoke

## Run @group crud tests
test-crud:
	$(_phpunit) --group crud

## Run @group unit tests
test-unit-group:
	$(_phpunit) --group unit

## Run @group validation tests
test-validation:
	$(_phpunit) --group validation

## Run @group multi-tenancy tests
test-multi-tenancy:
	$(_phpunit) --group multi-tenancy

## Run @group export tests
test-export:
	$(_phpunit) --group export

## Run @group security tests
test-security:
	$(_phpunit) --group security

## Run @group numbering tests
test-numbering:
	$(_phpunit) --group numbering

## Run @group authentication tests
test-authentication:
	$(_phpunit) --group authentication

## Run @group edge-cases tests
test-edge-cases:
	$(_phpunit) --group edge-cases

## Run @group modals tests
test-modals:
	$(_phpunit) --group modals

## Run tests in an arbitrary group           (usage: make test-group GROUP=crud)
test-group:
	$(_phpunit) --group "$(GROUP)"

# ── Exclude groups ─────────────────────────────────────────────────────────────

## Run all tests except the 'failing' group (useful during active development)
#test-no-failing:
#	$(_phpunit) --exclude-group failing

## Run all tests except 'troubleshooting', 'failing', and 'flaky' groups
#test-stable:
#	$(_phpunit) --exclude-group failing,flaky,troubleshooting

# ── Coverage ──────────────────────────────────────────────────────────────────

## Generate HTML coverage report in build/coverage/html/
coverage:
	APP_ENV=testing XDEBUG_MODE=coverage $(PHPUNIT) \
	    --configuration $(CONFIG) \
	    --coverage-html build/coverage/html
	@echo "Coverage report generated: build/coverage/html/index.html"

## Print a text-mode coverage summary to the terminal
coverage-text:
	APP_ENV=testing XDEBUG_MODE=coverage $(PHPUNIT) \
	    --configuration $(CONFIG) \
	    --coverage-text

## Generate Clover XML coverage (for CI / SonarQube / Codecov)
coverage-clover:
	APP_ENV=testing XDEBUG_MODE=coverage $(PHPUNIT) \
	    --configuration $(CONFIG) \
	    --coverage-clover build/coverage/clover.xml
	@echo "Clover report: build/coverage/clover.xml"

## Generate coverage for a single module     (usage: make coverage-module MODULE=Invoices)
coverage-module:
	APP_ENV=testing XDEBUG_MODE=coverage $(PHPUNIT) \
	    --configuration $(CONFIG) \
	    --filter "Modules\\\\$(MODULE)\\\\" \
	    --coverage-text

# ── php artisan test variants ─────────────────────────────────────────────────

## Run the full test suite via php artisan test
artisan-test:
	$(_artisan)

## Run only @group smoke tests via artisan
artisan-smoke:
	$(_artisan) --group smoke

## Run only the Unit suite via artisan
artisan-unit:
	$(_artisan) --testsuite Unit

## Run only the Feature suite via artisan
artisan-feature:
	$(_artisan) --testsuite Feature

## Run a specific test via artisan            (usage: make artisan-filter FILTER=MyTest)
artisan-filter:
	$(_artisan) --filter "$(FILTER)"

## Run tests in parallel via artisan (requires ext-pcntl)
artisan-parallel:
	$(_artisan) --parallel

## Run artisan test with compact output
artisan-pretty:
	$(_artisan) --compact

## Run artisan test and stop on first failure
artisan-bail:
	$(_artisan) --bail

# ── CI / pipeline ─────────────────────────────────────────────────────────────

## Full CI run: complete PHPUnit suite, stop on failure, no caching
ci:
	APP_ENV=testing $(PHPUNIT) \
	    --configuration $(CONFIG) \
	    --exclude-group failing,flaky,troubleshooting \
	    --stop-on-failure \
	    --stop-on-error \
	    --cache-result-file /dev/null

# ── Utilities ─────────────────────────────────────────────────────────────────

## Remove PHPUnit result cache, coverage build artefacts, and temp files
clean:
	rm -f .phpunit.result.cache
	rm -rf build/coverage
	@echo "Cleaned up PHPUnit cache and coverage artefacts."
