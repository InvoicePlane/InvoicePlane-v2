<?php

namespace Modules\Core\Filament\Admin\Resources\ReportTemplates;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\Pages\ListReportTemplates;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\Pages\ReportBuilder;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\Schemas\ReportTemplateForm;
use Modules\Core\Filament\Admin\Resources\ReportTemplates\Tables\ReportTemplatesTable;
use Modules\Core\Models\ReportTemplate;

class ReportTemplateResource extends Resource
{
    protected static ?string $model = ReportTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

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
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListReportTemplates::route('/'),
            'design' => ReportBuilder::route('/{record}/design'),
        ];
    }
}
