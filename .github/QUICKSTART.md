# Quickstart

Don’t need a full guide? Here’s how to run InvoicePlane V2 in under 1 minute:

```bash
git clone https://github.com/InvoicePlane/InvoicePlane-v2.git ivplv2
cd ivplv2
cp .env.example .env
edit .env to adjust to your standards,
set the APP_URL, set the database information
composer install
php artisan key:generate
php artisan migrate --seed
```

Then visit:

`http://localhost:8000/ivpl` (Artisan)

`http://invoiceplane.test/` (Herd)

`http://localhost/invoiceplane` (XAMPP)
