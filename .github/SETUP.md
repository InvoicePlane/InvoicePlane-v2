# Full Setup Guide

This guide explains everything you need to get InvoicePlane V2 running for local development.

---

## 1. Clone the Repo

```bash
git clone https://github.com/InvoicePlane/InvoicePlane-v2.git ivplv2
cd ivplv2
```


---

2. Create the Environment File

`cp .env.example .env`

Edit .env and set your database credentials:

```
DB_CONNECTION=mysql
DB_DATABASE=invoiceplane_db
DB_USERNAME=root
DB_PASSWORD=yourpassword
```


---

3. Install Dependencies

`composer install`
`yarn install && yarn build`

---

4. Generate App Key

`php artisan key:generate`


---

5. Migrate and Seed Database

`php artisan migrate --seed`




---

Optional Tools

Laravel Herd → https://laravel.com/docs/herd

MailCatcher → http://localhost:1080

Docker → See DOCKER.md

