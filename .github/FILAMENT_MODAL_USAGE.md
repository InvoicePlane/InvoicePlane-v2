# Filament Modal Width Usage Guide

## Correct Usage

### For Modal Actions (CreateAction, EditAction, ViewAction, etc.)

Use **string values** with `modalWidth()`:

```php
CreateAction::make()
    ->modalWidth('full')  // ✅ Correct
    // Other valid values: 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', 'screen'
```

### For Panel Configuration (in Panel Providers)

Use **Width enum** with `maxContentWidth()`:

```php
$panel
    ->maxContentWidth(Width::Full)  // ✅ Correct
    // This is the only place where Width enum should be used
```

## Common Mistakes to Avoid

### ❌ INCORRECT: Using Width enum with modalWidth()

```php
EditAction::make()
    ->modalWidth(Width::FiveExtraLarge)  // ❌ ERROR: modalWidth() expects string, not enum
```

**Error**: `Width::FiveExtraLarge` doesn't exist, and even if it did, `modalWidth()` expects a string.

**Fix**: Use a string value instead:
```php
EditAction::make()
    ->modalWidth('7xl')  // ✅ Correct
```

### ❌ INCORRECT: Using slideOver() with modal actions incorrectly

```php
CreateAction::make()
    ->modalWidth('full')
    ->slideOver()  // ❌ May not work as expected with modals
```

`slideOver()` is for slide-over panels, not modals. If you want a slide-over effect, configure it properly according to Filament documentation.

## Valid modalWidth String Values

- `'sm'` - Small
- `'md'` - Medium (default)
- `'lg'` - Large
- `'xl'` - Extra Large
- `'2xl'` - 2x Extra Large
- `'3xl'` - 3x Extra Large
- `'4xl'` - 4x Extra Large
- `'5xl'` - 5x Extra Large
- `'6xl'` - 6x Extra Large
- `'7xl'` - 7x Extra Large (largest)
- `'full'` - Full width
- `'screen'` - Full screen

## Examples from the Codebase

### List Page Create Action
```php
// Modules/Invoices/Filament/Company/Resources/Invoices/Pages/ListInvoices.php
CreateAction::make()
    ->modalWidth('full')
    ->mutateDataUsing(function (array $data) {
        return $data;
    })
    ->action(function (array $data) {
        app(InvoiceService::class)->createInvoice($data);
    })
```

### Table Edit Action
```php
// Modules/Invoices/Filament/Company/Resources/Invoices/Tables/InvoicesTable.php
EditAction::make()
    ->action(function (Invoice $record, array $data) {
        app(InvoiceService::class)->updateInvoice($record, $data);
    })
    ->modalWidth('full')
```

### Panel Configuration
```php
// Modules/Core/Providers/AdminPanelProvider.php
use Filament\Support\Enums\Width;

public function panel(Panel $panel): Panel
{
    return $panel
        ->id('admin')
        ->path('admin')
        ->maxContentWidth(Width::Full)  // ✅ Enum is correct here
        // ...
}
```

## References

- [Filament Actions Documentation](https://filamentphp.com/docs/4.x/actions/modals)
- [Filament Panel Configuration](https://filamentphp.com/docs/4.x/panels/configuration)
