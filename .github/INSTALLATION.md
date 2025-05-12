# Installation Guide
---

## Requirements

- PHP 8.2+
- Composer
- MariaDB, MySQL, or your own choice. Tested with MariaDB.
- Node.js & yarm (or npm)

---

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/InvoicePlane/InvoicePlane.git
cd InvoicePlane
```

2. Install PHP Dependencies

```
composer install
```

3. Environment Configuration

cp .env.example .env
php artisan key:generate

Update the .env file to match your database, mail, and app URL settings.


---

Database Setup

Option A: Use your local MySQL/MariaDB

Ensure the database is running and configured in your .env.

```
DB_CONNECTION=mysql
DB_DATABASE=invoiceplane_db
DB_USERNAME=root
DB_PASSWORD=secret
```

---

Migrate & Seed

php artisan migrate --seed


---

First-Time Setup


---

Support

Discord

Community Forums

Documentation Wiki
