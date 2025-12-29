<?php

namespace Modules\Core\Filament\Admin\Resources\Numberings;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\Numberings\Pages\ListNumberings;
use Modules\Core\Filament\Admin\Resources\Numberings\Schemas\NumberingForm;
use Modules\Core\Filament\Admin\Resources\Numberings\Tables\NumberingsTable;
use Modules\Core\Models\Numbering;

class NumberingResource extends Resource
{
    protected static ?string $model = Numbering::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::NumberedList;

    public static function form(Schema $schema): Schema
    {
        return NumberingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NumberingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNumberings::route('/'),
        ];
    }
}
