# Export Refactoring - Filament Export Action

## Overview

This document outlines the refactoring of export functionality from Maatwebsite/Excel to Filament's built-in Export Action system.

## Changes Made

### 1. Created Filament Exporters

All modules now have dedicated Filament Exporters located in `Modules/{ModuleName}/Filament/Exporters/`:

**Expenses Module:**
- `ExpenseExporter` - Regular export with 7 columns
- `ExpenseLegacyExporter` - Legacy export with 3 columns

**Products Module:**
- `ProductExporter` - Regular export with 7 columns
- `ProductLegacyExporter` - Legacy export with 3 columns

**Quotes Module:**
- `QuoteExporter` - Regular export with 8 columns
- `QuoteLegacyExporter` - Legacy export with 6 columns

**Projects Module:**
- `ProjectExporter` - Regular export with 5 columns
- `ProjectLegacyExporter` - Legacy export with 5 columns

**Tasks (Projects Module):**
- `TaskExporter` - Regular export with 6 columns
- `TaskLegacyExporter` - Legacy export with 6 columns

**Clients Module (Relations):**
- `RelationExporter` - Regular export with 11 columns
- `RelationLegacyExporter` - Legacy export with 4 columns

**Clients Module (Contacts):**
- `ContactExporter` - Regular export with 6 columns
- `ContactLegacyExporter` - Legacy export with 6 columns

**Invoices Module:**
- `InvoiceExporter` - Regular export with 6 columns
- `InvoiceLegacyExporter` - Legacy export with 4 columns

**Payments Module:**
- `PaymentExporter` - Regular export with 5 columns
- `PaymentLegacyExporter` - Legacy export with 4 columns

### 2. Updated List Pages

The following List Pages were updated to use Filament `ExportAction` instead of custom export services:

- `Modules/Expenses/Filament/Company/Resources/Expenses/Pages/ListExpenses.php`
- `Modules/Products/Filament/Company/Resources/Products/Pages/ListProducts.php`
- `Modules/Quotes/Filament/Company/Resources/Quotes/Pages/ListQuotes.php`
- `Modules/Projects/Filament/Company/Resources/Projects/Pages/ListProjects.php`
- `Modules/Projects/Filament/Company/Resources/Tasks/Pages/ListTasks.php`
- `Modules/Clients/Filament/Company/Resources/Relations/Pages/ListRelations.php`
- `Modules/Clients/Filament/Company/Resources/Contacts/Pages/ListContacts.php`
- `Modules/Invoices/Filament/Company/Resources/Invoices/Pages/ListInvoices.php`
- `Modules/Payments/Filament/Company/Resources/Payments/Pages/ListPayments.php`

### 3. Export Actions Available

Each List Page now has 4 export actions in an action group:

1. **Export as CSV (v2)** - Uses the regular exporter with CSV format
2. **Export as CSV (v1, Legacy)** - Uses the legacy exporter with CSV format
3. **Export as Excel (v2)** - Uses the regular exporter with XLSX format
4. **Export as Excel (v1, Legacy)** - Uses the legacy exporter with XLSX format

### 4. Database Migration

A new migration was added to create the `exports` table required by Filament Export:

- `Modules/Core/Database/Migrations/2025_11_13_061624_create_exports_table.php`

Run migrations to apply:
```bash
php artisan migrate
```

## Backward Compatibility

### Preserved Components

The following components are preserved for backward compatibility:

1. **All Maatwebsite/Excel Export Classes** (kept in `Modules/{ModuleName}/Exports/`)
2. **All Export Services** (kept in `Modules/{ModuleName}/Services/`)

These can be deprecated in a future release once the Filament Export system is fully tested and adopted.

## How Filament Export Works

### User Experience

1. User clicks on an export action
2. A modal opens showing available columns to export
3. User can select/deselect columns and customize column labels
4. User clicks "Export"
5. Export job is queued and runs asynchronously
6. User receives a notification when export is complete
7. User can download the exported file from the notification

### Technical Flow

1. `ExportAction` creates an `Export` database record
2. Export jobs are dispatched to the queue
3. Jobs process records in chunks (default: 100 rows per chunk)
4. Progress is tracked in the `exports` table
5. On completion, a notification is sent to the user
6. Exported file is stored on configured disk

### Configuration

Exporters can be configured in each `*Exporter.php` class:

- `getColumns()` - Define exportable columns
- `getModel()` - Specify the model being exported
- `getCompletedNotificationBody()` - Customize completion notification
- `getOptionsFormComponents()` - Add custom export options

## Testing

### Manual Testing Steps

For each module (Expenses, Products, Quotes, Projects, Tasks, Relations, Contacts, Invoices, Payments):

1. Navigate to the list page
2. Click the "Export" button
3. Test each of the 4 export options:
   - Export as CSV (v2)
   - Export as CSV (v1, Legacy)
   - Export as Excel (v2)
   - Export as Excel (v1, Legacy)
4. Verify:
   - Modal opens with column selection
   - Export completes successfully
   - Notification is received
   - File downloads correctly
   - File contains expected data and columns

### Automated Testing

The existing test files need to be updated to test Filament Export actions:

- `Modules/Expenses/Feature/Modules/ExpensesExportImportTest.php`
- `Modules/Products/Feature/Modules/ProductsExportImportTest.php`
- `Modules/Quotes/Feature/Modules/QuotesExportImportTest.php`
- `Modules/Projects/Feature/Modules/ProjectsExportImportTest.php`
- `Modules/Projects/Feature/Modules/TasksExportImportTest.php`

These tests are currently marked as incomplete and need to be rewritten to test Filament Export behavior.

## Future Improvements

1. **Deprecate Export Services**: Once Filament Export is fully tested, the old export services can be removed
2. **Update Tests**: Rewrite export tests to work with Filament's asynchronous export system
3. **Custom Export Options**: Add filtering, date ranges, and other export options via `getOptionsFormComponents()`
4. **Scheduled Exports**: Implement recurring exports using Filament's export scheduling features
5. **Export Templates**: Allow users to save preferred export configurations

## Troubleshooting

### Queue Configuration

Filament Export uses Laravel's queue system. Ensure your queue is configured:

```bash
# Start queue worker
php artisan queue:work
```

### Storage Configuration

Exports are stored using Laravel's filesystem. Ensure your storage is configured in `config/filesystems.php`.

### Permission Issues

Ensure the `exports` table exists and migrations have been run:

```bash
php artisan migrate
```

## References

- [Filament Export Documentation](https://filamentphp.com/docs/4.x/actions/export)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Maatwebsite/Excel Documentation](https://docs.laravel-excel.com)
