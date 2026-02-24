# Mason Package Integration Plan

## Overview
This document outlines the plan to refactor the InvoicePlane v2 ReportBuilder to use the `awcodes/mason` package instead of the current custom implementation.

## Installation

```bash
composer require awcodes/mason:^3.0
```

### Theme Setup
Since mason requires a custom Filament theme, add to `resources/css/filament/admin/theme.css`:

```css
@import '../../../../vendor/awcodes/mason/resources/css/plugin.css';
```

And update `tailwind.config.js`:

```js
content: [
    // ... existing content paths
    './vendor/awcodes/mason/resources/**/*.blade.php',
],
```

## Architecture Changes

### Current Structure
- `BlockDTO` - Data transfer object for blocks
- `ReportBuilder` Page - Custom drag-drop with Alpine.js
- `ReportTemplateService` - Manages block persistence to JSON files
- Block handlers for different block types

### New Structure with Mason
- **Brick Classes** - Replace BlockDTO with Mason Brick classes
- **BricksCollection** - Group bricks by category
- **Mason Field** - Replace custom drag-drop interface
- **Blade Views** - Preview and render templates for each brick
- **Maintain JSON Storage** - Keep filesystem storage (not database)

## Implementation Steps

### 1. Create Brick Classes for Each Block Type

Create directory: `app/Mason/Bricks/`

Each current `ReportBlockType` becomes a Brick:

#### Example: HeaderCompanyBrick.php
```php
<?php

namespace App\Mason\Bricks;

use Awcodes\Mason\Brick;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class HeaderCompanyBrick extends Brick
{
    public static function getId(): string
    {
        return 'header_company';
    }

    public static function getLabel(): string
    {
        return trans('ip.company_header');
    }

    public static function getIcon(): string | Heroicon | Htmlable | null
    {
        return Heroicon::OutlinedBuildingOffice;
    }

    public static function toPreviewHtml(array $config): ?string
    {
        return view('mason.bricks.header-company.preview', [
            'config' => $config,
        ])->render();
    }

    public static function toHtml(array $config, array $data): ?string
    {
        return view('mason.bricks.header-company.index', [
            'config' => $config,
            'data' => $data,
        ])->render();
    }

    public static function configureBrickAction(Action $action): Action
    {
        return $action
            ->label(trans('ip.configure_company_header'))
            ->modalHeading(trans('ip.company_header_settings'))
            ->icon('heroicon-o-building-office')
            ->slideOver()
            ->fillForm(fn (array $arguments): array => [
                'show_logo' => $arguments['show_logo'] ?? true,
                'show_vat_id' => $arguments['show_vat_id'] ?? true,
                'show_phone' => $arguments['show_phone'] ?? true,
                'show_email' => $arguments['show_email'] ?? true,
                'font_size' => $arguments['font_size'] ?? 10,
            ])
            ->schema([
                Checkbox::make('show_logo')
                    ->label(trans('ip.show_logo'))
                    ->default(true),
                Checkbox::make('show_vat_id')
                    ->label(trans('ip.show_vat_id'))
                    ->default(true),
                Checkbox::make('show_phone')
                    ->label(trans('ip.show_phone'))
                    ->default(true),
                Checkbox::make('show_email')
                    ->label(trans('ip.show_email'))
                    ->default(true),
                TextInput::make('font_size')
                    ->label(trans('ip.font_size'))
                    ->numeric()
                    ->default(10)
                    ->minValue(8)
                    ->maxValue(16),
            ])
            ->action(function (array $arguments, array $data, \Awcodes\Mason\Mason $component) {
                $brick = $component->getBrick($arguments['id']);

                if (blank($brick)) {
                    return;
                }

                $brickContent = [
                    'type' => 'masonBrick',
                    'attrs' => [
                        'config' => $data,
                        'id' => $arguments['id'],
                        'label' => $brick::getPreviewLabel($data),
                        'preview' => base64_encode($brick::toPreviewHtml($data)),
                    ],
                ];

                $component->runCommands([
                    \Awcodes\Mason\Actions\EditorCommand::make(
                        'insertContentAt',
                        arguments: [
                            $arguments['dragPosition'],
                            $brickContent,
                        ],
                    ),
                ]);
            });
    }
}
```

#### Brick Classes Needed
Based on `ReportBlockType` enum:
- `HeaderCompanyBrick` - Company header
- `HeaderClientBrick` - Client header
- `HeaderInvoiceMetaBrick` - Invoice metadata
- `DetailItemsBrick` - Line items table
- `DetailItemTaxBrick` - Tax breakdown
- `FooterTotalsBrick` - Totals section
- `FooterNotesBrick` - Footer notes
- `FooterQrCodeBrick` - QR code
- `CustomTextBrick` - Custom text block
- `CustomImageBrick` - Custom image block

### 2. Create Blade Views

Create directory: `resources/views/mason/bricks/`

For each brick, create two views:
- `preview.blade.php` - Shown in editor
- `index.blade.php` - Final render on PDF

#### Example: header-company/preview.blade.php
```blade
@props([
    'config' => []
])

<div class="border-2 border-dashed border-gray-300 p-4 rounded">
    <div class="flex items-start gap-4">
        @if($config['show_logo'] ?? true)
            <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                <span class="text-xs text-gray-500">{{ trans('ip.logo') }}</span>
            </div>
        @endif
        <div class="flex-1">
            <h3 class="font-bold text-lg">{{ trans('ip.company_name') }}</h3>
            <p class="text-sm text-gray-600">{{ trans('ip.company_address') }}</p>
            @if($config['show_phone'] ?? true)
                <p class="text-sm text-gray-600">{{ trans('ip.phone') }}: +1 234 567 890</p>
            @endif
            @if($config['show_email'] ?? true)
                <p class="text-sm text-gray-600">{{ trans('ip.email') }}: info@company.com</p>
            @endif
            @if($config['show_vat_id'] ?? true)
                <p class="text-sm text-gray-600">{{ trans('ip.vat_id') }}: 12345678</p>
            @endif
        </div>
    </div>
</div>
```

#### Example: header-company/index.blade.php
```blade
@props([
    'config' => [],
    'data' => []
])

<div class="company-header" style="font-size: {{ $config['font_size'] ?? 10 }}pt;">
    <table width="100%">
        <tr>
            @if($config['show_logo'] ?? true)
                <td width="100" valign="top">
                    @if(isset($data['company']['logo_path']))
                        <img src="{{ $data['company']['logo_path'] }}" alt="Logo" style="max-width: 100px;">
                    @endif
                </td>
            @endif
            <td valign="top">
                <strong>{{ $data['company']['name'] ?? '' }}</strong><br>
                {{ $data['company']['address'] ?? '' }}<br>
                @if($config['show_phone'] ?? true)
                    {{ trans('ip.phone') }}: {{ $data['company']['phone'] ?? '' }}<br>
                @endif
                @if($config['show_email'] ?? true)
                    {{ trans('ip.email') }}: {{ $data['company']['email'] ?? '' }}<br>
                @endif
                @if($config['show_vat_id'] ?? true)
                    {{ trans('ip.vat_id') }}: {{ $data['company']['vat_id'] ?? '' }}<br>
                @endif
            </td>
        </tr>
    </table>
</div>
```

### 3. Create BricksCollection

Create: `app/Mason/Collections/ReportBricksCollection.php`

```php
<?php

namespace App\Mason\Collections;

use App\Mason\Bricks\{
    HeaderCompanyBrick,
    HeaderClientBrick,
    HeaderInvoiceMetaBrick,
    DetailItemsBrick,
    DetailItemTaxBrick,
    FooterTotalsBrick,
    FooterNotesBrick,
    FooterQrCodeBrick,
    CustomTextBrick,
    CustomImageBrick
};

class ReportBricksCollection
{
    public static function all(): array
    {
        return [
            HeaderCompanyBrick::class,
            HeaderClientBrick::class,
            HeaderInvoiceMetaBrick::class,
            DetailItemsBrick::class,
            DetailItemTaxBrick::class,
            FooterTotalsBrick::class,
            FooterNotesBrick::class,
            FooterQrCodeBrick::class,
            CustomTextBrick::class,
            CustomImageBrick::class,
        ];
    }

    public static function header(): array
    {
        return [
            HeaderCompanyBrick::class,
            HeaderClientBrick::class,
            HeaderInvoiceMetaBrick::class,
        ];
    }

    public static function detail(): array
    {
        return [
            DetailItemsBrick::class,
            DetailItemTaxBrick::class,
        ];
    }

    public static function footer(): array
    {
        return [
            FooterTotalsBrick::class,
            FooterNotesBrick::class,
            FooterQrCodeBrick::class,
        ];
    }

    public static function custom(): array
    {
        return [
            CustomTextBrick::class,
            CustomImageBrick::class,
        ];
    }
}
```

### 4. Update ReportBuilder Page

Refactor: `Modules/Core/Filament/Admin/Resources/ReportTemplates/Pages/ReportBuilder.php`

```php
<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplates\Pages;

use App\Mason\Collections\ReportBricksCollection;
use Awcodes\Mason\Mason;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\ReportTemplateResource;
use Modules\Core\Models\ReportTemplate;
use Modules\Core\Services\ReportTemplateService;

class ReportBuilder extends Page
{
    use InteractsWithSchemas;

    public ReportTemplate $record;

    protected static string $resource = ReportTemplateResource::class;

    protected string $view = 'core::filament.admin.resources.report-template-resource.pages.design-report-template';

    public function getMaxContentWidth(): string
    {
        return 'full';
    }

    public function mount(ReportTemplate $record): void
    {
        $this->record = $record;
    }

    protected function getSchema(): Schema
    {
        return Schema::make([
            Mason::make('content')
                ->label(trans('ip.report_layout'))
                ->bricks(ReportBricksCollection::all())
                ->previewLayout('layouts.mason-preview')
                ->doubleClickToEdit()
                ->sortBricks()
                ->displayActionsAsGrid()
                ->extraInputAttributes(['style' => 'min-height: 40rem;'])
                ->statePath('content'),
        ]);
    }

    public function save(): void
    {
        $data = $this->schema->getState();
        $service = app(ReportTemplateService::class);
        
        // Convert Mason JSON to our block structure and persist to filesystem
        $blocks = $this->convertMasonDataToBlocks($data['content']);
        $service->persistBlocks($this->record, $blocks);
        
        $this->dispatch('blocks-saved');
    }

    protected function convertMasonDataToBlocks(string $masonJson): array
    {
        $masonData = json_decode($masonJson, true);
        $blocks = [];

        // Transform Mason's structure to our block structure
        foreach ($masonData['content'] ?? [] as $item) {
            if ($item['type'] === 'masonBrick') {
                $attrs = $item['attrs'];
                $blocks[$attrs['id']] = [
                    'id' => $attrs['id'],
                    'type' => $this->extractBrickType($attrs['id']),
                    'config' => $attrs['config'],
                    'label' => $attrs['label'],
                    // ... map other properties
                ];
            }
        }

        return $blocks;
    }

    protected function extractBrickType(string $brickId): string
    {
        // Extract type from brick ID (e.g., "header_company_xyz" -> "header_company")
        return preg_replace('/_[a-z0-9]+$/', '', $brickId);
    }
}
```

### 5. Create Preview Layout

Create: `resources/views/layouts/mason-preview.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} - {{ trans('ip.report_preview') }}</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @masonStyles
        
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background: #f3f4f6;
                padding: 1rem;
            }
            
            #mason-preview-container {
                --mason-border-color: rgb(59, 130, 246);
                --mason-controls-background: rgba(0, 0, 0, 0.8);
                --mason-button-hover-background: rgba(255, 255, 255, 0.2);
                --mason-drop-zone-background: rgba(59, 130, 246, 0.5);
            }
        </style>
    </head>
    <body>
        <main>
            @include('mason::iframe-preview-content', ['blocks' => $blocks])
        </main>
    </body>
</html>
```

### 6. Update Blade Template View

Update: `resources/views/core/filament/admin/resources/report-template-resource/pages/design-report-template.blade.php`

Replace the current custom drag-drop interface with the Mason field from the schema.

### 7. Maintain Filesystem Storage

The key requirement is to **NOT** store blocks in the database. Continue using the current JSON file storage system via `ReportTemplateFileRepository`.

**Adapter Pattern**: Create a service to convert between Mason's JSON format and our block structure:

Create: `Modules/Core/Services/MasonStorageAdapter.php`

```php
<?php

namespace Modules\Core\Services;

use Modules\Core\DTOs\BlockDTO;
use Modules\Core\Transformers\BlockTransformer;

class MasonStorageAdapter
{
    /**
     * Convert Mason JSON to Block DTOs for filesystem storage
     */
    public function masonToBlocks(string $masonJson): array
    {
        $masonData = json_decode($masonJson, true);
        $blocks = [];

        foreach ($masonData['content'] ?? [] as $item) {
            if ($item['type'] === 'masonBrick') {
                $block = $this->createBlockFromMasonBrick($item['attrs']);
                $blocks[$block->getId()] = $block;
            }
        }

        return $blocks;
    }

    /**
     * Convert Block DTOs to Mason JSON for editor
     */
    public function blocksToMason(array $blockDTOs): string
    {
        $content = [];

        foreach ($blockDTOs as $blockDTO) {
            $content[] = [
                'type' => 'masonBrick',
                'attrs' => [
                    'id' => $blockDTO->getId(),
                    'config' => $blockDTO->getConfig(),
                    'label' => $blockDTO->getLabel(),
                    'preview' => base64_encode($this->generatePreview($blockDTO)),
                ],
            ];
        }

        return json_encode([
            'type' => 'doc',
            'content' => $content,
        ]);
    }

    protected function createBlockFromMasonBrick(array $attrs): BlockDTO
    {
        // Transform Mason brick attrs to BlockDTO
        // Implementation details...
    }

    protected function generatePreview(BlockDTO $block): string
    {
        // Generate preview HTML for the block
        // Implementation details...
    }
}
```

### 8. Update Translation Keys

Add to `resources/lang/en/ip.php`:

```php
// Mason Report Builder
'report_layout' => 'Report Layout',
'report_preview' => 'Report Preview',
'configure_company_header' => 'Configure Company Header',
'company_header_settings' => 'Company Header Settings',
'show_logo' => 'Show Logo',
'show_vat_id' => 'Show VAT ID',
'show_phone' => 'Show Phone',
'show_email' => 'Show Email',
'font_size' => 'Font Size',
// ... add translations for all bricks
```

### 9. Testing Updates

Update tests to work with Mason structure:

- `ReportBuilderFieldCanvasIntegrationTest.php`
- `ReportBuilderBlockWidthTest.php`
- `ReportBuilderBlockEditTest.php`

Use Mason's `Faker` helper for generating test data:

```php
use Awcodes\Mason\Support\Faker;

$content = Faker::make()
    ->brick(
        id: 'header_company',
        config: [
            'show_logo' => true,
            'show_vat_id' => true,
            'font_size' => 10,
        ]
    )
    ->asJson();
```

## Migration Strategy

### Phase 1: Install and Setup
1. Install mason package
2. Configure theme and assets
3. Create preview layout

### Phase 2: Create Bricks
1. Create all brick classes
2. Create preview views
3. Create render views
4. Test each brick individually

### Phase 3: Integration
1. Create BricksCollection
2. Update ReportBuilder page
3. Create MasonStorageAdapter
4. Wire up save functionality

### Phase 4: Testing
1. Update all tests
2. Test drag-drop functionality
3. Test persistence to JSON
4. Test PDF generation with new structure

### Phase 5: Cleanup
1. Remove old custom drag-drop code
2. Remove unused DTOs (if any)
3. Update documentation
4. Run linters and fix issues

## Benefits

1. **Drag-and-Drop**: Native, battle-tested drag-drop UI from mason
2. **Maintainability**: Less custom code to maintain
3. **Filament Integration**: Better integration with Filament ecosystem
4. **Extensibility**: Easy to add new brick types
5. **User Experience**: More polished UI with slideOver configs
6. **Testing**: Mason provides faker helpers for testing

## Considerations

1. **Learning Curve**: Team needs to understand Mason's API
2. **Migration**: Existing templates need conversion
3. **Customization**: Some custom features may need workarounds
4. **Bundle Size**: Mason adds additional JS/CSS assets
5. **Storage**: Need adapter to maintain JSON file storage

## Next Steps

1. Install package locally
2. Create first brick (HeaderCompanyBrick)
3. Test basic drag-drop functionality
4. Implement storage adapter
5. Iterate on remaining bricks
