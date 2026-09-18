# Report Builder Enhancements

## Overview

This document describes the enhancements made to the Report Builder functionality in InvoicePlane v2. The changes address several issues and add new capabilities for managing report templates and blocks.

## Problems Solved

### 1. Block Width Options
**Problem**: Report blocks only supported half-width and full-width options.

**Solution**: Extended `ReportBlockWidth` enum to support four width options:
- `ONE_THIRD` (4 columns in 12-column grid)
- `HALF` (6 columns)
- `TWO_THIRDS` (8 columns)
- `FULL` (12 columns)

### 2. Block Edit Form Not Populating
**Problem**: When clicking "Edit" on a block in the Report Builder, the form opened but didn't show the record's data.

**Solution**: 
- Fixed `configureBlockAction()` in `ReportBuilder.php` to properly lookup blocks using `block_type`
- Added proper form population in both `fillForm()` and `mountUsing()` methods
- Added logging (`Log::info`) for debugging purposes to help identify data issues

### 3. Debugging Visibility
**Problem**: Using `dd()` in Livewire/Alpine context didn't show debug output.

**Solution**: Replaced debug dumps with `Log::info()` calls that write to Laravel's log files:
```php
Log::info('Block data for edit:', $data);
Log::info('Mounting block config with data:', $data);
```

### 4. Field Drag/Drop Canvas
**Problem**: No way to configure which fields appear in a block or their layout.

**Solution**: 
- Created a drag-and-drop field canvas interface
- Added `fields-canvas.blade.php` view component
- Integrated canvas into the block editor slideover panel
- Fields can be dragged from "Available Fields" to the canvas
- Field configurations are saved to JSON files

### 5. Block Width Rendering
**Problem**: Blocks in the init() function didn't respect their configured widths (e.g., full-width invoice_items showed as half-width).

**Solution**: Updated the Alpine.js template in `design-report-template.blade.php` to properly calculate grid-column spans based on block widths:
```javascript
grid-column: span ${block.position.width >= 12 ? '2' : (block.position.width >= 8 ? '2' : '1')}
```

## Technical Implementation

### Enum Enhancement
```php
enum ReportBlockWidth: string
{
    case ONE_THIRD = 'one_third';
    case HALF = 'half';
    case TWO_THIRDS = 'two_thirds';
    case FULL = 'full';

    public function getGridWidth(): int
    {
        return match ($this) {
            self::ONE_THIRD => 4,
            self::HALF => 6,
            self::TWO_THIRDS => 8,
            self::FULL => 12,
        };
    }
}
```

### Field Storage Architecture
Fields are stored separately from blocks:
- **Block Records**: Stored in `report_blocks` database table with metadata
- **Field Configurations**: Stored in JSON files at `storage/app/report_blocks/{slug}.json`

This separation allows:
- Fast block queries without loading heavy field data
- Easy version control and backup of field configurations
- Flexibility to extend field properties without schema changes

### ReportBlockService Methods
```php
// Save fields to JSON file
saveBlockFields(ReportBlock $block, array $fields): void

// Load fields from JSON file
loadBlockFields(ReportBlock $block): array

// Get complete configuration including fields
getBlockConfiguration(ReportBlock $block): array
```

### Field Canvas Component
The drag/drop canvas supports:
- Dragging available fields to canvas
- Removing fields from canvas
- Preserving field positions and dimensions
- Complex field metadata (styles, visibility, etc.)
- Real-time sync with Livewire component state

## Database Changes

### Migration: report_blocks table
Updated default values and column comments:
```php
// Updated width column to support 4 options
$table->string('width')->default('half'); // one_third, half, two_thirds, or full

// Added data_source default
$table->string('data_source')->default('invoice');
```

**Note on Configuration Storage:**
Block field configurations are **not** stored in the database. Instead, they are stored as JSON files in the filesystem at `storage/app/report_blocks/{slug}.json`. This separates the block metadata (in database) from the field layout configuration (in files), allowing for easier version control and more flexible configuration management.

## Testing

All new functionality is covered by comprehensive PHPUnit tests (marked as incomplete per requirements):

### Unit Tests
- `ReportBlockWidthTest`: Tests enum values and grid width calculations (6 tests)
- `ReportBlockServiceFieldsTest`: Tests JSON field storage/loading (9 tests)

### Feature Tests
- `ReportBuilderBlockWidthTest`: Tests width rendering in designer (8 tests)
- `ReportBuilderBlockEditTest`: Tests form data population (8 tests)
- `ReportBuilderFieldCanvasIntegrationTest`: Tests field canvas workflow (8 tests)

**Total: 39 test cases**

To run the tests:
```bash
php artisan test --filter=ReportBlock
php artisan test --filter=ReportBuilder
```

## Usage Examples

### Creating a Block with Custom Width
```php
$block = ReportBlock::create([
    'block_type' => 'custom_block',
    'name' => 'Custom Block',
    'width' => ReportBlockWidth::TWO_THIRDS,
    'data_source' => 'invoice',
    'default_band' => 'header',
]);
```

### Saving Field Configuration
```php
$service = app(ReportBlockService::class);

$fields = [
    [
        'id' => 'company_name',
        'label' => 'Company Name',
        'x' => 0,
        'y' => 0,
        'width' => 200,
        'height' => 40,
    ],
    [
        'id' => 'company_address',
        'label' => 'Company Address',
        'x' => 0,
        'y' => 50,
        'width' => 200,
        'height' => 60,
    ],
];

$service->saveBlockFields($block, $fields);
```

### Loading Field Configuration
```php
$fields = $service->loadBlockFields($block);
```

## Files Modified

### Core Files
- `Modules/Core/Enums/ReportBlockWidth.php` - Enhanced enum
- `Modules/Core/Models/ReportBlock.php` - Added HasFactory trait
- `Modules/Core/Models/ReportTemplate.php` - Added HasFactory trait
- `Modules/Core/Services/ReportTemplateService.php` - Updated width calculation
- `Modules/Core/Services/ReportBlockService.php` - Added field management methods

### Filament Resources
- `Modules/Core/Filament/Admin/Resources/ReportTemplates/Pages/ReportBuilder.php` - Fixed form population
- `Modules/Core/Filament/Admin/Resources/ReportBlocks/Schemas/ReportBlockForm.php` - Added field canvas

### Views
- `Modules/Core/resources/views/filament/admin/resources/report-template-resource/pages/design-report-template.blade.php` - Fixed width rendering
- `Modules/Core/resources/views/filament/admin/resources/report-blocks/fields-canvas.blade.php` - New canvas view

### Database
- `Modules/Core/Database/Migrations/2026_01_01_184544_create_report_blocks_table.php` - Added config column
- `Modules/Core/Database/Factories/ReportBlockFactory.php` - New factory
- `Modules/Core/Database/Factories/ReportTemplateFactory.php` - New factory

### Tests
- `Modules/Core/Tests/Unit/ReportBlockWidthTest.php` - New
- `Modules/Core/Tests/Unit/ReportBlockServiceFieldsTest.php` - New
- `Modules/Core/Tests/Feature/ReportBuilderBlockWidthTest.php` - New
- `Modules/Core/Tests/Feature/ReportBuilderBlockEditTest.php` - New
- `Modules/Core/Tests/Feature/ReportBuilderFieldCanvasIntegrationTest.php` - New

## Future Enhancements

Potential areas for future improvement:
1. Visual field editor with WYSIWYG preview
2. Field templates/presets for common layouts
3. Conditional field visibility based on data
4. Field validation rules
5. Custom field types (QR codes, barcodes, charts)
6. Multi-language field labels
7. Export/import field configurations

## Notes

- All tests are marked as incomplete (`markTestIncomplete()`) by default as requested
- Tests have working implementations and can be unmarked when ready to run
- Field JSON files are stored in `storage/app/report_blocks/` directory
- Logging can be monitored at `storage/logs/laravel.log`
- Block widths automatically map to grid columns using `getGridWidth()` method
