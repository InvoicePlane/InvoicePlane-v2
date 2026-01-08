<?php

namespace Modules\Clients\Filament\Company\Resources\Relations;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Clients\Filament\Company\Resources\Relations\Pages\ListRelations;
use Modules\Clients\Filament\Company\Resources\Relations\Schemas\RelationForm;
use Modules\Clients\Filament\Company\Resources\Relations\Tables\RelationsTable;
use Modules\Clients\Models\Relation;
use Modules\Core\Filament\Company\Resources\BaseResource;

class RelationResource extends BaseResource
{
    protected static ?string $model = Relation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    public static function form(Schema $schema): Schema
    {
        return RelationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RelationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRelations::route('/'),
        ];
    }
}
