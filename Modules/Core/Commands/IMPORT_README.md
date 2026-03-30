# InvoicePlane v1 to v2 Database Import

This document describes how to use the `import:db` command to migrate data from InvoicePlane v1 to InvoicePlane v2.

## Overview

The `import:db` command allows you to:
- Import a complete InvoicePlane v1 database from a MySQL dump file
- Map v1 data structures to v2 schema
- Maintain all relationships between entities
- Import into an existing company or create a new one

## Requirements

- InvoicePlane v1 MySQL database dump file
- MySQL/MariaDB database server
- PHP 8.2 or higher
- Laravel 12+ with InvoicePlane v2 installed

## Command Syntax

```bash
php artisan import:db <filename> [--company_id=<id>]
```

### Arguments

- `filename` (required): Filename of the SQL dump located in `storage/app/private/imports/`

### Options

- `--company_id` (optional): ID of an existing company to import data into. If not specified, a new company will be created.

## Usage Examples

### Import into a new company

Place your dump file in `storage/app/private/imports/` and run:

```bash
php artisan import:db invoiceplane_v1_dump.sql
```

This will:
1. Create a new company named "Imported from InvoicePlane v1"
2. Import all data from the dump file into this company
3. Display import statistics

### Import into an existing company

```bash
php artisan import:db invoiceplane_v1_dump.sql --company_id=22
```

This will import all data into company with ID 22.

## Data Import Order

The import process follows dependency order to maintain referential integrity:

1. **Tax Rates** - Import first as they're referenced by products and items
2. **Product Categories** (v1: product_families) - Required for products
3. **Product Units** - Required for products
4. **Products** - Required for invoice/quote items
5. **Clients** (v2: relations) - Required for invoices and quotes
6. **Invoice Groups** (v2: numbering) - Used for invoice/quote numbering
7. **Invoices** with Invoice Items - Main invoice data
8. **Quotes** with Quote Items - Quote data
9. **Payments** - Linked to invoices and customers

## Data Mapping

### Status Mappings

#### Invoice Status (v1 → v2)
- 1 → draft
- 2 → sent
- 3 → viewed
- 4 → paid
- 5 → overdue

#### Quote Status (v1 → v2)
- 1 → draft
- 2 → sent
- 3 → viewed
- 4 → approved
- 5 → rejected
- 6 → canceled

#### Payment Method (v1 → v2)
- 1 → cash
- 2 → bank_transfer
- 3 → credit_card
- 4 → paypal

### Table Mappings

| InvoicePlane v1 Table | InvoicePlane v2 Table | Notes |
|-----------------------|-----------------------|-------|
| `ip_families` | `product_categories` | Product families become categories |
| `ip_units` | `product_units` | Direct mapping |
| `ip_products` | `products` | With category and unit relationships |
| `ip_clients` | `relations` | Clients become customer relations |
| `ip_invoice_groups` | `numbering` | Invoice groups become numbering records |
| `ip_invoices` | `invoices` | With customer relationship |
| `ip_invoice_items` | `invoice_items` | With product and invoice relationships |
| `ip_quotes` | `quotes` | With prospect relationship |
| `ip_quote_items` | `quote_items` | With product and quote relationships |
| `ip_payments` | `payments` | With invoice and customer relationships |
| `ip_tax_rates` | `tax_rates` | Direct mapping |

## Import Statistics

After a successful import, the command displays statistics:

```
Import completed successfully!
+---------------------+-------+
| Entity              | Count |
+---------------------+-------+
| Product Categories  | 5     |
| Product Units       | 3     |
| Products            | 127   |
| Clients             | 42    |
| Invoice Groups      | 2     |
| Invoices            | 358   |
| Invoice Items       | 891   |
| Quotes              | 67    |
| Quote Items         | 134   |
| Payments            | 289   |
+---------------------+-------+
```

## Error Handling

### Missing Tables
The import service checks for table existence before importing. If a v1 table doesn't exist in the dump, it will be skipped without error.

### Missing Dependencies
- Invoices without clients will be skipped
- Quotes without prospects will be skipped
- Payments without invoices or customers will be skipped
- Products without categories will be assigned to a default "Default" category

### Database Errors
If the dump restoration fails or database errors occur, the command will:
1. Display the error message
2. Show stack trace
3. Return exit code 1
4. Leave temporary database for debugging (can be manually dropped)

## Technical Details

### Temporary Database
The import process:
1. Creates a temporary database named `invoiceplane_v1_import`
2. Restores the dump file to this database
3. Reads data from temporary database
4. Imports into v2 schema
5. **Note:** Temporary database is kept for debugging purposes and should be manually dropped if needed

### ID Mapping
The service maintains internal ID mappings to preserve relationships:
- Old v1 IDs are mapped to new v2 IDs
- Relationships are updated to use new IDs
- Foreign key constraints are respected

### Default Values
When v1 data is missing or incomplete:
- Default user ID: Auto-assigned from existing users scoped to company
- Default product type: "service"
- Default payment status: "paid"
- Default invoice/quote date: Current date

## Troubleshooting

### "Dump file not found" Error
Ensure the file path is correct and the file exists:
```bash
ls -la /path/to/dump.sql
```

### "Failed to restore dump" Error
Check:
- MySQL credentials in `.env` file are correct
- MySQL server is running
- User has permission to create databases
- Dump file is valid MySQL format

### "Could not authenticate" Error
Verify database credentials:
```bash
mysql -u username -p -e "SELECT 1"
```

### Memory Issues
For large databases, you may need to increase PHP memory limit:
```bash
php -d memory_limit=512M artisan import:db dump.sql
```

## Testing

The import functionality includes comprehensive PHPUnit tests:

```bash
# Run import tests only
php artisan test --filter ImportInvoicePlaneV1CommandTest

# Run with coverage
php artisan test --filter ImportInvoicePlaneV1CommandTest --coverage
```

Test fixtures are located in: `Modules/Core/Tests/Fixtures/test_invoiceplane_v1_dump.sql`

## Security Considerations

- The command requires database credentials with CREATE DATABASE privilege
- Temporary import database is kept after import for debugging and verification; drop it manually when no longer needed
- SQL injection is prevented by using Laravel's query builder
- File paths are validated before processing

## Support

For issues or questions:
1. Check this README first
2. Review error messages and stack traces
3. Check database logs
4. Open an issue on GitHub with:
   - Error message
   - InvoicePlane v1 version
   - Database dump size/structure
   - PHP and MySQL versions
