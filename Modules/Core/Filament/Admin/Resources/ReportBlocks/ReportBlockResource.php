<?php

namespace Modules\Core\Filament\Admin\Resources\ReportBlocks;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\Pages\ListReportBlocks;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\Schemas\ReportBlockForm;
use Modules\Core\Filament\Admin\Resources\ReportBlocks\Tables\ReportBlocksTable;
use Modules\Core\Models\ReportBlock;

class ReportBlockResource extends Resource
{
    protected static ?string $model = ReportBlock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquare3Stack3d;

    public static function form(Schema $schema): Schema
    {
        return ReportBlockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportBlocksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportBlocks::route('/'),
        ];
    }
}
