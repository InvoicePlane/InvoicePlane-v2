# Data Import & Export Guide

InvoicePlane v2 supports importing data from InvoicePlane v1 databases and exporting data to CSV/Excel formats. This guide covers both workflows.

---

## Exporting Data

All modules support exporting data in CSV and Excel formats:

- **Clients & Contacts**: Export all relation and contact records
- **Invoices & Quotes**: Export with full line items and totals
- **Payments**: Export payment history with invoice links
- **Products & Categories**: Export product catalog with pricing
- **Projects & Tasks**: Export projects and associated tasks
- **Expenses**: Export expense records with categories
- **And more**: Consistent export support across all modules

### How to Export

1. Navigate to any list page (e.g., Invoices, Clients, Products)
2. Use the **Export** action (typically in the header or row actions)
3. Choose format: **CSV** or **Excel**
4. Two versions available:
   - **v2 Format**: Native InvoicePlane v2 schema
   - **v1-Legacy Format**: Compatible with InvoicePlane v1 for backward compatibility

---

## Importing from InvoicePlane v1

The `import:db` command provides a robust pathway to migrate data from InvoicePlane v1 installations.

### Requirements

- **Source**: SQL dump from a v1 installation (e.g., `backup.sql`)
- **Location**: Place dump file in `storage/app/private/imports/`
- **Supported Entities**: 15 entity types with full data integrity:
  - Tax Rates, Products & Categories, Custom Fields
  - Users, Clients & Contacts
  - Invoice Groups (Numbering), Invoices & Items
  - Quotes & Items
  - Payments
  - Projects & Tasks
  - Recurring Invoices
  - Uploads & Attachments
  - Email Templates, Settings, Notes

### Quick Start

#### Dry Run (Preview without Importing)

```bash
php artisan import:db backup.sql --dry-run
```

This shows:
- How many records exist in the source
- How many will migrate successfully
- Which records cannot be imported and why
- A detailed notes section explaining skipped data

#### Import into New Company

```bash
php artisan import:db backup.sql
```

Creates a new company named `{filename} - {YYYY-MM-DD HH:MM:SS}` and imports all compatible records.

#### Import into Existing Company

```bash
php artisan import:db backup.sql --company_id=22
```

If the company doesn't exist, it will be created with that ID.

### Features

- **Idempotent**: Re-running the same import twice skips already-imported records
- **Dry Run Support**: Preview results before committing
- **Financial Reconciliation**: Validates that totals in invoices/quotes match their line items
- **Rollback Capable**: Store the batch ID to rollback if needed (future feature)
- **Error Resilience**: Handles real-world data quality issues (missing fields, oversized values, orphaned records)

### Understanding the Output

After import, you'll see:

```
Migration Results:
+-----------+----------+---------+--------+
| Entity    | Migrated | Skipped | Errors |
+-----------+----------+---------+--------+
| Invoices  | 1,623    | 0       | 0      |
| Clients   | 890      | 0       | 0      |
| Payments  | 748      | 0       | 0      |
...
```

**Skipped records** are documented in the Details section, with reasons (e.g., "Product row #363 has empty name, will be skipped").

### Known Limitations

1. **Email Templates**: The v2 `EmailTemplateType` enum is misconfigured; email templates may not import correctly (workaround: manually recreate in v2)
2. **File Attachments**: File contents are not included in SQL dumps; re-upload manually
3. **User Passwords**: v1 password hashes are not compatible; users must reset passwords or use SSO

---

## Future: CSV & ImportAction UI

The following import methods are planned but not yet implemented:

- **CSV Import UI**: Per-module import wizards via Filament `ImportAction`
- **Excel Import**: Read Excel files directly
- **Bulk Operations**: Import products from external catalogs, clients from spreadsheets

Track progress on [issue #85](https://github.com/InvoicePlane/InvoicePlane-v2/issues/85).

---

## Troubleshooting

### Import Errors

- **"Dump file not found"**: Ensure the SQL file is in `storage/app/private/imports/`
- **"No company found for your account"**: The system user must be attached to a company before import
- **"Connection failed"**: v1 database credentials may be wrong (only for direct DB imports, not SQL dumps)

### Validation Errors

- **Email validation fails**: Check that email addresses in v1 are valid format
- **Invoice totals mismatch**: Line item amounts don't sum to invoice total; review in v1 before importing
- **Missing foreign keys**: Clients must exist before invoices can reference them

---

## Support

- **Community**: [InvoicePlane Community Forums](https://community.invoiceplane.com/)
- **Docs**: [InvoicePlane Wiki](https://wiki.invoiceplane.com/)
