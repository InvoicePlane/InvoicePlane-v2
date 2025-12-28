### Features

- **Invoice & Quote Management** - Create, send, and track invoices and quotes
- **Peppol E-Invoicing** - Send invoices through the European Peppol network (UBL, FatturaPA, ZUGFeRD)
- **Customer & Contact Handling** - Manage customers and relationships
- **Payment Tracking & Reminders** - Track payments and send automated reminders
- **Modular Architecture** - Laravel + Filament with clean module separation
- **Multi-Tenant Support** - Via Filament Companies with company isolation
- **Realtime UI** - Built with Livewire for reactive interfaces
- **Asynchronous Export System** - Requires queue workers for background processing
- **Comprehensive Testing** - PHPUnit tests with 100% coverage goal

---

### Installation

To install and run InvoicePlane V2 locally, see the [Installation Guide](.github/INSTALLATION.md).

Quick summary:

```bash
git clone https://github.com/InvoicePlane/InvoicePlane.git
cd InvoicePlane
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# Start queue worker for export functionality
php artisan queue:work
```

**Note:** Export functionality requires a queue worker to be running. For production, configure a queue driver (Redis, database, etc.) and use a process manager like Supervisor.

For detailed steps, see: INSTALLATION.md


---

### Contributing

We welcome community contributions!

To learn how to contribute code, create modules, write tests, or help translate the app:

Read the Contributing Guide

Follow the Module Checklist to avoid duplication

### Translations
Use Crowdin to help with translations:
https://translations.invoiceplane.com

---

Support & Community

Discord: https://discord.gg/PPzD2hTrXt

Forums: https://community.invoiceplane.com

Issue Tracker: https://github.com/InvoicePlane/InvoicePlane/issues

Documentation Wiki: https://wiki.invoiceplane.com

---

### Security

See SECURITY.md for more info.


---

License

InvoicePlane V2 is open-source software licensed under the MIT License.
The InvoicePlane name and logo are protected trademarks of Kovah.de.
