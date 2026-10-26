# Database Seeding

Running `php artisan migrate --seed` will initialize the application with:

## Core Seeds

- Default settings (`settings` table)
- System users & roles (if defined)

## Development Seeds

- Sample customers
- Example invoices
- Demo products
- Test payment methods

## How to Customize

You can modify any of the seeders in:

```bash
database/seeders/
Modules/{Module}/Database/Seeders/
```

To re-seed the database:

`php artisan migrate:fresh --seed`

Note: Never use test seeders in production unless you customize them for live use.

---

Want to add new seed data for a module?
Create a seeder in the appropriate Modules/{Module}/Database/Seeders/ directory and register it.
