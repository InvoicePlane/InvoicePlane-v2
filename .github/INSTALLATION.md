# Installation Guide

This guide explains how to install and run it in your preferred environment.

---

Requirements

- PHP 8.2+
- Composer
- MariaDB, MySQL or your own choice (tested with MariaDB)
- Node.js & Yarn (or npm)
- Laravel CLI (php artisan)
- Docker, Laravel Herd, or XAMPP/WAMP (or equivalents)

---

Preparations:

```bash
git clone https://github.com/InvoicePlane/InvoicePlane.git
cd InvoicePlane
```

Environment Setup Options

Option 1: Docker or Laravel Sail

`docker compose up -d`

or

`sail up -d`

Visit: http://localhost/ or your own sitename

---

Option 2: Laravel Herd (macOS / Windows)

Visit: `http://invoiceplane.test/`
See YouTube video

---

Option 3: XAMPP / WAMP / MAMP

1. Place the project inside your htdocs or www directory.

2. Create a database (e.g., invoiceplane_db).

3. Update your .env:

```bash
DB_CONNECTION=mysql
DB_DATABASE=invoiceplane_db
DB_USERNAME=root
DB_PASSWORD=
```

Visit: `http://localhost/invoiceplane`

---

Option 4: PHP Artisan Serve

`php artisan serve`

Visit: `http://127.0.0.1:8000/`

---

## Shared Setup Steps

Run these steps regardless of which environment you use:

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
```

---

## Support

Discord: https://discord.gg/PPzD2hTrXt

Community Forums: https://community.invoiceplane.com

Wiki: https://wiki.invoiceplane.com
