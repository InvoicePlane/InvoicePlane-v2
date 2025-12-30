# InvoicePlane v2

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-12%2B-red.svg)](https://laravel.com)
[![Filament Version](https://img.shields.io/badge/Filament-4.0-orange.svg)](https://filamentphp.com)

**InvoicePlane v2** is a modern, open-source invoicing and billing application built with Laravel 12, Filament 4, and Livewire. It features a modular architecture, multi-tenancy support, and comprehensive Peppol e-invoicing integration for European businesses.

---

## Features

- **Invoice & Quote Management** - Create, send, and track invoices and quotes
- **Peppol E-Invoicing** - Send invoices through the European Peppol network (UBL, FatturaPA, ZUGFeRD)
- **Customer & Contact Handling** - Manage customers and relationships
- **Payment Tracking & Reminders** - Track payments and send automated reminders
- **Modular Architecture** - Laravel + Filament with clean module separation
- **Multi-Tenant Support** - Via Filament Companies with company isolation
- **Realtime UI** - Built with Livewire for reactive interfaces
- **Asynchronous Export System** - Requires queue workers for background processing
- **Comprehensive Testing** - PHPUnit tests with 100% coverage goal
- **Internationalization** - Full translation support via Crowdin

---

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js 20+ and Yarn
- MySQL 8.0+ or PostgreSQL 13+
- Redis (recommended for queue/cache)

---

## Installation

To install and run InvoicePlane v2 locally, see the [Installation Guide](.github/INSTALLATION.md).

### Quick Start

```bash
# Clone the repository
git clone https://github.com/InvoicePlane/InvoicePlane-v2.git
cd InvoicePlane-v2

# Install dependencies
composer install
yarn install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --seed

# Build frontend assets
yarn build

# Start queue worker for export functionality
php artisan queue:work
```

**Note:** Export functionality requires a queue worker to be running. For production, configure a queue driver (Redis, database, etc.) and use a process manager like Supervisor.

For detailed setup instructions, see [INSTALLATION.md](.github/INSTALLATION.md).

---

## Documentation

- **[Installation Guide](.github/INSTALLATION.md)** - Complete setup instructions
- **[Contributing Guide](.github/CONTRIBUTING.md)** - How to contribute code
- **[Testing Guide](RUNNING_TESTS.md)** - Running and writing tests
- **[Maintenance Guide](.github/MAINTENANCE.md)** - Dependency management and security updates
- **[Seeding Guide](.github/SEEDING.md)** - Database seeding instructions
- **[Upgrade Guide](.github/UPGRADE.md)** - Upgrading from previous versions
- **[Security Policy](.github/SECURITY.md)** - Reporting security vulnerabilities
- **[Peppol Architecture](PEPPOL_ARCHITECTURE.md)** - E-invoicing system details
- **[Workflows README](.github/workflows/README.md)** - GitHub Actions automation and secrets setup

---

## Development

### Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
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

### Building Assets

```bash
# Development build
yarn dev

# Production build
yarn build

# Watch for changes
yarn dev --watch
```

---

## Contributing

We welcome community contributions!

To learn how to contribute code, create modules, write tests, or help translate the app:

- Read the [Contributing Guide](.github/CONTRIBUTING.md)
- Follow the [Module Checklist](CHECKLIST.md) to avoid duplication
- Review the [Junie Guidelines](.junie/guidelines.md) for coding standards
- Check [Copilot Instructions](.github/copilot-instructions.md) for AI assistance

---

## Translations

Help translate InvoicePlane v2 into your language using Crowdin:

**[Join Translation Project →](https://translations.invoiceplane.com)**

Current languages:
- English (default)
- Dutch
- German
- Spanish
- French
- And more...

See [TRANSLATIONS.md](.github/TRANSLATIONS.md) for more details.

---

## Support & Community

- **Discord** - [Join our Discord server](https://discord.gg/PPzD2hTrXt)
- **Forums** - [Community discussions](https://community.invoiceplane.com)
- **Issue Tracker** - [Report bugs and request features](https://github.com/InvoicePlane/InvoicePlane-v2/issues)
- **Documentation Wiki** - [Official documentation](https://wiki.invoiceplane.com)

---

## Security

If you discover a security vulnerability, please follow our [Security Policy](.github/SECURITY.md) for responsible disclosure.

**Do not** report security vulnerabilities through public GitHub issues.

---

## License

InvoicePlane v2 is open-source software licensed under the **MIT License**.

The InvoicePlane name and logo are protected trademarks of Kovah.de.

---

## Acknowledgments

Built with:
- [Laravel](https://laravel.com) - The PHP framework
- [Filament](https://filamentphp.com) - Admin panel framework
- [Livewire](https://livewire.laravel.com) - Reactive frontend
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS
- [PHPUnit](https://phpunit.de) - Testing framework

Special thanks to all our [contributors](https://github.com/InvoicePlane/InvoicePlane-v2/graphs/contributors)!

---

**Developed by the InvoicePlane community**
