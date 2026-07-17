# InvoicePlane v2

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-13%2B-red.svg)](https://laravel.com)
[![Filament Version](https://img.shields.io/badge/Filament-5.x-orange.svg)](https://filamentphp.com)

**InvoicePlane v2** is a modern, open-source invoicing and billing application built with Laravel, Filament, and Livewire. It features a modular architecture and multi-tenancy support. Peppol e-invoicing is a planned feature (see [Peppol E-Invoicing](#-peppol-e-invoicing) below) but is not yet implemented on the `develop` branch.

---

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Quick Start](#-quick-start)
- [Full Installation](#-full-installation)
- [Configuration](#-configuration)
- [Development](#-development)
- [Module Structure](#-module-structure)
- [Testing](#-testing)
- [Peppol E-Invoicing](#-peppol-e-invoicing)
- [Deployment](#-deployment)
- [Documentation](#-documentation)
- [Contributing](#-contributing)
- [Support](#-support)
- [License](#-license)

---

## ✨ Features

- **Invoice & Quote Management** - Create, send, and track invoices and quotes
- **Customer & Contact Handling** - Manage customers and relationships
- **Payment Tracking & Reminders** - Track payments and send automated reminders
- **Modular Architecture** - Laravel + Filament with clean module separation
- **Multi-Tenant Support** - Via Filament Companies with company isolation
- **Realtime UI** - Built with Livewire for reactive interfaces
- **Asynchronous Export System** - Requires queue workers for background processing
- **Comprehensive Testing** - PHPUnit tests with high coverage
- **Internationalization** - Full translation support via Crowdin

---

## 📦 Requirements

- **PHP** 8.3 or higher
- **Composer** 2.x
- **Node.js** 20+ and Yarn
- **Database** MariaDB 10.11+ (recommended), MySQL 8.0+, or SQLite (dev only)
- **Redis** (recommended for queue/cache in production)
- **Queue Worker** (required for export functionality)

---

## 🚀 Quick Start

Get InvoicePlane v2 running in under 5 minutes:

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

# Configure your database in .env, then:
php artisan migrate --seed

# Build frontend assets
yarn build

# Start development server
php artisan serve

# In a separate terminal, start queue worker (required for exports)
php artisan queue:work
```

**Default Login:**
- Admin Panel: `http://localhost:8000/admin`
- Company Panel: `http://localhost:8000/{search_code}` (tenant-scoped root path, e.g. `http://localhost:8000/ivplv2` for the seeded default company)
- Email: `admin@invoiceplane.com` / Password: `password`

> **Note:** For production deployment, see the [Deployment](#-deployment) section.

---

## 💻 Full Installation

### 1. Clone Repository

```bash
git clone https://github.com/InvoicePlane/InvoicePlane-v2.git
cd InvoicePlane-v2
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# JavaScript dependencies
yarn install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure:

```env
APP_NAME="InvoicePlane"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=invoiceplane_v2
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Queue Configuration (required for exports)
QUEUE_CONNECTION=redis  # or 'database' or 'sync' for local dev

# Redis Configuration (recommended)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### 4. Database Setup

```bash
# Run migrations and seed database
php artisan migrate --seed
```

This creates:
- Default admin user
- Sample data (invoices, customers, products)
- Default roles and permissions

### 5. Build Assets

```bash
# Development build
yarn dev

# Production build
yarn build
```

### 6. Start Application

```bash
# Development server
php artisan serve

# Queue worker (required for export functionality)
php artisan queue:work
```

For production setup with Nginx/Apache, see [INSTALLATION.md](.github/INSTALLATION.md).

---

## ⚙️ Configuration

### Queue Workers

Export functionality requires a queue worker to be running:

```bash
# Local development (sync queue)
QUEUE_CONNECTION=sync

# Production (Redis recommended)
QUEUE_CONNECTION=redis
```

For production, use a process manager like Supervisor:

```ini
[program:invoiceplane-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

### Peppol Configuration

Peppol e-invoicing is **not yet implemented** on `develop` — see the [Peppol E-Invoicing](#-peppol-e-invoicing) section below for details and tracking issue.

---

## 🛠️ Development

### Running Tests

Tests run inside the Docker workspace container — the database is only reachable there:

```bash
# Run all tests
docker exec ivpldock-workspace-1 bash -c "cd /var/www/projects/ip2 && php artisan test"

# Run specific test file
docker exec ivpldock-workspace-1 bash -c "cd /var/www/projects/ip2 && php artisan test Modules/Invoices/Tests/Feature/InvoicesTest.php"

# Fall back to phpunit for full exception traces on failure
docker exec ivpldock-workspace-1 bash -c "cd /var/www/projects/ip2 && vendor/bin/phpunit Modules/Invoices/Tests/Feature/InvoicesTest.php"
```

Or use the Makefile shorthand (see `Makefile` for available targets).

**Without Docker:** if you don't have the Docker workspace set up, you can run the suite locally against an in-memory SQLite database instead. Create/edit `.env.testing`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

Then run tests normally:

```bash
php artisan test
```

See [RUNNING_TESTS.md](.github/RUNNING_TESTS.md) for advanced testing.

### Code Quality

```bash
# Format code with Laravel Pint
vendor/bin/pint

# Run static analysis
vendor/bin/phpstan analyse

# Run Rector for automated refactoring
vendor/bin/rector process --dry-run
```

### Asset Development

```bash
# Development build with hot reload
yarn dev

# Production build
yarn build

# Watch for changes
yarn dev --watch
```

### Database Seeding

```bash
# Seed all seeders
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=InvoiceSeeder

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

See [SEEDING.md](.github/SEEDING.md) for custom seeding.

---

## 📦 Module Structure

All business logic lives under `Modules/` (via `nwidart/laravel-modules`), not in `app/`. The root `app/` directory is intentionally thin — it exists only for framework bootstrapping (`app/Providers/AppServiceProvider.php`); `app/Http/Controllers/` is empty because routing is handled entirely by Filament panel providers.

There are 8 modules today: `Core`, `Clients`, `Invoices`, `Quotes`, `Payments`, `Products`, `Projects`, and `Expenses`. Every module follows the same internal layout:

```
Modules/<Name>/
  Database/{Factories,Migrations,Seeders}/
  Models/
  Filament/{Admin,Company}/Resources/<Resource>/{Pages,Tables,Schemas}/
  Services/
  Enums/
  Events/ Listeners/ Observers/
  Traits/ Helpers/
  Http/{Controllers,Requests,Middleware}/
  Providers/
  Tests/{Unit,Feature}/
```

- **`Filament/Admin/...`** and **`Filament/Company/...`** hold the resources/pages registered on the two main Filament panels (see [Panel Access](#-panel-access) below).
- **`Services/`** hold the business-logic layer — services extend `Modules\Core\Services\BaseService` and accept/return plain arrays and Eloquent models (there is no separate DTO layer).
- **`Tests/Unit`** and **`Tests/Feature`** are discovered automatically by PHPUnit across all modules (see `phpunit.xml`); there's no central `tests/` directory at the project root.

Before adding a new resource/model/service, check the [Module Checklist](.github/CHECKLIST.md) to avoid duplicating something that already exists in another module.

### Panel Access

InvoicePlane v2 registers three Filament panels:

| Panel | URL path | Tenanted | Typical users |
|-------|----------|----------|----------------|
| **Admin** | `/admin` | No | Super admins, admins, assistants |
| **Company** | `/{search_code}` (root) | Yes — scoped to `Company` by `search_code` | Company admins and customers |
| **User** | `/user` | No | Reserved for future use |

The company panel has no fixed `/company` path — it is tenant-scoped and the tenant's `search_code` (lowercased) is the first URL segment, e.g. `/ivplv2/dashboard` for the seeded default company.

---

## 🧪 Testing

InvoicePlane v2 includes comprehensive PHPUnit tests:

### Test Structure

```
Modules/
  ├── Invoices/Tests/
  │   ├── Unit/          # Unit tests
  │   ├── Feature/       # Feature tests
  │   └── Integration/   # Integration tests
  ├── Quotes/Tests/
  └── ...
```

### Writing Tests

All tests follow these conventions:
- Test methods start with `it_` (e.g., `it_creates_invoice`)
- Use `#[Test]` attribute
- Follow Arrange-Act-Assert pattern
- Extend appropriate base test case

Example:

```php
#[Test]
public function it_creates_invoice(): void
{
    /* Arrange */
    $customer = Customer::factory()->create();
    
    /* Act */
    $invoice = Invoice::factory()->create(['customer_id' => $customer->id]);
    
    /* Assert */
    $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
}
```

---

## 📧 Peppol E-Invoicing

**Status: not yet implemented on `develop`.** `Modules/Invoices/Config/config.php` defines an extensive configuration tree for a planned multi-format Peppol integration, but there is currently no consuming PHP code anywhere in the repository — no `PeppolService`, no provider adapters, nothing wired into the invoice send flow. The real implementation work lives on the unmerged branch `feature/126-implement-peppol`, tracked by issue [#126](https://github.com/InvoicePlane/InvoicePlane-v2/issues/126).

If you're looking to contribute Peppol support, start with that issue and branch rather than the config file alone — the config schema exists, but the format handlers, access-point client, and UI wiring described in earlier drafts of this README were aspirational and have been removed here until they actually ship.

---

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper `APP_URL`
- [ ] Use strong `APP_KEY` (never share)
- [ ] Configure production database (MySQL/PostgreSQL)
- [ ] Set up Redis for cache and queue
- [ ] Configure queue workers with Supervisor
- [ ] Set up proper mail configuration
- [ ] Configure backups
- [ ] Set up SSL/TLS certificates
- [ ] Configure firewall rules
- [ ] Set up monitoring and logging

### Web Server Configuration

#### Nginx

```nginx
server {
    listen 80;
    server_name invoiceplane.example.com;
    root /var/www/invoiceplane/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache

```apache
<VirtualHost *:80>
    ServerName invoiceplane.example.com
    DocumentRoot /var/www/invoiceplane/public

    <Directory /var/www/invoiceplane/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/invoiceplane_error.log
    CustomLog ${APACHE_LOG_DIR}/invoiceplane_access.log combined
</VirtualHost>
```

### Docker Deployment

See [DOCKER.md](.github/DOCKER.md) for containerized deployment.

---

## 📚 Documentation

### Getting Started
- **[Quick Start](#-quick-start)** - 5-minute setup guide
- **[Installation Guide](.github/INSTALLATION.md)** - Complete setup instructions
- **[Docker Setup](.github/DOCKER.md)** - Run with Docker containers

### Development
- **[Contributing Guide](.github/CONTRIBUTING.md)** - How to contribute code
- **[Module Checklist](.github/CHECKLIST.md)** - Avoid duplication when creating modules
- **[Running Tests](.github/RUNNING_TESTS.md)** - Testing guide
- **[Seeding Guide](.github/SEEDING.md)** - Database seeding instructions

### Advanced Topics
- **[Peppol Architecture](.github/PEPPOL_ARCHITECTURE.md)** - E-invoicing system details
- **[Maintenance Guide](.github/MAINTENANCE.md)** - Dependency management and security
- **[Theme Customization](.github/THEMES.md)** - Customize the UI
- **[Data Import](.github/IMPORTING.md)** - Import from other systems
- **[Translations](.github/TRANSLATIONS.md)** - Internationalization

### Operations
- **[Upgrade Guide](.github/UPGRADE.md)** - Upgrading between versions
- **[GitHub Actions Setup](.github/workflows/README.md)** - CI/CD automation
- **[Security Policy](.github/SECURITY.md)** - Reporting vulnerabilities

---

## 🤝 Contributing

We welcome contributions from the community!

### How to Contribute

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes** following our coding standards
4. **Write/update tests** for your changes
5. **Run tests** (`php artisan test`)
6. **Format code** (`vendor/bin/pint`)
7. **Commit changes** (`git commit -m 'Add amazing feature'`)
8. **Push to branch** (`git push origin feature/amazing-feature`)
9. **Open a Pull Request**

### Guidelines

- Follow the [Contributing Guide](.github/CONTRIBUTING.md)
- Review the [Module Checklist](.github/CHECKLIST.md) before creating modules
- Follow PSR-12 coding standards
- Write meaningful commit messages (see [git-commit-instructions.md](.github/git-commit-instructions.md))
- All tests must pass
- Maintain test coverage above 80%
- Update documentation for new features

### Code of Conduct

- Be respectful and inclusive
- Welcome newcomers
- Focus on what's best for the community
- Show empathy towards other community members

---

## 🌍 Translations

Help translate InvoicePlane v2 into your language!

**[Join Translation Project on Crowdin →](https://translations.invoiceplane.com)**

### Current Languages

- 🇬🇧 English (default)
- 🇳🇱 Dutch
- 🇩🇪 German
- 🇪🇸 Spanish
- 🇫🇷 French
- 🇮🇹 Italian
- 🇵🇹 Portuguese
- And more...

See [TRANSLATIONS.md](.github/TRANSLATIONS.md) for translation guidelines.

---

## 💬 Support & Community

### Get Help

- **Discord** - [Join our Discord server](https://discord.gg/PPzD2hTrXt) for real-time chat
- **Forums** - [Community discussions](https://community.invoiceplane.com)
- **Documentation** - [Official wiki](https://wiki.invoiceplane.com)
- **Issue Tracker** - [Report bugs](https://github.com/InvoicePlane/InvoicePlane-v2/issues)

### Reporting Issues

When reporting bugs, please include:
- InvoicePlane version
- PHP version
- Laravel version
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable

### Security Vulnerabilities

**Do not** report security vulnerabilities through public GitHub issues.

Instead, follow our [Security Policy](.github/SECURITY.md) for responsible disclosure.

---

## 📄 License

InvoicePlane v2 is open-source software licensed under the **MIT License**.

See [LICENSE](LICENSE) file for details.

The InvoicePlane name and logo are protected trademarks of Kovah.de.

---

## 🙏 Acknowledgments

### Built With

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Filament](https://filamentphp.com) - Beautiful admin panels
- [Livewire](https://livewire.laravel.com) - A magical front-end framework
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript framework
- [PHPUnit](https://phpunit.de) - Testing framework

### Special Thanks

- All our amazing [contributors](https://github.com/InvoicePlane/InvoicePlane-v2/graphs/contributors)
- The Laravel community
- The Filament team
- Everyone who reports bugs and suggests features
- Translation contributors on Crowdin

---

**Made with ❤️ by the InvoicePlane Community**

[⬆ Back to top](#invoiceplane-v2)
