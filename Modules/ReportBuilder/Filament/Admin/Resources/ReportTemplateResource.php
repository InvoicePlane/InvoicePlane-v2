<?php

namespace Modules\ReportBuilder\Filament\Admin\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Pages\CreateReportTemplate;
use Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Pages\DesignReportTemplate;
use Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Pages\EditReportTemplate;
use Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Pages\ListReportTemplates;
use Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Schemas\ReportTemplateForm;
use Modules\ReportBuilder\Filament\Admin\Resources\ReportTemplateResource\Tables\ReportTemplatesTable;
use Modules\ReportBuilder\Models\ReportTemplate;

class ReportTemplateResource extends Resource
{
    protected static ?string $model = ReportTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    public static function getModelLabel(): string
    {
        return 'Report Template';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Report Templates';
    }

    public static function getNavigationLabel(): string
    {
        return 'Report Templates';
    }

    public static function form(Schema $schema): Schema
    {
        return ReportTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListReportTemplates::route('/'),
            'create' => CreateReportTemplate::route('/create'),
            'edit'   => EditReportTemplate::route('/{record}/edit'),
            'design' => DesignReportTemplate::route('/{record}/design'),
        ];
    }
}
