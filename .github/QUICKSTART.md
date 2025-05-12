# Quickstart

Don’t need a full guide? Here’s how to run InvoicePlane V2 in under 1 minute:

```bash
git clone https://github.com/InvoicePlane/InvoicePlane.git
cd InvoicePlane
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
```


Then visit:

`http://localhost:8000/ivpl` (Artisan)

`http://invoiceplane.test/` (Herd)

`http://localhost/invoiceplane` (XAMPP)
