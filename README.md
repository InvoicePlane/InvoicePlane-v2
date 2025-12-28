### Features

- Invoice & Quote Management
- Customer & Contact Handling
- Payment Tracking & Reminders
- Modular Architecture (Laravel + Filament)
- Multi-Tenant Support via Filament Companies
- Realtime UI with Livewire
- Asynchronous Export System (requires queue workers)

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
